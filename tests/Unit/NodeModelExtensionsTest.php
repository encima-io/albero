<?php

namespace Encima\Albero\Tests\Unit;

use Encima\Albero\Tests\TestCase;
use Illuminate\Events\Dispatcher;
use Encima\Albero\Tests\Models\Category\Category;
use Encima\Albero\Tests\Models\Category\SoftCategory;
use Encima\Albero\Tests\Models\Category\ScopedCategory;
use Encima\Albero\Tests\Models\Category\OrderedCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Encima\Albero\Tests\Models\Category\MultiScopedCategory;

class NodeModelExtensionsTest extends TestCase
{
    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_new_query_returns_eloquent_builder_with_extended_query_builder()
    {
        $query = (new Category())->newQuery()->getQuery();

        $this->assertInstanceOf(
            \Encima\Albero\Extensions\Query\Builder::class,
            $query
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_new_collection_returns_custom_one()
    {
        $this->assertInstanceOf(
            \Encima\Albero\Extensions\Eloquent\Collection::class,
            (new Category())->newCollection()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_observable_events_includes_moving_events()
    {
        $events = with(new Category())->getObservableEvents();

        $this->assertContains('moving', $events);
        $this->assertContains('moved', $events);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_are_soft_deletes_enabled()
    {
        $this->assertFalse((new Category())->areSoftDeletesEnabled());
        $this->assertTrue((new SoftCategory())->areSoftDeletesEnabled());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_soft_deletes_enabled_static()
    {
        $this->assertFalse(Category::softDeletesEnabled());
        $this->assertTrue(SoftCategory::softDeletesEnabled());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-14
     */
    public function it_is_moving()
    {
        $closure = function () {
        };
        $this->partialMock(Dispatcher::class, function ($mock) use ($closure) {
            $mock->shouldReceive('listen')
                    ->once()
                    ->with('eloquent.moving: '.get_class(new Category()), $closure);
        });
        Category::setEventDispatcher(app(Dispatcher::class));
        Category::moving($closure);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-14
     */
    public function it_moved()
    {
        $closure = function () {
        };
        $this->partialMock(Dispatcher::class, function ($mock) use ($closure) {
            $mock->shouldReceive('listen')
                ->once()
                ->with('eloquent.moved: '.get_class(new Category()), $closure);
        });
        Category::setEventDispatcher(app(Dispatcher::class));
        Category::moved($closure);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_reload_resets_changes_on_fresh_nodes()
    {
        $new = new Category();

        $new->name = 'Some new category';
        $new->reload();

        $this->assertNull($new->name);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_reload_resets_changes_on_persisted_nodes()
    {
        $node = create(Category::class, [
            'name' => 'Some node',
        ]);

        $node->name = 'A better node';
        $node->left = 10;
        $node->reload();

        $this->assertEquals(
            Category::firstWhere('name', 'Some node')->getAttributes(),
            $node->getAttributes()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_reload_resets_changes_on_deleted_nodes()
    {
        $node = Category::create(['name' => 'Some node']);
        $this->assertNotNull($node->getKey());

        $node->delete();
        $this->assertNull(Category::firstWhere('name', 'Some node'));

        $node->name = 'A better node';
        $node->reload();

        $this->assertEquals('Some node', $node->name);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_reload_throws_exception_if_node_cannot_be_located()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [Encima\Albero\Tests\Models\Category\Category].');

        $node = create(Category::class, [
            'name' => 'Some node',
            'parent_id' => null,
        ]);
        $this->assertNotNull($node->getKey());
        $node->delete();
        $this->assertNull(Category::find($node->id));
        $this->assertFalse($node->exists);

        // Fake persisted state, reload & expect failure
        $node->exists = true;
        $node->reload();
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_new_nested_set_query_uses_internal_builder()
    {
        $category = new Category();
        $builder = $category->newNestedSetQuery();
        $query = $builder->getQuery();

        $this->assertInstanceOf(
            \Encima\Albero\Extensions\Query\Builder::class,
            $query
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_new_nested_set_query_is_ordered_by_default()
    {
        $category = new Category();
        $builder = $category->newNestedSetQuery();
        $query = $builder->getQuery();

        $this->assertSame([], $query->wheres);
        $this->assertNotEmpty($query->orders);
        $this->assertEquals($category->getLeftColumnName(), $category->getOrderColumnName());
        $this->assertEquals($category->getQualifiedLeftColumnName(), $category->getQualifiedOrderColumnName());
        $this->assertEquals($category->getQualifiedOrderColumnName(), $query->orders[0]['column']);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_new_nested_set_query_is_ordered_by_custom()
    {
        $category = new OrderedCategory();
        $builder = $category->newNestedSetQuery();
        $query = $builder->getQuery();

        $this->assertSame([], $query->wheres);
        $this->assertNotEmpty($query->orders);
        $this->assertEquals('name', $category->getOrderColumnName());
        $this->assertEquals('categories.name', $category->getQualifiedOrderColumnName());
        $this->assertEquals($category->getQualifiedOrderColumnName(), $query->orders[0]['column']);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_new_nested_set_query_includes_scoped_columns()
    {
        $category = new Category();
        $simpleQuery = $category->newNestedSetQuery()->getQuery();
        $this->assertSame([], $simpleQuery->wheres);

        $scopedCategory = new ScopedCategory();
        $scopedQuery = $scopedCategory->newNestedSetQuery()->getQuery();
        $this->assertCount(1, $scopedQuery->wheres);
        $this->assertEquals($scopedCategory->getScopedColumns(), array_map(function ($elem) {
            return $elem['column'];
        }, $scopedQuery->wheres));

        $multiScopedCategory = new MultiScopedCategory();
        $multiScopedQuery = $multiScopedCategory->newNestedSetQuery()->getQuery();
        $this->assertCount(2, $multiScopedQuery->wheres);
        $this->assertEquals($multiScopedCategory->getScopedColumns(), array_map(function ($elem) {
            return $elem['column'];
        }, $multiScopedQuery->wheres));
    }
}
