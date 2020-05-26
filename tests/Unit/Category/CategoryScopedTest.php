<?php

namespace Encima\Albero\Tests\Unit\Category;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\MoveNotPossibleException;
use Encima\Albero\Tests\Models\Category\ScopedCategory;
use Encima\Albero\Tests\Models\Category\MultiScopedCategory;
use Encima\Albero\Tests\Models\Category\OrderedScopedCategory;
use Encima\Albero\Tests\Seeders\Category\ScopedCategorySeeder;
use Encima\Albero\Tests\Seeders\Category\MultiScopedCategorySeeder;
use Encima\Albero\Tests\Seeders\Category\OrderedScopedCategorySeeder;

class CategoryScopedTest extends TestCase
{
    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_in_same_scope()
    {
        $this->seed(MultiScopedCategorySeeder::class);
        $root1 = ScopedCategory::firstWhere('name', 'Root 1');
        $child1 = ScopedCategory::firstWhere('name', 'Child 1');
        $child2 = ScopedCategory::firstWhere('name', 'Child 2');

        $root2 = ScopedCategory::firstWhere('name', 'Root 2');
        $child4 = ScopedCategory::firstWhere('name', 'Child 4');
        $child5 = ScopedCategory::firstWhere('name', 'Child 5');

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $this->assertTrue($root1->inSameScope($child1));
        $this->assertTrue($child1->inSameScope($child2));
        $this->assertTrue($child2->inSameScope($root1));

        $this->assertTrue($root2->inSameScope($child4));
        $this->assertTrue($child4->inSameScope($child5));
        $this->assertTrue($child5->inSameScope($root2));

        $this->assertFalse($root1->inSameScope($root2));
        $this->assertFalse($root2->inSameScope($root1));

        $this->assertFalse($child1->inSameScope($child4));
        $this->assertFalse($child4->inSameScope($child1));

        $this->assertFalse($child2->inSameScope($child5));
        $this->assertFalse($child5->inSameScope($child2));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_in_same_scope_multiple()
    {
        $this->seed(MultiScopedCategorySeeder::class);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $child1 = MultiScopedCategory::firstWhere('name', 'Child 1');
        $child2 = MultiScopedCategory::firstWhere('name', 'Child 2');

        $child4 = MultiScopedCategory::firstWhere('name', 'Child 4');
        $child5 = MultiScopedCategory::firstWhere('name', 'Child 5');

        $enfant1 = MultiScopedCategory::firstWhere('name', 'Enfant 1');
        $enfant2 = MultiScopedCategory::firstWhere('name', 'Enfant 2');

        $hijo1 = MultiScopedCategory::firstWhere('name', 'Hijo 1');
        $hijo2 = MultiScopedCategory::firstWhere('name', 'Hijo 2');

        $this->assertTrue($child1->inSameScope($child2));
        $this->assertTrue($child4->inSameScope($child5));
        $this->assertTrue($enfant1->inSameScope($enfant2));
        $this->assertTrue($hijo1->inSameScope($hijo2));

        $this->assertFalse($child2->inSameScope($child4));
        $this->assertFalse($child5->inSameScope($enfant1));
        $this->assertFalse($enfant2->inSameScope($hijo1));
        $this->assertFalse($hijo2->inSameScope($child1));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_self_or_ancestor_of()
    {
        $this->seed(ScopedCategorySeeder::class);
        $root1 = ScopedCategory::firstWhere('name', 'Root 1');
        $child21 = ScopedCategory::firstWhere('name', 'Child 2.1');

        $root2 = ScopedCategory::firstWhere('name', 'Root 2');
        $child51 = ScopedCategory::firstWhere('name', 'Child 5.1');

        $this->assertTrue($root1->isSelfOrAncestorOf($child21));
        $this->assertTrue($root2->isSelfOrAncestorOf($child51));

        $this->assertFalse($root1->isSelfOrAncestorOf($child51));
        $this->assertFalse($root2->isSelfOrAncestorOf($child21));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_self_or_descendant_of()
    {
        $this->seed(ScopedCategorySeeder::class);
        $root1 = ScopedCategory::firstWhere('name', 'Root 1');
        $child21 = ScopedCategory::firstWhere('name', 'Child 2.1');

        $root2 = ScopedCategory::firstWhere('name', 'Root 2');
        $child51 = ScopedCategory::firstWhere('name', 'Child 5.1');

        $this->assertTrue($child21->isSelfOrDescendantOf($root1));
        $this->assertTrue($child51->isSelfOrDescendantOf($root2));

        $this->assertFalse($child21->isSelfOrDescendantOf($root2));
        $this->assertFalse($child51->isSelfOrDescendantOf($root1));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_siblings_and_self()
    {
        $this->seed(ScopedCategorySeeder::class);

        $root2 = ScopedCategory::firstWhere('name', 'Root 2');
        $child1 = ScopedCategory::firstWhere('name', 'Child 1');
        $child2 = ScopedCategory::firstWhere('name', 'Child 2');
        $child3 = ScopedCategory::firstWhere('name', 'Child 3');

        $expected = [$root2];
        $this->assertEquals($expected, $root2->getSiblingsAndSelf()->all());

        $expected = [$child1, $child2, $child3];
        $this->assertEquals($expected, $child2->getSiblingsAndSelf()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_siblings_and_self_multiple()
    {
        $this->seed(MultiScopedCategorySeeder::class);

        $root1 = MultiScopedCategory::firstWhere('name', 'Racine 1');
        $child1 = MultiScopedCategory::firstWhere('name', 'Hijo 1');
        $child2 = MultiScopedCategory::firstWhere('name', 'Hijo 2');
        $child3 = MultiScopedCategory::firstWhere('name', 'Hijo 3');

        $expected = [$root1];
        $this->assertEquals($expected, $root1->getSiblingsAndSelf()->all());

        $expected = [$child1, $child2, $child3];
        $this->assertEquals($expected, $child3->getSiblingsAndSelf()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_simple_movements()
    {
        $this->seed(ScopedCategorySeeder::class);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root = create(ScopedCategory::class, [
          'parent_id' => null,
          'name' => 'Root 3',
          'company_id' => 2,
        ]);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $child = ScopedCategory::firstWhere('name', 'Child 6');

        $child->makeChildOf($root);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root->fresh();
        $expected = [ScopedCategory::firstWhere('name', 'Child 6')];
        $this->assertEquals($expected, $root->children()->get()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_simple_subtree_movements()
    {
        $this->seed(ScopedCategorySeeder::class);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root = create(ScopedCategory::class, [
            'name' => 'Root 3',
            'parent_id' => null,
            'company_id' => 2,
        ]);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $child = ScopedCategory::firstWhere('name', 'Child 5');
        $child->makeChildOf($root);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root->reload();
        $expected = [
            ScopedCategory::firstWhere('name', 'Child 5'),
            ScopedCategory::firstWhere('name', 'Child 5.1'),
        ];
        $this->assertEquals($expected, $root->getDescendants()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_full_subtree_movements()
    {
        $this->seed(ScopedCategorySeeder::class);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root = create(ScopedCategory::class, [
            'name' => 'Root 3',
            'parent_id' => null,
            'company_id' => 2,
        ]);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $child = ScopedCategory::firstWhere('name', 'Root 2');
        $child->makeChildOf($root);
        $this->assertTrue(ScopedCategory::isValidNestedSet());

        $root->reload();
        $expected = [
            ScopedCategory::firstWhere('name', 'Root 2'),
            ScopedCategory::firstWhere('name', 'Child 4'),
            ScopedCategory::firstWhere('name', 'Child 5'),
            ScopedCategory::firstWhere('name', 'Child 5.1'),
            ScopedCategory::firstWhere('name', 'Child 6'),
        ];
        $this->assertEquals($expected, $root->getDescendants()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_simple_movements_multiple()
    {
        $this->seed(MultiScopedCategorySeeder::class);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root = create(MultiScopedCategory::class, [
            'parent_id' => null,
            'name' => 'Raiz 2',
            'company_id' => 3,
            'language' => 'es',
        ]);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $child = MultiScopedCategory::firstWhere('name', 'Hijo 1');
        $child->makeChildOf($root);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root->reload();
        $expected = [MultiScopedCategory::firstWhere('name', 'Hijo 1')];
        $this->assertEquals($expected, $root->children()->get()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_simple_subtree_movements_multiple()
    {
        $this->seed(MultiScopedCategorySeeder::class);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root = create(MultiScopedCategory::class, [
            'parent_id' => null,
            'name' => 'Raiz 2',
            'company_id' => 3,
            'language' => 'es',
        ]);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $child = MultiScopedCategory::firstWhere('name', 'Hijo 2');
        $child->makeChildOf($root);

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root->reload();
        $expected = [
            MultiScopedCategory::firstWhere('name', 'Hijo 2'),
            MultiScopedCategory::firstWhere('name', 'Hijo 2.1'),
        ];
        $this->assertEquals($expected, $root->getDescendants()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_full_subtree_movements_multiple()
    {
        $this->seed(MultiScopedCategorySeeder::class);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root = create(MultiScopedCategory::class, [
            'parent_id' => null,
            'name' => 'Raiz 2',
            'company_id' => 3,
            'language' => 'es',
        ]);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $child = MultiScopedCategory::firstWhere('name', 'Raiz 1');
        $child->makeChildOf($root);
        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $root->reload();
        $expected = [
            MultiScopedCategory::firstWhere('name', 'Raiz 1'),
            MultiScopedCategory::firstWhere('name', 'Hijo 1'),
            MultiScopedCategory::firstWhere('name', 'Hijo 2'),
            MultiScopedCategory::firstWhere('name', 'Hijo 2.1'),
            MultiScopedCategory::firstWhere('name', 'Hijo 3'),
        ];
        $this->assertEquals($expected, $root->getDescendants()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_nests_correctly_with_scoped_order()
    {
        $this->seed(OrderedScopedCategorySeeder::class);

        $expectedWhole1 = [
            'Root 1' => [
                'Child 1' => null,
                'Child 2' => [
                    'Child 2.1' => null,
                ],
                'Child 3' => null,
            ],
        ];

        $expectedWhole2 = [
            'Root 2' => [
                'Child 4' => null,
                'Child 5' => [
                    'Child 5.1' => null,
                ],
                'Child 6' => null,
            ],
        ];

        $this->assertEquals(
            $expectedWhole1,
            hmap(OrderedScopedCategory::where('company_id', 1)->get()->toHierarchy()->toArray())
        );
        $this->assertEquals(
            $expectedWhole2,
            hmap(OrderedScopedCategory::where('company_id', 2)->get()->toHierarchy()->toArray())
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nodes_cannot_move_between_scopes()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->seed(ScopedCategorySeeder::class);
        $child4 = ScopedCategory::firstWhere('name', 'Child 4');
        $root1 = ScopedCategory::firstWhere('name', 'Root 1');

        $child4->makeChildOf($root1);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nodes_cannot_move_between_scopes_multiple()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->seed(MultiScopedCategorySeeder::class);
        $root1 = MultiScopedCategory::firstWhere('name', 'Root 1');
        $child4 = MultiScopedCategory::firstWhere('name', 'Child 4');

        $child4->makeChildOf($root1);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nodes_cannot_move_between_scopes_multiple2()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->seed(MultiScopedCategorySeeder::class);
        $root1 = MultiScopedCategory::firstWhere('name', 'Racine 1');
        $child2 = MultiScopedCategory::firstWhere('name', 'Hijo 2');

        $child2->makeChildOf($root1);
    }

    // TODO: Moving nodes between scopes is problematic ATM. Fix it or find a work-around.

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_node_between_scopes()
    {
        $this->markTestSkipped();

        // $root1    = Menu::create(array('caption' => 'TL1', 'site_id' => 1, 'language' => 'en'));
    // $child11  = Menu::create(array('caption' => 'C11', 'site_id' => 1, 'language' => 'en'));
    // $child12  = Menu::create(array('caption' => 'C12', 'site_id' => 1, 'language' => 'en'));

    // $this->assertTrue(Menu::isValidNestedSet());

    // $child11->makeChildOf($root1);
    // $child12->makeChildOf($root1);

    // $this->assertTrue(Menu::isValidNestedSet());

    // $root2    = Menu::create(array('caption' => 'TL2', 'site_id' => 2, 'language' => 'en'));
    // $child21  = Menu::create(array('caption' => 'C21', 'site_id' => 2, 'language' => 'en'));
    // $child22  = Menu::create(array('caption' => 'C22', 'site_id' => 2, 'language' => 'en'));
    // $child21->makeChildOf($root2);
    // $child22->makeChildOf($root2);

    // $this->assertTrue(Menu::isValidNestedSet());

    // $child11->update(array('site_id' => 2));
    // $child11->makeChildOf($root2);

    // $this->assertTrue(Menu::isValidNestedSet());

    // $expected = array($this->menus('C12'));
    // $this->assertEquals($expected, $root1->children()->get()->all());

    // $expected = array($this->menus('C21'), $this->menus('C22'), $this->menus('C11'));
    // $this->assertEquals($expected, $root2->children()->get()->all());
    }
}
