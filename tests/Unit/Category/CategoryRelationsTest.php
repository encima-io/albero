<?php

namespace Encima\Albero\Tests\Unit\Category;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\Tests\Models\Category\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Encima\Albero\Tests\Models\Category\OrderedCategory;
use Encima\Albero\Tests\Seeders\Category\CategorySeeder;
use Encima\Albero\Tests\Seeders\Category\OrderedCategorySeeder;

class CategoryRelationsTest extends TestCase
{
    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_parent_relation_is_a_belongs_to()
    {
        $category = create(Category::class);

        $this->assertInstanceOf(BelongsTo::class, $category->parent());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_parent_relation_is_self_referential()
    {
        $category = create(Category::class);

        $this->assertInstanceOf(Category::class, $category->parent()->getRelated());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_parent_relation_refers_to_correct_field()
    {
        $category = create(Category::class);

        $this->assertEquals($category->getParentColumnName(), $category->parent()->getForeignKeyName());

        $this->assertEquals($category->getQualifiedParentColumnName(), $category->parent()->getQualifiedForeignKeyName());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_parent_relation()
    {
        $this->seed(CategorySeeder::class);
        $this->assertEquals(Category::firstWhere('name', 'Child 2.1')->parent()->first(), Category::firstWhere('name', 'Child 2'));
        $this->assertEquals(Category::firstWhere('name', 'Child 2')->parent()->first(), Category::firstWhere('name', 'Root 1'));
        $this->assertNull(Category::firstWhere('name', 'Root 1')->parent()->first());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_children_relation_is_a_has_many()
    {
        $category = create(Category::class);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $category->children());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_children_relation_is_self_referential()
    {
        $category = create(Category::class);

        $this->assertInstanceOf(
            Category::class,
            $category->children()->getRelated()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_children_relation_referes_to_correct_field()
    {
        $category = create(Category::class);

        $this->assertEquals(
            $category->getParentColumnName(),
            $category->children()->getForeignKeyName()
        );
        $this->assertEquals(
            $category->getQualifiedParentColumnName(),
            $category->children()->getQualifiedForeignKeyName()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_children_relation()
    {
        $this->seed(CategorySeeder::class);
        $root = Category::firstWhere('name', 'Root 1');

        foreach ($root->children() as $child) {
            $this->assertEquals($root->getKey(), $child->getParentId());
        }

        $expected = [Category::firstWhere('name', 'Child 1'), Category::firstWhere('name', 'Child 2'), Category::firstWhere('name', 'Child 3')];

        $this->assertEquals($expected, $root->children()->get()->all());

        $this->assertEmpty(Category::firstWhere('name', 'Child 3')->children()->get()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_children_relation_uses_default_ordering()
    {
        $category = create(Category::class);

        $query = $category->children()->getQuery()->getQuery();

        $expected = [
            'column' => 'left',
            'direction' => 'asc',
        ];
        $this->assertEquals($expected, $query->orders[0]);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_children_relation_uses_custom_ordering()
    {
        $category = new OrderedCategory();

        $query = $category->children()->getQuery()->getQuery();

        $expected = ['column' => 'name', 'direction' => 'asc'];
        $this->assertEquals($expected, $query->orders[0]);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_children_relation_obeys_default_ordering()
    {
        $this->seed(CategorySeeder::class);

        $children = Category::firstWhere('name', 'Root 1')->children()->get()->all();
        $expected = [
            Category::firstWhere('name', 'Child 1'),
            Category::firstWhere('name', 'Child 2'),
            Category::firstWhere('name', 'Child 3'),
        ];
        $this->assertEquals($expected, $children);

        // Swap 2 nodes & re-test
        Category::where('id', 2)->update(['left' => 8, 'right' => 9]);
        Category::where('id', 5)->update(['left' => 2, 'right' => 3]);

        $children = Category::firstWhere('name', 'Root 1')->children()->get()->all();

        $expected = [
            Category::firstWhere('name', 'Child 3'),
            Category::firstWhere('name', 'Child 2'),
            Category::firstWhere('name', 'Child 1'),
        ];
        $this->assertEquals($expected, $children);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_children_relation_obeys_custom_ordering()
    {
        $this->seed(OrderedCategorySeeder::class);

        $children = OrderedCategory::find(1)->children()->get()->all();

        $expected = [OrderedCategory::find(5), OrderedCategory::find(2), OrderedCategory::find(3)];
        $this->assertEquals($expected, $children);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_children_relation_allows_node_creation()
    {
        $this->seed(CategorySeeder::class);
        $child = create(Category::class, [
            'name' => 'Child 3.1',
        ]);

        Category::firstWhere('name', 'Child 3')->children()->save($child);

        $this->assertTrue($child->exists);
        $this->assertEquals(Category::firstWhere('name', 'Child 3')->getKey(), $child->getParentId());
    }
}
