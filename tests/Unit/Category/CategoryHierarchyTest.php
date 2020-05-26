<?php

namespace Encima\Albero\Tests\Unit\Category;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\Tests\Models\Category\Category;
use Encima\Albero\Tests\Models\Category\OrderedCategory;
use Encima\Albero\Tests\Seeders\Category\CategorySeeder;
use Encima\Albero\Tests\Seeders\Category\OrderedCategorySeeder;

class CategoryHierarchyTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_returns_collection_in_expected_order()
    {
        $this->seed(CategorySeeder::class);
        $results = Category::all();
        $expected = Category::query()->orderBy('left')->get();

        $this->assertEquals($results, $expected);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_returns_an_ordered_collection_in_expected_order()
    {
        $this->seed(OrderedCategorySeeder::class);
        $results = OrderedCategory::all();
        $expected = OrderedCategory::query()->orderBy('name')->get();

        $this->assertEquals($results, $expected);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_roots_static()
    {
        $this->seed(CategorySeeder::class);

        $query = Category::whereNull('parent_id')->get();
        $roots = Category::roots()->get();

        $this->assertEquals($query->count(), $roots->count());
        $this->assertCount(2, $roots);

        foreach ($query->pluck('id') as $node) {
            $this->assertContains($node, $roots->pluck('id'));
        }
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_roots_static_with_custom_order()
    {
        $this->seed(OrderedCategorySeeder::class);

        $category = create(OrderedCategory::class, [
            'name' => 'A new root is born',
            'parent_id' => null,
        ]);

        $roots = OrderedCategory::roots()->get();

        $this->assertCount(3, $roots);
        $this->assertEquals(
            $category->fresh()->getAttributes(),
            $roots->first()->fresh()->getAttributes()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_root_static()
    {
        $this->seed(CategorySeeder::class);
        $this->assertEquals(Category::root(), Category::firstWhere('name', 'Root 1'));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_all_leaves_static()
    {
        $this->seed(CategorySeeder::class);
        $allLeaves = Category::allLeaves()->get();

        $this->assertCount(4, $allLeaves);

        $leaves = $allLeaves->pluck('name');

        $this->assertContains('Child 1', $leaves);
        $this->assertContains('Child 2.1', $leaves);
        $this->assertContains('Child 3', $leaves);
        $this->assertContains('Root 2', $leaves);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_all_trunks_static()
    {
        $this->seed(CategorySeeder::class);
        $allTrunks = Category::allTrunks()->get();

        $this->assertCount(1, $allTrunks);

        $trunks = $allTrunks->pluck('name');
        $this->assertContains('Child 2', $trunks);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_root()
    {
        $this->seed(CategorySeeder::class);
        $this->assertEquals(
            Category::firstWhere('name', 'Root 1'),
            Category::firstWhere('name', 'Root 1')->getRoot()
        );
        $this->assertEquals(
            Category::firstWhere('name', 'Root 2'),
            Category::firstWhere('name', 'Root 2')->getRoot()
        );
        $this->assertEquals(
            Category::firstWhere('name', 'Root 1'),
            Category::firstWhere('name', 'Child 1')->getRoot()
        );
        $this->assertEquals(
            Category::firstWhere('name', 'Root 1'),
            Category::firstWhere('name', 'Child 2')->getRoot()
        );
        $this->assertEquals(
            Category::firstWhere('name', 'Root 1'),
            Category::firstWhere('name', 'Child 2.1')->getRoot()
        );
        $this->assertEquals(
            Category::firstWhere('name', 'Root 1'),
            Category::firstWhere('name', 'Child 3')->getRoot()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_root_equals_self_if_unpersisted()
    {
        $category = new Category();

        $this->assertEquals($category->getRoot(), $category);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_root_equals_value_if_set_if_unpersisted()
    {
        $this->seed(CategorySeeder::class);
        $parent = Category::roots()->first();

        $child = new Category();
        $child->setAttribute($child->getParentColumnName(), $parent->getKey());

        $this->assertEquals($child->getRoot(), $parent);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_root()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::firstWhere('name', 'Root 1')->isRoot());
        $this->assertTrue(Category::firstWhere('name', 'Root 2')->isRoot());

        $this->assertFalse(Category::firstWhere('name', 'Child 1')->isRoot());
        $this->assertFalse(Category::firstWhere('name', 'Child 2')->isRoot());
        $this->assertFalse(Category::firstWhere('name', 'Child 2.1')->isRoot());
        $this->assertFalse(Category::firstWhere('name', 'Child 3')->isRoot());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_leaves()
    {
        $this->seed(CategorySeeder::class);
        $leaves = [Category::firstWhere('name', 'Child 1'), Category::firstWhere('name', 'Child 2.1'), Category::firstWhere('name', 'Child 3')];

        $this->assertEquals($leaves, Category::firstWhere('name', 'Root 1')->getLeaves()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_leaves_in_iteration()
    {
        $this->seed(CategorySeeder::class);
        $node = Category::firstWhere('name', 'Root 1');

        $expectedIds = [2, 4, 5];

        foreach ($node->getLeaves() as $i => $leaf) {
            $this->assertEquals($expectedIds[$i], $leaf->getKey());
        }
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_trunks()
    {
        $this->seed(CategorySeeder::class);
        $trunks = [Category::firstWhere('name', 'Child 2')];

        $this->assertEquals($trunks, Category::firstWhere('name', 'Root 1')->getTrunks()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_trunks_in_iteration()
    {
        $this->seed(CategorySeeder::class);
        $node = Category::firstWhere('name', 'Root 1');

        $expectedIds = [3];

        foreach ($node->getTrunks() as $i => $trunk) {
            $this->assertEquals($expectedIds[$i], $trunk->getKey());
        }
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_leaf()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::firstWhere('name', 'Child 1')->isLeaf());
        $this->assertTrue(Category::firstWhere('name', 'Child 2.1')->isLeaf());
        $this->assertTrue(Category::firstWhere('name', 'Child 3')->isLeaf());
        $this->assertTrue(Category::firstWhere('name', 'Root 2')->isLeaf());

        $this->assertFalse(Category::firstWhere('name', 'Root 1')->isLeaf());
        $this->assertFalse(Category::firstWhere('name', 'Child 2')->isLeaf());

        $new = new Category();
        $this->assertFalse($new->isLeaf());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_trunk()
    {
        $this->seed(CategorySeeder::class);
        $this->assertFalse(Category::firstWhere('name', 'Child 1')->isTrunk());
        $this->assertFalse(Category::firstWhere('name', 'Child 2.1')->isTrunk());
        $this->assertFalse(Category::firstWhere('name', 'Child 3')->isTrunk());
        $this->assertFalse(Category::firstWhere('name', 'Root 2')->isTrunk());

        $this->assertFalse(Category::firstWhere('name', 'Root 1')->isTrunk());
        $this->assertTrue(Category::firstWhere('name', 'Child 2')->isTrunk());

        $new = new Category();
        $this->assertFalse($new->isTrunk());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_without_node_scope()
    {
        $this->seed(CategorySeeder::class);
        $child = Category::firstWhere('name', 'Child 2.1');

        $expected = [Category::firstWhere('name', 'Root 1'), $child];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutNode(Category::firstWhere('name', 'Child 2'))->get()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_without_self_scope()
    {
        $this->seed(CategorySeeder::class);
        $child = Category::firstWhere('name', 'Child 2.1');

        $expected = [Category::firstWhere('name', 'Root 1'), Category::firstWhere('name', 'Child 2')];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutSelf()->get()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_without_root_scope()
    {
        $this->seed(CategorySeeder::class);
        $child = Category::firstWhere('name', 'Child 2.1');

        $expected = [Category::firstWhere('name', 'Child 2'), $child];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutRoot()->get()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_limit_depth_scope()
    {
        $this->seed(CategorySeeder::class);
        (new CategorySeeder())->nestUptoAt(Category::firstWhere('name', 'Child 2.1'), 10);

        $node = Category::firstWhere('name', 'Child 2');

        $descendancy = $node->descendants()->pluck('id')->toArray();
        // dd($descendancy);
        $this->assertEmpty($node->descendants()->limitDepth(0)->pluck('id'));
        $this->assertEquals($node, $node->descendantsAndSelf()->limitDepth(0)->first());

        $this->assertEquals(
            array_slice($descendancy, 0, 3),
            $node->descendants()->limitDepth(3)->pluck('id')->toArray()
        );
        $this->assertEquals(
            array_slice($descendancy, 0, 5),
            $node->descendants()->limitDepth(5)->pluck('id')->toArray()
        );
        $this->assertEquals(
            array_slice($descendancy, 0, 7),
            $node->descendants()->limitDepth(7)->pluck('id')->toArray()
        );
        $this->assertEquals(
            $descendancy,
            $node->descendants()->limitDepth(1000)->pluck('id')->toArray()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_ancestors_and_self()
    {
        $this->seed(CategorySeeder::class);
        $child = Category::firstWhere('name', 'Child 2.1');

        $expected = [Category::firstWhere('name', 'Root 1'), Category::firstWhere('name', 'Child 2'), $child];

        $this->assertEquals($expected, $child->getAncestorsAndSelf()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_ancestors_and_self_without_root()
    {
        $this->seed(CategorySeeder::class);
        $child = Category::firstWhere('name', 'Child 2.1');

        $expected = [Category::firstWhere('name', 'Child 2'), $child];

        $this->assertEquals($expected, $child->getAncestorsAndSelfWithoutRoot()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_ancestors()
    {
        $this->seed(CategorySeeder::class);
        $child = Category::firstWhere('name', 'Child 2.1');

        $expected = [Category::firstWhere('name', 'Root 1'), Category::firstWhere('name', 'Child 2')];

        $this->assertEquals($expected, $child->getAncestors()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_ancestors_without_root()
    {
        $this->seed(CategorySeeder::class);
        $child = Category::firstWhere('name', 'Child 2.1');

        $expected = [Category::firstWhere('name', 'Child 2')];

        $this->assertEquals($expected, $child->getAncestorsWithoutRoot()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_descendants_and_self()
    {
        $this->seed(CategorySeeder::class);
        $parent = Category::firstWhere('name', 'Root 1');

        $expected = [
            $parent,
            Category::firstWhere('name', 'Child 1'),
            Category::firstWhere('name', 'Child 2'),
            Category::firstWhere('name', 'Child 2.1'),
            Category::firstWhere('name', 'Child 3'),
        ];

        $this->assertCount(count($expected), $parent->getDescendantsAndSelf());

        $this->assertEquals($expected, $parent->getDescendantsAndSelf()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_descendants_and_self_with_limit()
    {
        $this->seed(CategorySeeder::class);
        with(new CategorySeeder())->nestUptoAt(Category::firstWhere('name', 'Child 2.1'), 3);

        $parent = Category::firstWhere('name', 'Root 1');

        $this->assertEquals([$parent], $parent->getDescendantsAndSelf(0)->all());

        $this->assertEquals([
      $parent,
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendantsAndSelf(1)->all());

        $this->assertEquals([
      $parent,
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 2.1'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendantsAndSelf(2)->all());

        $this->assertEquals([
      $parent,
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 2.1'),
      Category::firstWhere('name', 'Child 2.1.1'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendantsAndSelf(3)->all());

        $this->assertEquals([
      $parent,
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 2.1'),
      Category::firstWhere('name', 'Child 2.1.1'),
      Category::firstWhere('name', 'Child 2.1.1.1'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendantsAndSelf(4)->all());

        $this->assertEquals([
      $parent,
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 2.1'),
      Category::firstWhere('name', 'Child 2.1.1'),
      Category::firstWhere('name', 'Child 2.1.1.1'),
      Category::firstWhere('name', 'Child 2.1.1.1.1'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendantsAndSelf(10)->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_descendants()
    {
        $this->seed(CategorySeeder::class);
        $parent = Category::firstWhere('name', 'Root 1');

        $expected = [
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 2.1'),
      Category::firstWhere('name', 'Child 3'),
    ];

        $this->assertCount(count($expected), $parent->getDescendants());

        $this->assertEquals($expected, $parent->getDescendants()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_descendants_with_limit()
    {
        $this->seed(CategorySeeder::class);
        with(new CategorySeeder())->nestUptoAt(Category::firstWhere('name', 'Child 2.1'), 3);

        $parent = Category::firstWhere('name', 'Root 1');

        $this->assertEmpty($parent->getDescendants(0)->all());

        $this->assertEquals([
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendants(1)->all());

        $this->assertEquals([
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 2.1'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendants(2)->all());

        $this->assertEquals([
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 2.1'),
      Category::firstWhere('name', 'Child 2.1.1'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendants(3)->all());

        $this->assertEquals([
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 2.1'),
      Category::firstWhere('name', 'Child 2.1.1'),
      Category::firstWhere('name', 'Child 2.1.1.1'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendants(4)->all());

        $this->assertEquals([
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 2.1'),
      Category::firstWhere('name', 'Child 2.1.1'),
      Category::firstWhere('name', 'Child 2.1.1.1'),
      Category::firstWhere('name', 'Child 2.1.1.1.1'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendants(5)->all());

        $this->assertEquals([
      Category::firstWhere('name', 'Child 1'),
      Category::firstWhere('name', 'Child 2'),
      Category::firstWhere('name', 'Child 2.1'),
      Category::firstWhere('name', 'Child 2.1.1'),
      Category::firstWhere('name', 'Child 2.1.1.1'),
      Category::firstWhere('name', 'Child 2.1.1.1.1'),
      Category::firstWhere('name', 'Child 3'),
    ], $parent->getDescendants(10)->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_descendants_recurses_children()
    {
        $this->seed(CategorySeeder::class);
        $a = Category::create(['name' => 'A']);
        $b = Category::create(['name' => 'B']);
        $c = Category::create(['name' => 'C']);

        // a > b > c
        $b->makeChildOf($a);
        $c->makeChildOf($b);

        $a->reload();
        $b->reload();
        $c->reload();

        $this->assertEquals(1, $a->children()->count());
        $this->assertEquals(1, $b->children()->count());
        $this->assertEquals(2, $a->descendants()->count());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_immediate_descendants()
    {
        $this->seed(CategorySeeder::class);
        $expected = [Category::firstWhere('name', 'Child 1'), Category::firstWhere('name', 'Child 2'), Category::firstWhere('name', 'Child 3')];

        $this->assertEquals($expected, Category::firstWhere('name', 'Root 1')->getImmediateDescendants()->all());

        $this->assertEquals([Category::firstWhere('name', 'Child 2.1')], Category::firstWhere('name', 'Child 2')->getImmediateDescendants()->all());

        $this->assertEmpty(Category::firstWhere('name', 'Root 2')->getImmediateDescendants()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_self_or_ancestor_of()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::firstWhere('name', 'Root 1')->isSelfOrAncestorOf(Category::firstWhere('name', 'Child 1')));
        $this->assertTrue(Category::firstWhere('name', 'Root 1')->isSelfOrAncestorOf(Category::firstWhere('name', 'Child 2.1')));
        $this->assertTrue(Category::firstWhere('name', 'Child 2')->isSelfOrAncestorOf(Category::firstWhere('name', 'Child 2.1')));
        $this->assertFalse(Category::firstWhere('name', 'Child 2.1')->isSelfOrAncestorOf(Category::firstWhere('name', 'Child 2')));
        $this->assertFalse(Category::firstWhere('name', 'Child 1')->isSelfOrAncestorOf(Category::firstWhere('name', 'Child 2')));
        $this->assertTrue(Category::firstWhere('name', 'Child 1')->isSelfOrAncestorOf(Category::firstWhere('name', 'Child 1')));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_ancestor_of()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::firstWhere('name', 'Root 1')->isAncestorOf(Category::firstWhere('name', 'Child 1')));
        $this->assertTrue(Category::firstWhere('name', 'Root 1')->isAncestorOf(Category::firstWhere('name', 'Child 2.1')));
        $this->assertTrue(Category::firstWhere('name', 'Child 2')->isAncestorOf(Category::firstWhere('name', 'Child 2.1')));
        $this->assertFalse(Category::firstWhere('name', 'Child 2.1')->isAncestorOf(Category::firstWhere('name', 'Child 2')));
        $this->assertFalse(Category::firstWhere('name', 'Child 1')->isAncestorOf(Category::firstWhere('name', 'Child 2')));
        $this->assertFalse(Category::firstWhere('name', 'Child 1')->isAncestorOf(Category::firstWhere('name', 'Child 1')));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_self_or_descendant_of()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::firstWhere('name', 'Child 1')->isSelfOrDescendantOf(Category::firstWhere('name', 'Root 1')));
        $this->assertTrue(Category::firstWhere('name', 'Child 2.1')->isSelfOrDescendantOf(Category::firstWhere('name', 'Root 1')));
        $this->assertTrue(Category::firstWhere('name', 'Child 2.1')->isSelfOrDescendantOf(Category::firstWhere('name', 'Child 2')));
        $this->assertFalse(Category::firstWhere('name', 'Child 2')->isSelfOrDescendantOf(Category::firstWhere('name', 'Child 2.1')));
        $this->assertFalse(Category::firstWhere('name', 'Child 2')->isSelfOrDescendantOf(Category::firstWhere('name', 'Child 1')));
        $this->assertTrue(Category::firstWhere('name', 'Child 1')->isSelfOrDescendantOf(Category::firstWhere('name', 'Child 1')));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_descendant_of()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::firstWhere('name', 'Child 1')->isDescendantOf(Category::firstWhere('name', 'Root 1')));
        $this->assertTrue(Category::firstWhere('name', 'Child 2.1')->isDescendantOf(Category::firstWhere('name', 'Root 1')));
        $this->assertTrue(Category::firstWhere('name', 'Child 2.1')->isDescendantOf(Category::firstWhere('name', 'Child 2')));
        $this->assertFalse(Category::firstWhere('name', 'Child 2')->isDescendantOf(Category::firstWhere('name', 'Child 2.1')));
        $this->assertFalse(Category::firstWhere('name', 'Child 2')->isDescendantOf(Category::firstWhere('name', 'Child 1')));
        $this->assertFalse(Category::firstWhere('name', 'Child 1')->isDescendantOf(Category::firstWhere('name', 'Child 1')));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_siblings_and_self()
    {
        $this->seed(CategorySeeder::class);
        $child = Category::firstWhere('name', 'Child 2');

        $expected = [Category::firstWhere('name', 'Child 1'), $child, Category::firstWhere('name', 'Child 3')];
        $this->assertEquals($expected, $child->getSiblingsAndSelf()->all());

        $expected = [Category::firstWhere('name', 'Root 1'), Category::firstWhere('name', 'Root 2')];
        $this->assertEquals($expected, Category::firstWhere('name', 'Root 1')->getSiblingsAndSelf()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_siblings()
    {
        $this->seed(CategorySeeder::class);
        $child = Category::firstWhere('name', 'Child 2');

        $expected = [Category::firstWhere('name', 'Child 1'), Category::firstWhere('name', 'Child 3')];

        $this->assertEquals($expected, $child->getSiblings()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_left_sibling()
    {
        $this->seed(CategorySeeder::class);
        $this->assertEquals(Category::firstWhere('name', 'Child 1'), Category::firstWhere('name', 'Child 2')->getLeftSibling());
        $this->assertEquals(Category::firstWhere('name', 'Child 2'), Category::firstWhere('name', 'Child 3')->getLeftSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_left_sibling_of_first_root_is_null()
    {
        $this->seed(CategorySeeder::class);
        $this->assertNull(Category::firstWhere('name', 'Root 1')->getLeftSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_left_sibling_with_none_is_null()
    {
        $this->seed(CategorySeeder::class);
        $this->assertNull(Category::firstWhere('name', 'Child 2.1')->getLeftSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_left_sibling_of_leftmost_node_is_null()
    {
        $this->seed(CategorySeeder::class);
        $this->assertNull(Category::firstWhere('name', 'Child 1')->getLeftSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_right_sibling()
    {
        $this->seed(CategorySeeder::class);
        $this->assertEquals(Category::firstWhere('name', 'Child 3'), Category::firstWhere('name', 'Child 2')->getRightSibling());
        $this->assertEquals(Category::firstWhere('name', 'Child 2'), Category::firstWhere('name', 'Child 1')->getRightSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_right_sibling_of_roots()
    {
        $this->seed(CategorySeeder::class);
        $this->assertEquals(Category::firstWhere('name', 'Root 2'), Category::firstWhere('name', 'Root 1')->getRightSibling());
        $this->assertNull(Category::firstWhere('name', 'Root 2')->getRightSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_right_sibling_with_none_is_null()
    {
        $this->seed(CategorySeeder::class);
        $this->assertNull(Category::firstWhere('name', 'Child 2.1')->getRightSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_right_sibling_of_rightmost_node_is_null()
    {
        $this->seed(CategorySeeder::class);
        $this->assertNull(Category::firstWhere('name', 'Child 3')->getRightSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_inside_subtree()
    {
        $this->seed(CategorySeeder::class);
        $this->assertFalse(Category::firstWhere('name', 'Child 1')->insideSubtree(Category::firstWhere('name', 'Root 2')));
        $this->assertFalse(Category::firstWhere('name', 'Child 2')->insideSubtree(Category::firstWhere('name', 'Root 2')));
        $this->assertFalse(Category::firstWhere('name', 'Child 3')->insideSubtree(Category::firstWhere('name', 'Root 2')));

        $this->assertTrue(Category::firstWhere('name', 'Child 1')->insideSubtree(Category::firstWhere('name', 'Root 1')));
        $this->assertTrue(Category::firstWhere('name', 'Child 2')->insideSubtree(Category::firstWhere('name', 'Root 1')));
        $this->assertTrue(Category::firstWhere('name', 'Child 2.1')->insideSubtree(Category::firstWhere('name', 'Root 1')));
        $this->assertTrue(Category::firstWhere('name', 'Child 3')->insideSubtree(Category::firstWhere('name', 'Root 1')));

        $this->assertTrue(Category::firstWhere('name', 'Child 2.1')->insideSubtree(Category::firstWhere('name', 'Child 2')));
        $this->assertFalse(Category::firstWhere('name', 'Child 2.1')->insideSubtree(Category::firstWhere('name', 'Root 2')));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_level()
    {
        $this->seed(CategorySeeder::class);
        $this->assertEquals(0, Category::firstWhere('name', 'Root 1')->getLevel());
        $this->assertEquals(1, Category::firstWhere('name', 'Child 1')->getLevel());
        $this->assertEquals(2, Category::firstWhere('name', 'Child 2.1')->getLevel());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_returns_an_eloquent_collection()
    {
        $this->seed(CategorySeeder::class);
        $categories = Category::all()->toHierarchy();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $categories);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_returns_hierarchical_data()
    {
        $this->seed(CategorySeeder::class);
        $categories = Category::all()->toHierarchy();

        $this->assertEquals(2, $categories->count());

        $first = $categories->first();
        $this->assertEquals('Root 1', $first->name);
        $this->assertEquals(3, $first->children->count());

        $first_lvl2 = $first->children->first();
        $this->assertEquals('Child 1', $first_lvl2->name);
        $this->assertEquals(0, $first_lvl2->children->count());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_nests_correctly()
    {
        $this->seed(CategorySeeder::class);
        // Prune all categories
        Category::query()->delete();

        // Build a sample tree structure:
        //
        //   - A
        //     |- A.1
        //     |- A.2
        //   - B
        //     |- B.1
        //     |- B.2
        //         |- B.2.1
        //         |- B.2.2
        //           |- B.2.2.1
        //         |- B.2.3
        //     |- B.3
        //   - C
        //     |- C.1
        //     |- C.2
        //   - D
        //
        $a = Category::create(['name' => 'A']);
        $b = Category::create(['name' => 'B']);
        $c = Category::create(['name' => 'C']);
        $d = Category::create(['name' => 'D']);

        $ch = Category::create(['name' => 'A.1']);
        $ch->makeChildOf($a);

        $ch = Category::create(['name' => 'A.2']);
        $ch->makeChildOf($a);

        $ch = Category::create(['name' => 'B.1']);
        $ch->makeChildOf($b);

        $ch = Category::create(['name' => 'B.2']);
        $ch->makeChildOf($b);

        $ch2 = Category::create(['name' => 'B.2.1']);
        $ch2->makeChildOf($ch);

        $ch2 = Category::create(['name' => 'B.2.2']);
        $ch2->makeChildOf($ch);

        $ch3 = Category::create(['name' => 'B.2.2.1']);
        $ch3->makeChildOf($ch2);

        $ch2 = Category::create(['name' => 'B.2.3']);
        $ch2->makeChildOf($ch);

        $ch = Category::create(['name' => 'B.3']);
        $ch->makeChildOf($b);

        $ch = Category::create(['name' => 'C.1']);
        $ch->makeChildOf($c);

        $ch = Category::create(['name' => 'C.2']);
        $ch->makeChildOf($c);

        $this->assertTrue(Category::isValidNestedSet());

        // Build expectations (expected trees/subtrees)
        $expectedWholeTree = [
      'A' => ['A.1' => null, 'A.2' => null],
      'B' => [
        'B.1' => null,
        'B.2' =>
        [
          'B.2.1' => null,
          'B.2.2' => ['B.2.2.1' => null],
          'B.2.3' => null,
        ],
        'B.3' => null,
      ],
      'C' => ['C.1' => null, 'C.2' => null],
      'D' => null,
    ];

        $expectedSubtreeA = ['A' => ['A.1' => null, 'A.2' => null]];

        $expectedSubtreeB = [
      'B' => [
        'B.1' => null,
        'B.2' =>
        [
          'B.2.1' => null,
          'B.2.2' => ['B.2.2.1' => null],
          'B.2.3' => null,
        ],
        'B.3' => null,
      ],
    ];

        $expectedSubtreeC = ['C.1' => null, 'C.2' => null];

        $expectedSubtreeD = ['D' => null];

        // Perform assertions
        $wholeTree = hmap(Category::all()->toHierarchy()->toArray());
        $this->assertSame($expectedWholeTree, $wholeTree);

        $subtreeA = hmap(Category::firstWhere('name', 'A')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertSame($expectedSubtreeA, $subtreeA);

        $subtreeB = hmap(Category::firstWhere('name', 'B')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertSame($expectedSubtreeB, $subtreeB);

        $subtreeC = hmap(Category::firstWhere('name', 'C')->getDescendants()->toHierarchy()->toArray());
        $this->assertSame($expectedSubtreeC, $subtreeC);

        $subtreeD = hmap(Category::firstWhere('name', 'D')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertSame($expectedSubtreeD, $subtreeD);

        $this->assertTrue(Category::firstWhere('name', 'D')->getDescendants()->toHierarchy()->isEmpty());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_nests_correctly_not_sequential()
    {
        $this->seed(CategorySeeder::class);
        $parent = Category::firstWhere('name', 'Child 1');

        $parent->children()->create(['name' => 'Child 1.1']);

        $parent->children()->create(['name' => 'Child 1.2']);

        $this->assertTrue(Category::isValidNestedSet());

        $expected = [
      'Child 1' => [
        'Child 1.1' => null,
        'Child 1.2' => null,
      ],
    ];

        $parent->reload();
        $this->assertSame($expected, hmap($parent->getDescendantsAndSelf()->toHierarchy()->toArray()));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_nests_correctly_with_order()
    {
        $this->seed(OrderedCategorySeeder::class);

        $expectedWhole = [
            'Root A' => null,
            'Root Z' => [
                'Child A' => null,
                'Child C' => null,
                'Child G' => [
                    'Child G.1' => null,
                ],
            ],
        ];

        $this->assertSame(
            $expectedWhole,
            hmap(OrderedCategory::all()->toHierarchy()->toArray())
        );

        $expectedSubtreeZ = [
            'Root Z' => [
                'Child A' => null,
                'Child C' => null,
                'Child G' => [
                    'Child G.1' => null,
                ],
            ],
        ];
        $this->assertSame(
            $expectedSubtreeZ,
            hmap(OrderedCategory::firstWhere('name', 'Root Z')
                ->getDescendantsAndSelf()
                ->toHierarchy()->toArray()
            )
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_nested_list()
    {
        $this->seed(CategorySeeder::class);
        $seperator = ' ';
        $nestedList = Category::getNestedList('name', 'id', $seperator);

        $expected = [
      1 => str_repeat($seperator, 0).'Root 1',
      2 => str_repeat($seperator, 1).'Child 1',
      3 => str_repeat($seperator, 1).'Child 2',
      4 => str_repeat($seperator, 2).'Child 2.1',
      5 => str_repeat($seperator, 1).'Child 3',
      6 => str_repeat($seperator, 0).'Root 2',
    ];

        $this->assertSame($expected, $nestedList);
    }
}
