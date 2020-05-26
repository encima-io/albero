<?php

namespace Encima\Albero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Encima\Albero\Extensions\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Encima\Albero\Extensions\Query\Builder as QueryBuilder;

/**
 * Node
 *
 * This abstract class implements Nested Set functionality. A Nested Set is a
 * smart way to implement an ordered tree with the added benefit that you can
 * select all of their descendants with a single query. Drawbacks are that
 * insertion or move operations need more complex sql queries.
 *
 * Nested sets are appropiate when you want either an ordered tree (menus,
 * commercial categories, etc.) or an efficient way of querying big trees.
 */
trait HasNestedSets
{
    protected string $parentColumn = 'parent_id';

    protected string $leftColumn = 'left';

    protected string $rightColumn = 'right';

    protected string $depthColumn = 'depth';

    /** @var string|null */
    protected $orderColumn = null;

    /** @var int|string|null */
    protected static $moveToNewParentId = null;

    protected array $scoped = [];

    /**
     * The "booting" method of the model.
     *
     * We'll use this method to register event listeners on a Node instance as
     * suggested in the beta documentation...
     *
     * TODO:
     *
     *    - Find a way to avoid needing to declare the called methods "public"
     *    as registering the event listeners *inside* this methods does not give
     *    us an object context.
     *
     * Events:
     *
     *    1. "creating": Before creating a new Node we'll assign a default value
     *    for the left and right indexes.
     *
     *    2. "saving": Before saving, we'll perform a check to see if we have to
     *    move to another parent.
     *
     *    3. "saved": Move to the new parent after saving if needed and re-set
     *    depth.
     *
     *    4. "deleting": Before delete we should prune all children and update
     *    the left and right indexes for the remaining nodes.
     *
     *    5. (optional) "restoring": Before a soft-delete node restore operation,
     *    shift its siblings.
     *
     *    6. (optional) "restore": After having restored a soft-deleted node,
     *    restore all of its descendants.
     *
     * @return void
     */
    protected static function bootHasNestedSets(): void
    {
        static::creating(function ($node) {
            $node->setDefaultLeftAndRight();
        });

        static::saving(function ($node) {
            $node->storeNewParent();
        });

        static::saved(function ($node) {
            $node->moveToNewParent();
            $node->setDepth();
        });

        static::deleting(function ($node) {
            $node->destroyDescendants();
        });

        if (static::softDeletesEnabled()) {
            static::restoring(function ($node) {
                $node->shiftSiblingsForRestore();
            });

            static::restored(function ($node) {
                $node->restoreDescendants();
            });
        }
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    abstract public function getTable();

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    abstract public function getAttribute($key);

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    abstract public function setAttribute($key, $value);

    /**
     * Determine if the model or any of the given attribute(s) have been modified.
     *
     * @param  array|string|null  $attributes
     * @return bool
     */
    abstract public function isDirty($attributes = null);

    /**
    * Define an inverse one-to-one or many relationship.
    *
    * @param  string  $related
    * @param  string|null  $foreignKey
    * @param  string|null  $ownerKey
    * @param  string|null  $relation
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    abstract public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null);

    /**
     * Define a one-to-many relationship.
     *
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function hasMany($related, $foreignKey = null, $localKey = null);

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function newQuery();

    /**
     * Get the database connection for the model.
     *
     * @return \Illuminate\Database\Connection
     */
    abstract public function getConnection();

    /**
    * Get the parent column name.
    *
    * @return string
    */
    public function getParentColumnName(): string
    {
        return $this->parentColumn;
    }

    /**
    * Get the table qualified parent column name.
    *
    * @return string
    */
    public function getQualifiedParentColumnName(): string
    {
        return $this->getTable().'.'.$this->getParentColumnName();
    }

    /**
    * Get the value of the models "parent_id" field.
    *
    * @return int|string
    */
    public function getParentId()
    {
        return $this->getAttribute($this->getparentColumnName());
    }

    /**
     * Get the "left" field column name.
     *
     * @return string
     */
    public function getLeftColumnName(): string
    {
        return $this->leftColumn;
    }

    /**
     * Get the table qualified "left" field column name.
     *
     * @return string
     */
    public function getQualifiedLeftColumnName(): string
    {
        return $this->getTable().'.'.$this->getLeftColumnName();
    }

    /**
     * Get the value of the model's "left" field.
     *
     * @return int
     */
    public function getLeft(): int
    {
        return $this->getAttribute($this->getLeftColumnName());
    }

    /**
     * Get the "right" field column name.
     *
     * @return string
     */
    public function getRightColumnName(): string
    {
        return $this->rightColumn;
    }

    /**
     * Get the table qualified "right" field column name.
     *
     * @return string
     */
    public function getQualifiedRightColumnName(): string
    {
        return $this->getTable().'.'.$this->getRightColumnName();
    }

    /**
     * Get the value of the model's "right" field.
     *
     * @return int
     */
    public function getRight(): int
    {
        return $this->getAttribute($this->getRightColumnName());
    }

    /**
     * Get the "depth" field column name.
     *
     * @return string
     */
    public function getDepthColumnName(): string
    {
        return $this->depthColumn;
    }

    /**
     * Get the table qualified "depth" field column name.
     *
     * @return string
     */
    public function getQualifiedDepthColumnName(): string
    {
        return $this->getTable().'.'.$this->getDepthColumnName();
    }

    /**
     * Get the model's "depth" value.
     *
     * @return int
     */
    public function getDepth(): ?int
    {
        return $this->getAttribute($this->getDepthColumnName());
    }

    /**
     * Get the "order" field column name.
     *
     * @return string
     */
    public function getOrderColumnName(): string
    {
        return is_null($this->orderColumn) ? $this->getLeftColumnName() : $this->orderColumn;
    }

    /**
     * Get the table qualified "order" field column name.
     *
     * @return string
     */
    public function getQualifiedOrderColumnName(): string
    {
        return $this->getTable().'.'.$this->getOrderColumnName();
    }

    /**
     * Get the model's "order" value.
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->getAttribute($this->getOrderColumnName());
    }

    /**
     * Get the column names which define our scope
     *
     * @return array
     */
    public function getScopedColumns(): array
    {
        return (array) $this->scoped;
    }

    /**
     * Get the qualified column names which define our scope
     *
     * @return array
     */
    public function getQualifiedScopedColumns(): array
    {
        if (!$this->isScoped()) {
            return $this->getScopedColumns();
        }

        $prefix = $this->getTable().'.';

        return array_map(function ($c) use ($prefix) {
            return $prefix.$c;
        }, $this->getScopedColumns());
    }

    /**
     * Returns wether this particular node instance is scoped by certain fields
     * or not.
     *
     * @return boolean
     */
    public function isScoped(): bool
    {
        return !!(count($this->getScopedColumns()) > 0);
    }

    /**
    * Parent relation (self-referential) 1-1.
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(get_class($this), $this->getParentColumnName());
    }

    /**
    * Children relation (self-referential) 1-N.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function children(): HasMany
    {
        return $this->hasMany(get_class($this), $this->getParentColumnName())
                ->orderBy($this->getOrderColumnName());
    }

    /**
     * Get a new "scoped" query builder for the Node's model.
     *
     * @param  bool  $excludeDeleted
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newNestedSetQuery($excludeDeleted = true): Builder
    {
        $builder = $this->newQuery($excludeDeleted)->orderBy($this->getQualifiedOrderColumnName());

        if ($this->isScoped()) {
            foreach ($this->scoped as $scopeFld) {
                $builder->where($scopeFld, '=', $this->{$scopeFld});
            }
        }

        return $builder;
    }

    /**
     * Overload new Collection
     *
     * @param array $models
     * @return \Encima\Albero\Extensions\Eloquent\Collection
     */
    public function newCollection(array $models = []): Collection
    {
        return new Collection($models);
    }

    /**
     * Get all of the nodes from the database.
     *
     * @param  array  $columns
     * @return \Encima\Albero\Extensions\Eloquent\Collection|static[]
     */
    public static function all($columns = ['*']): Collection
    {
        $instance = new static();

        return $instance->newQuery()
                    ->orderBy($instance->getQualifiedOrderColumnName())
                    ->get($columns);
    }

    /**
     * Returns the first root node.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function root(): Model
    {
        return static::roots()->first();
    }

    /**
     * Static query scope. Returns a query scope with all root nodes.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function roots(): Builder
    {
        $instance = new static();

        return $instance->newQuery()
                    ->whereNull($instance->getParentColumnName())
                    ->orderBy($instance->getQualifiedOrderColumnName());
    }

    /**
     * Static query scope. Returns a query scope with all nodes which are at
     * the end of a branch.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function allLeaves(): Builder
    {
        $instance = new static();

        $grammar = $instance->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($instance->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($instance->getQualifiedLeftColumnName());

        return $instance->newQuery()
            ->whereRaw($rgtCol.' - '.$lftCol.' = 1')
            ->orderBy($instance->getQualifiedOrderColumnName());
    }

    /**
     * Static query scope. Returns a query scope with all nodes which are at
     * the middle of a branch (not root and not leaves).
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function allTrunks(): Builder
    {
        $instance = new static();

        $grammar = $instance->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($instance->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($instance->getQualifiedLeftColumnName());

        return $instance->newQuery()
            ->whereNotNull($instance->getParentColumnName())
            ->whereRaw($rgtCol.' - '.$lftCol.' != 1')
            ->orderBy($instance->getQualifiedOrderColumnName());
    }

    /**
     * Checks wether the underlying Nested Set structure is valid.
     *
     * @return boolean
     */
    public static function isValidNestedSet(): bool
    {
        $validator = new SetValidator(new static());

        return $validator->passes();
    }

    /**
     * Rebuilds the structure of the current Nested Set.
     *
     * @param  bool $force
     * @return void
     */
    public static function rebuild($force = false): void
    {
        $builder = new SetBuilder(new static());

        $builder->rebuild($force);
    }

    /**
     * Maps the provided tree structure into the database.
     *
     * @param   array|\Illuminate\Support\Contracts\ArrayableInterface
     * @return  boolean
     */
    public static function buildTree($nodeList): bool
    {
        return (new static())->makeTree($nodeList);
    }

    /**
     * Query scope which extracts a certain node object from the current query
     * expression.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithoutNode($query, $node): Builder
    {
        return $query->where($node->getKeyName(), '!=', $node->getKey());
    }

    /**
     * Extracts current node (self) from current query expression.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithoutSelf($query): Builder
    {
        return $this->scopeWithoutNode($query, $this);
    }

    /**
     * Extracts first root (from the current node p-o-v) from current query
     * expression.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithoutRoot($query): Builder
    {
        return $this->scopeWithoutNode($query, $this->getRoot());
    }

    /**
     * Provides a depth level limit for the query.
     *
     * @param   query   \Illuminate\Database\Query\Builder
     * @param   limit   integer
     * @return  \Illuminate\Database\Query\Builder
     */
    public function scopeLimitDepth($query, $limit): Builder
    {
        $depth = $this->exists ? $this->getDepth() : $this->getLevel();
        $max = $depth + $limit;
        $scopes = [$depth, $max];

        return $query->whereBetween($this->getDepthColumnName(), [min($scopes), max($scopes)]);
    }

    /**
     * Returns true if this is a root node.
     *
     * @return boolean
     */
    public function isRoot(): bool
    {
        return is_null($this->getParentId());
    }

    /**
     * Returns true if this is a leaf node (end of a branch).
     *
     * @return boolean
     */
    public function isLeaf(): bool
    {
        return $this->exists && ($this->getRight() - $this->getLeft() == 1);
    }

    /**
     * Returns true if this is a trunk node (not root or leaf).
     *
     * @return boolean
     */
    public function isTrunk(): bool
    {
        return !$this->isRoot() && !$this->isLeaf();
    }

    /**
     * Returns true if this is a child node.
     *
     * @return boolean
     */
    public function isChild(): bool
    {
        return !$this->isRoot();
    }

    /**
     * Returns the root node starting at the current node.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRoot(): Model
    {
        if ($this->exists) {
            return $this->ancestorsAndSelf()->whereNull($this->getParentColumnName())->first();
        }
        $parentId = $this->getParentId();

        if (!is_null($parentId) && $currentParent = static::find($parentId)) {
            return $currentParent->getRoot();
        }

        return $this;
    }

    /**
     * Instance scope which targes all the ancestor chain nodes including
     * the current one.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function ancestorsAndSelf(): Builder
    {
        return $this->newNestedSetQuery()
                ->where($this->getLeftColumnName(), '<=', $this->getLeft())
                ->where($this->getRightColumnName(), '>=', $this->getRight());
    }

    /**
     * Get all the ancestor chain from the database including the current node.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAndSelf($columns = ['*']): Collection
    {
        return $this->ancestorsAndSelf()->get($columns);
    }

    /**
     * Get all the ancestor chain from the database including the current node
     * but without the root node.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAndSelfWithoutRoot($columns = ['*']): Collection
    {
        return $this->ancestorsAndSelf()->withoutRoot()->get($columns);
    }

    /**
     * Instance scope which targets all the ancestor chain nodes excluding
     * the current one.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function ancestors(): Builder
    {
        return $this->ancestorsAndSelf()->withoutSelf();
    }

    /**
     * Get all the ancestor chain from the database excluding the current node.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestors($columns = ['*']): Collection
    {
        return $this->ancestors()->get($columns);
    }

    /**
     * Get all the ancestor chain from the database excluding the current node
     * and the root node (from the current node's perspective).
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsWithoutRoot($columns = ['*']): Collection
    {
        return $this->ancestors()->withoutRoot()->get($columns);
    }

    /**
     * Instance scope which targets all children of the parent, including self.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function siblingsAndSelf(): Builder
    {
        return $this->newNestedSetQuery()
                ->where($this->getParentColumnName(), $this->getParentId());
    }

    /**
     * Get all children of the parent, including self.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblingsAndSelf($columns = ['*']): Collection
    {
        return $this->siblingsAndSelf()->get($columns);
    }

    /**
     * Instance scope targeting all children of the parent, except self.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function siblings(): Builder
    {
        return $this->siblingsAndSelf()->withoutSelf();
    }

    /**
     * Return all children of the parent, except self.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblings($columns = ['*']): Collection
    {
        return $this->siblings()->get($columns);
    }

    /**
     * Instance scope targeting all of its nested children which do not have
     * children.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function leaves(): Builder
    {
        $grammar = $this->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($this->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($this->getQualifiedLeftColumnName());

        return $this->descendants()
                ->whereRaw($rgtCol.' - '.$lftCol.' = 1');
    }

    /**
     * Return all of its nested children which do not have children.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLeaves($columns = ['*']): Collection
    {
        return $this->leaves()->get($columns);
    }

    /**
     * Instance scope targeting all of its nested children which are between the
     * root and the leaf nodes (middle branch).
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function trunks(): Builder
    {
        $grammar = $this->getConnection()->getQueryGrammar();

        $rgtCol = $grammar->wrap($this->getQualifiedRightColumnName());
        $lftCol = $grammar->wrap($this->getQualifiedLeftColumnName());

        return $this->descendants()
                ->whereNotNull($this->getQualifiedParentColumnName())
                ->whereRaw($rgtCol.' - '.$lftCol.' != 1');
    }

    /**
     * Return all of its nested children which are trunks.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrunks($columns = ['*']): Collection
    {
        return $this->trunks()->get($columns);
    }

    /**
     * Scope targeting itself and all of its nested children.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function descendantsAndSelf(): Builder
    {
        return $this->newNestedSetQuery()
                ->where($this->getLeftColumnName(), '>=', $this->getLeft())
                ->where($this->getLeftColumnName(), '<', $this->getRight());
    }

    /**
     * Retrieve all nested children an self.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendantsAndSelf($columns = ['*']): Collection
    {
        if (is_array($columns)) {
            return $this->descendantsAndSelf()->get($columns);
        }

        $arguments = func_get_args();

        $limit = intval(array_shift($arguments));
        $columns = array_shift($arguments) ?: ['*'];

        return $this->descendantsAndSelf()->limitDepth($limit)->get($columns);
    }

    /**
     * Set of all children & nested children.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function descendants(): Builder
    {
        return $this->descendantsAndSelf()->withoutSelf();
    }

    /**
     * Retrieve all of its children & nested children.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendants($columns = ['*']): Collection
    {
        if (is_array($columns)) {
            return $this->descendants()->get($columns);
        }

        $arguments = func_get_args();

        $limit = intval(array_shift($arguments));
        $columns = array_shift($arguments) ?: ['*'];

        return $this->descendants()->limitDepth($limit)->get($columns);
    }

    /**
     * Set of "immediate" descendants (aka children), alias for the children relation.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function immediateDescendants(): Builder
    {
        return $this->children();
    }

    /**
     * Retrive all of its "immediate" descendants.
     *
     * @param array   $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getImmediateDescendants($columns = ['*']): Collection
    {
        return $this->children()->get($columns);
    }

    /**
    * Returns the level of this node in the tree.
    * Root level is 0.
    *
    * @return int
    */
    public function getLevel(): int
    {
        if (is_null($this->getParentId())) {
            return 0;
        }

        return $this->computeLevel();
    }

    /**
     * Returns true if node is a descendant.
     *
     * @param NestedSet
     * @return boolean
     */
    public function isDescendantOf($other): bool
    {
        return (
      $this->getLeft() > $other->getLeft() &&
      $this->getLeft() < $other->getRight() &&
      $this->inSameScope($other)
    );
    }

    /**
     * Returns true if node is self or a descendant.
     *
     * @param NestedSet
     * @return boolean
     */
    public function isSelfOrDescendantOf($other): bool
    {
        return (
      $this->getLeft() >= $other->getLeft() &&
      $this->getLeft() < $other->getRight() &&
      $this->inSameScope($other)
    );
    }

    /**
     * Returns true if node is an ancestor.
     *
     * @param NestedSet
     * @return boolean
     */
    public function isAncestorOf($other): bool
    {
        return (
      $this->getLeft() < $other->getLeft() &&
      $this->getRight() > $other->getLeft() &&
      $this->inSameScope($other)
    );
    }

    /**
     * Returns true if node is self or an ancestor.
     *
     * @param NestedSet
     * @return boolean
     */
    public function isSelfOrAncestorOf($other): bool
    {
        return (
      $this->getLeft() <= $other->getLeft() &&
      $this->getRight() > $other->getLeft() &&
      $this->inSameScope($other)
    );
    }

    /**
     * Returns the first sibling to the left.
     *
     * @return NestedSet
     */
    public function getLeftSibling(): ?Model
    {
        return $this->siblings()
                ->where($this->getLeftColumnName(), '<', $this->getLeft())
                ->orderBy($this->getOrderColumnName(), 'desc')
                ->get()
                ->last();
    }

    /**
     * Returns the first sibling to the right.
     *
     * @return NestedSet
     */
    public function getRightSibling(): ?Model
    {
        return $this->siblings()
                ->where($this->getLeftColumnName(), '>', $this->getLeft())
                ->first();
    }

    /**
     * Find the left sibling and move to left of it.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function moveLeft(): Model
    {
        return $this->moveToLeftOf($this->getLeftSibling());
    }

    /**
     * Find the right sibling and move to the right of it.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function moveRight(): Model
    {
        return $this->moveToRightOf($this->getRightSibling());
    }

    /**
     * Move to the node to the left of ...
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function moveToLeftOf($node): Model
    {
        return $this->moveTo($node, 'left');
    }

    /**
     * Move to the node to the right of ...
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function moveToRightOf($node): Model
    {
        return $this->moveTo($node, 'right');
    }

    /**
     * Alias for moveToRightOf
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function makeNextSiblingOf($node): Model
    {
        return $this->moveToRightOf($node);
    }

    /**
     * Alias for moveToRightOf
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function makeSiblingOf($node): Model
    {
        return $this->moveToRightOf($node);
    }

    /**
     * Alias for moveToLeftOf
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function makePreviousSiblingOf($node): Model
    {
        return $this->moveToLeftOf($node);
    }

    /**
     * Make the node a child of ...
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function makeChildOf($node): self
    {
        return $this->moveTo($node, 'child');
    }

    /**
     * Make the node the first child of ...
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function makeFirstChildOf($node): Model
    {
        if ($node->children()->count() == 0) {
            return $this->makeChildOf($node);
        }

        return $this->moveToLeftOf($node->children()->first());
    }

    /**
     * Make the node the last child of ...
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function makeLastChildOf($node): Model
    {
        return $this->makeChildOf($node);
    }

    /**
     * Make current node a root node.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function makeRoot(): Model
    {
        return $this->moveTo($this, 'root');
    }

    /**
     * Equals?
     *
     * @param \Illuminate\Database\Eloquent\Model
     * @return boolean
     */
    public function equals($node): bool
    {
        return ($this == $node);
    }

    /**
     * Checkes if the given node is in the same scope as the current one.
     *
     * @param \Illuminate\Database\Eloquent\Model
     * @return boolean
     */
    public function inSameScope($other): bool
    {
        foreach ($this->getScopedColumns() as $fld) {
            if ($this->{$fld} != $other->{$fld}) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks wether the given node is a descendant of itself. Basically, whether
     * its in the subtree defined by the left and right indices.
     *
     * @param \Illuminate\Database\Eloquent\Model
     * @return boolean
     */
    public function insideSubtree($node): bool
    {
        return (
            $this->getLeft() >= $node->getLeft() &&
            $this->getLeft() <= $node->getRight() &&
            $this->getRight() >= $node->getLeft() &&
            $this->getRight() <= $node->getRight()
        );
    }

    /**
     * Sets default values for left and right fields.
     *
     * @return void
     */
    public function setDefaultLeftAndRight(): void
    {
        $withHighestRight = $this->newNestedSetQuery()->reOrderBy($this->getRightColumnName(), 'desc')->take(1)->sharedLock()->first();

        $maxRgt = 0;
        if (!is_null($withHighestRight)) {
            $maxRgt = $withHighestRight->getRight();
        }

        $this->setAttribute($this->getLeftColumnName(), $maxRgt + 1);
        $this->setAttribute($this->getRightColumnName(), $maxRgt + 2);
    }

    /**
     * Store the parent_id if the attribute is modified so as we are able to move
     * the node to this new parent after saving.
     *
     * @return void
     */
    public function storeNewParent(): void
    {
        if ($this->isDirty($this->getParentColumnName()) && ($this->exists || !$this->isRoot())) {
            static::$moveToNewParentId = $this->getParentId();
        } else {
            static::$moveToNewParentId = false;
        }
    }

    /**
     * Move to the new parent if appropiate.
     *
     * @return void
     */
    public function moveToNewParent(): void
    {
        $pid = static::$moveToNewParentId;

        if (is_null($pid)) {
            $this->makeRoot();
        } elseif ($pid !== false) {
            $this->makeChildOf($pid);
        }
    }

    /**
     * Sets the depth attribute
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function setDepth(): Model
    {
        $self = $this;

        $this->getConnection()->transaction(function () use ($self) {
            $self->reload();

            $level = $self->getLevel();

            $self->newNestedSetQuery()->where($self->getKeyName(), '=', $self->getKey())->update([$self->getDepthColumnName() => $level]);
            $self->setAttribute($self->getDepthColumnName(), $level);
        });

        return $this;
    }

    /**
     * Sets the depth attribute for the current node and all of its descendants.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function setDepthWithSubtree(): Model
    {
        $self = $this;

        $this->getConnection()->transaction(function () use ($self) {
            $self->reload();

            $self->descendantsAndSelf()->select($self->getKeyName())->lockForUpdate()->get();

            $oldDepth = !is_null($self->getDepth()) ? $self->getDepth() : 0;

            $newDepth = $self->getLevel();

            $self->newNestedSetQuery()->where($self->getKeyName(), '=', $self->getKey())->update([$self->getDepthColumnName() => $newDepth]);
            $self->setAttribute($self->getDepthColumnName(), $newDepth);

            $diff = $newDepth - $oldDepth;
            if (!$self->isLeaf() && $diff != 0) {
                $self->descendants()->increment($self->getDepthColumnName(), $diff);
            }
        });

        return $this;
    }

    /**
     * Prunes a branch off the tree, shifting all the elements on the right
     * back to the left so the counts work.
     *
     * @return void;
     */
    public function destroyDescendants(): void
    {
        if (is_null($this->getRight()) || is_null($this->getLeft())) {
            return;
        }

        $self = $this;

        $this->getConnection()->transaction(function () use ($self) {
            $self->reload();

            $lftCol = $self->getLeftColumnName();
            $rgtCol = $self->getRightColumnName();
            $lft = $self->getLeft();
            $rgt = $self->getRight();

            // Apply a lock to the rows which fall past the deletion point
            $self->newNestedSetQuery()->where($lftCol, '>=', $lft)->select($self->getKeyName())->lockForUpdate()->get();

            // Prune children
            $self->newNestedSetQuery()->where($lftCol, '>', $lft)->where($rgtCol, '<', $rgt)->delete();

            // Update left and right indexes for the remaining nodes
            $diff = $rgt - $lft + 1;

            $self->newNestedSetQuery()->where($lftCol, '>', $rgt)->decrement($lftCol, $diff);
            $self->newNestedSetQuery()->where($rgtCol, '>', $rgt)->decrement($rgtCol, $diff);
        });
    }

    /**
     * "Makes room" for the the current node between its siblings.
     *
     * @return void
     */
    public function shiftSiblingsForRestore(): void
    {
        if (is_null($this->getRight()) || is_null($this->getLeft())) {
            return;
        }

        $self = $this;

        $this->getConnection()->transaction(function () use ($self) {
            $lftCol = $self->getLeftColumnName();
            $rgtCol = $self->getRightColumnName();
            $lft = $self->getLeft();
            $rgt = $self->getRight();

            $diff = $rgt - $lft + 1;

            $self->newNestedSetQuery()->where($lftCol, '>=', $lft)->increment($lftCol, $diff);
            $self->newNestedSetQuery()->where($rgtCol, '>=', $lft)->increment($rgtCol, $diff);
        });
    }

    /**
     * Restores all of the current node's descendants.
     *
     * @return void
     */
    public function restoreDescendants(): void
    {
        if (is_null($this->getRight()) || is_null($this->getLeft())) {
            return;
        }

        $self = $this;

        $this->getConnection()->transaction(function () use ($self) {
            $self->newNestedSetQuery()
        ->withTrashed()
        ->where($self->getLeftColumnName(), '>', $self->getLeft())
        ->where($self->getRightColumnName(), '<', $self->getRight())
        ->update([
          $self->getDeletedAtColumn() => null,
          $self->getUpdatedAtColumn() => $self->{$self->getUpdatedAtColumn()},
        ]);
        });
    }

    /**
     * Return an key-value array indicating the node's depth with $seperator
     *
     * @return Array
     */
    public static function getNestedList($column, $key = null, $seperator = ' '): array
    {
        $instance = new static();

        $key = $key ?: $instance->getKeyName();
        $depthColumn = $instance->getDepthColumnName();

        $nodes = $instance->newNestedSetQuery()->get()->toArray();

        return array_combine(array_map(function ($node) use ($key) {
            return $node[$key];
        }, $nodes), array_map(function ($node) use ($seperator, $depthColumn, $column) {
            return str_repeat($seperator, $node[$depthColumn]).$node[$column];
        }, $nodes));
    }

    /**
     * Maps the provided tree structure into the database using the current node
     * as the parent. The provided tree structure will be inserted/updated as the
     * descendancy subtree of the current node instance.
     *
     * @param   array|\Illuminate\Support\Contracts\ArrayableInterface
     * @return  boolean
     */
    public function makeTree($nodeList): bool
    {
        $mapper = new SetMapper($this);

        return $mapper->map($nodeList);
    }

    /**
     * Main move method. Here we handle all node movements with the corresponding
     * lft/rgt index updates.
     *
     * @param Baum\Node|int $target
     * @param string        $position
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function moveTo($target, $position): self
    {
        return Move::to($this, $target, $position);
    }

    /**
     * Compute current node level. If could not move past ourseleves return
     * our ancestor count, otherwhise get the first parent level + the computed
     * nesting.
     *
     * @return integer
     */
    protected function computeLevel(): int
    {
        list($node, $nesting) = $this->determineDepth($this);

        if ($node->equals($this)) {
            return $this->ancestors()->count();
        }

        return $node->getLevel() + $nesting;
    }

    /**
     * Return an array with the last node we could reach and its nesting level
     *
     * @param   Baum\Node $node
     * @param   integer   $nesting
     * @return  array
     */
    protected function determineDepth($node, $nesting = 0): array
    {
        // Traverse back up the ancestry chain and add to the nesting level count
        while ($parent = $node->parent()->first()) {
            $nesting = $nesting + 1;

            $node = $parent;
        }

        return [$node, $nesting];
    }

    /**
     * Reloads the model from the database.
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws ModelNotFoundException
     */
    public function reload(): Model
    {
        if ($this->exists || ($this->areSoftDeletesEnabled() && $this->trashed())) {
            $fresh = $this->getFreshInstance();

            if (is_null($fresh)) {
                throw (new ModelNotFoundException())->setModel(get_called_class());
            }

            $this->setRawAttributes($fresh->getAttributes(), true);

            $this->setRelations($fresh->getRelations());

            $this->exists = $fresh->exists;
        } else {
            // Revert changes if model is not persisted
            $this->attributes = $this->original;
        }


        return $this;
    }

    /**
     * Get the observable event names.
     *
     * @return array
     */
    public function getObservableEvents(): array
    {
        return array_merge(['moving', 'moved'], parent::getObservableEvents());
    }

    /**
     * Register a moving model event with the dispatcher.
     *
     * @param  Closure|string  $callback
     * @return void
     */
    public static function moving($callback, $priority = 0): void
    {
        static::registerModelEvent('moving', $callback, $priority);
    }

    /**
     * Register a moved model event with the dispatcher.
     *
     * @param  Closure|string  $callback
     * @return void
     */
    public static function moved($callback, $priority = 0): void
    {
        static::registerModelEvent('moved', $callback, $priority);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Baum\Extensions\Query\Builder
     */
    protected function newBaseQueryBuilder(): QueryBuilder
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        return new QueryBuilder($conn, $grammar, $conn->getPostProcessor());
    }

    /**
     * Returns a fresh instance from the database.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getFreshInstance(): ?Model
    {
        if ($this->areSoftDeletesEnabled()) {
            return static::withTrashed()->find($this->getKey());
        }

        return static::find($this->getKey());
    }

    /**
     * Returns wether soft delete functionality is enabled on the model or not.
     *
     * @return boolean
     */
    public function areSoftDeletesEnabled(): bool
    {
        // To determine if there's a global soft delete scope defined we must
        // first determine if there are any, to workaround a non-existent key error.
        $globalScopes = $this->getGlobalScopes();

        if (count($globalScopes) === 0) {
            return false;
        }

        // Now that we're sure that the calling class has some kind of global scope
        // we check for the SoftDeletingScope existance
        return static::hasGlobalScope(new SoftDeletingScope());
    }

    /**
     * Static method which returns wether soft delete functionality is enabled
     * on the model.
     *
     * @return boolean
     */
    public static function softDeletesEnabled(): bool
    {
        return (new static())->areSoftDeletesEnabled();
    }
}
