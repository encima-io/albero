<?php

namespace Encima\Albero;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Contracts\ArrayableInterface;

class SetMapper
{
    /** @var \Illuminate\Database\Eloquent\Model */
    protected $node = null;

    protected string $childrenKeyName = 'children';

    /**
     * Create a new \Baum\SetBuilder class instance.
     *
     * @param   \Baum\Node      $node
     * @return  void
     */
    public function __construct(Model $node, string $childrenKeyName = 'children')
    {
        $this->node = $node;

        $this->childrenKeyName = $childrenKeyName;
    }

    /**
     * Maps a tree structure into the database. Unguards & wraps in transaction.
     *
     * @param   array|\Illuminate\Support\Contracts\ArrayableInterface
     * @return  boolean
     */
    public function map($nodeList): bool
    {
        $self = $this;

        return $this->wrapInTransaction(function () use ($self, $nodeList) {
            forward_static_call([get_class($self->node), 'unguard']);

            $result = $self->mapTree($nodeList);

            forward_static_call([get_class($self->node), 'reguard']);

            return $result;
        });
    }

    /**
     * Maps a tree structure into the database without unguarding nor wrapping
     * inside a transaction.
     *
     * @param   array|\Illuminate\Support\Contracts\ArrayableInterface
     * @return  boolean
     */
    public function mapTree($nodeList): bool
    {
        $tree = $nodeList instanceof ArrayableInterface ? $nodeList->toArray() : $nodeList;

        $affectedKeys = [];

        $result = $this->mapTreeRecursive($tree, $this->node->getKey(), $affectedKeys);

        if ($result && count($affectedKeys) > 0) {
            $this->deleteUnaffected($affectedKeys);
        }

        return $result;
    }

    /**
     * Returns the children key name to use on the mapping array
     *
     * @return string
     */
    public function getChildrenKeyName(): string
    {
        return $this->childrenKeyName;
    }

    /**
     * Maps a tree structure into the database
     *
     * @param   array   $tree
     * @param   mixed   $parent
     * @return  boolean
     */
    protected function mapTreeRecursive(array $tree, $parentKey = null, &$affectedKeys = []): bool
    {
        // For every attribute entry: We'll need to instantiate a new node either
        // from the database (if the primary key was supplied) or a new instance. Then,
        // append all the remaining data attributes (including the `parent_id` if
        // present) and save it. Finally, tail-recurse performing the same
        // operations for any child node present. Setting the `parent_id` property at
        // each level will take care of the nesting work for us.
        foreach ($tree as $attributes) {
            $node = $this->firstOrNew($this->getSearchAttributes($attributes));

            $data = $this->getDataAttributes($attributes);
            if (!is_null($parentKey)) {
                $data[$node->getParentColumnName()] = $parentKey;
            }

            $node->fill($data);

            $result = $node->save();

            if (!$result) {
                return false;
            }

            $affectedKeys[] = $node->getKey();

            if (array_key_exists($this->getChildrenKeyName(), $attributes)) {
                $children = $attributes[$this->getChildrenKeyName()];

                if (count($children) > 0) {
                    $result = $this->mapTreeRecursive($children, $node->getKey(), $affectedKeys);

                    if (!$result) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function getSearchAttributes(array $attributes): array
    {
        $searchable = [$this->node->getKeyName()];

        return Arr::only($attributes, $searchable);
    }

    protected function getDataAttributes(array $attributes): array
    {
        $exceptions = [$this->node->getKeyName(), $this->getChildrenKeyName()];

        return Arr::except($attributes, $exceptions);
    }

    protected function firstOrNew(array $attributes): Model
    {
        $className = get_class($this->node);

        if (count($attributes) === 0) {
            return new $className();
        }

        return forward_static_call([$className, 'firstOrNew'], $attributes);
    }

    protected function pruneScope(): Builder
    {
        if ($this->node->exists) {
            return $this->node->descendants();
        }

        return $this->node->newNestedSetQuery();
    }

    protected function deleteUnaffected(array $keys = []): bool
    {
        return $this->pruneScope()->whereNotIn($this->node->getKeyName(), $keys)->delete();
    }

    protected function wrapInTransaction(Closure $callback)
    {
        return $this->node->getConnection()->transaction($callback);
    }
}