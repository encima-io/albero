<?php

namespace Encima\Albero\Tests\Unit\Category;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\MoveNotPossibleException;
use Encima\Albero\Tests\Models\Category\Category;
use Encima\Albero\Tests\Seeders\Category\CategorySeeder;

class CategoryMovementTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_left()
    {
        Category::firstWhere('name', 'Child 2')->moveLeft();

        $this->assertNull(Category::firstWhere('name', 'Child 2')->getLeftSibling());

        $this->assertEquals(Category::firstWhere('name', 'Child 1'), Category::firstWhere('name', 'Child 2')->getRightSibling());

        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_left_raises_an_exception_when_not_possible()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('Could not resolve target node. This node cannot move any further to the left.');
        $node = Category::firstWhere('name', 'Child 2');

        $node->moveLeft();
        $node->moveLeft();
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_left_does_not_change_depth()
    {
        Category::firstWhere('name', 'Child 2')->moveLeft();

        $this->assertEquals(1, Category::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(2, Category::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_left_with_subtree()
    {
        Category::firstWhere('name', 'Root 2')->moveLeft();

        $this->assertNull(Category::firstWhere('name', 'Root 2')->getLeftSibling());
        $this->assertEquals(Category::firstWhere('name', 'Root 1'), Category::firstWhere('name', 'Root 2')->getRightSibling());
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(0, Category::firstWhere('name', 'Root 1')->getDepth());
        $this->assertEquals(0, Category::firstWhere('name', 'Root 2')->getDepth());

        $this->assertEquals(1, Category::firstWhere('name', 'Child 1')->getDepth());
        $this->assertEquals(1, Category::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(1, Category::firstWhere('name', 'Child 3')->getDepth());

        $this->assertEquals(2, Category::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_left_of()
    {
        Category::firstWhere('name', 'Child 3')->moveToLeftOf(Category::firstWhere('name', 'Child 1'));

        $this->assertNull(Category::firstWhere('name', 'Child 3')->getLeftSibling());

        $this->assertEquals(Category::firstWhere('name', 'Child 1'), Category::firstWhere('name', 'Child 3')->getRightSibling());

        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_left_of_raises_an_exception_when_not_possible()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('Could not resolve target node. This node cannot move any further to the left.');
        Category::firstWhere('name', 'Child 1')->moveToLeftOf(Category::firstWhere('name', 'Child 1')->getLeftSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_left_of_does_not_change_depth()
    {
        Category::firstWhere('name', 'Child 2')->moveToLeftOf(Category::firstWhere('name', 'Child 1'));

        $this->assertEquals(1, Category::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(2, Category::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_left_of_with_subtree()
    {
        Category::firstWhere('name', 'Root 2')->moveToLeftOf(Category::firstWhere('name', 'Root 1'));

        $this->assertNull(Category::firstWhere('name', 'Root 2')->getLeftSibling());
        $this->assertEquals(Category::firstWhere('name', 'Root 1'), Category::firstWhere('name', 'Root 2')->getRightSibling());
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(0, Category::firstWhere('name', 'Root 1')->getDepth());
        $this->assertEquals(0, Category::firstWhere('name', 'Root 2')->getDepth());

        $this->assertEquals(1, Category::firstWhere('name', 'Child 1')->getDepth());
        $this->assertEquals(1, Category::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(1, Category::firstWhere('name', 'Child 3')->getDepth());

        $this->assertEquals(2, Category::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_right()
    {
        Category::firstWhere('name', 'Child 2')->moveRight();

        $this->assertNull(Category::firstWhere('name', 'Child 2')->getRightSibling());

        $this->assertEquals(Category::firstWhere('name', 'Child 3'), Category::firstWhere('name', 'Child 2')->getLeftSibling());

        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_right_raises_an_exception_when_not_possible()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('Could not resolve target node. This node cannot move any further to the right.');
        $node = Category::firstWhere('name', 'Child 2');

        $node->moveRight();
        $node->moveRight();
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_right_does_not_change_depth()
    {
        Category::firstWhere('name', 'Child 2')->moveRight();

        $this->assertEquals(1, Category::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(2, Category::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_right_with_subtree()
    {
        Category::firstWhere('name', 'Root 1')->moveRight();

        $this->assertNull(Category::firstWhere('name', 'Root 1')->getRightSibling());
        $this->assertEquals(Category::firstWhere('name', 'Root 2'), Category::firstWhere('name', 'Root 1')->getLeftSibling());
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(0, Category::firstWhere('name', 'Root 1')->getDepth());
        $this->assertEquals(0, Category::firstWhere('name', 'Root 2')->getDepth());

        $this->assertEquals(1, Category::firstWhere('name', 'Child 1')->getDepth());
        $this->assertEquals(1, Category::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(1, Category::firstWhere('name', 'Child 3')->getDepth());

        $this->assertEquals(2, Category::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_right_of()
    {
        Category::firstWhere('name', 'Child 1')->moveToRightOf(Category::firstWhere('name', 'Child 3'));

        $this->assertNull(Category::firstWhere('name', 'Child 1')->getRightSibling());

        $this->assertEquals(Category::firstWhere('name', 'Child 3'), Category::firstWhere('name', 'Child 1')->getLeftSibling());

        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_right_of_raises_an_exception_when_not_possible()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('Could not resolve target node. This node cannot move any further to the right.');
        Category::firstWhere('name', 'Child 3')->moveToRightOf(Category::firstWhere('name', 'Child 3')->getRightSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_right_of_does_not_change_depth()
    {
        Category::firstWhere('name', 'Child 2')->moveToRightOf(Category::firstWhere('name', 'Child 3'));

        $this->assertEquals(1, Category::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(2, Category::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_right_of_with_subtree()
    {
        Category::firstWhere('name', 'Root 1')->moveToRightOf(Category::firstWhere('name', 'Root 2'));

        $this->assertNull(Category::firstWhere('name', 'Root 1')->getRightSibling());
        $this->assertEquals(Category::firstWhere('name', 'Root 2'), Category::firstWhere('name', 'Root 1')->getLeftSibling());
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(0, Category::firstWhere('name', 'Root 1')->getDepth());
        $this->assertEquals(0, Category::firstWhere('name', 'Root 2')->getDepth());

        $this->assertEquals(1, Category::firstWhere('name', 'Child 1')->getDepth());
        $this->assertEquals(1, Category::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(1, Category::firstWhere('name', 'Child 3')->getDepth());

        $this->assertEquals(2, Category::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_root()
    {
        Category::firstWhere('name', 'Child 2')->makeRoot();

        $newRoot = Category::firstWhere('name', 'Child 2');

        $this->assertNull($newRoot->parent()->first());
        $this->assertEquals(0, $newRoot->getLevel());
        $this->assertEquals(9, $newRoot->getLeft());
        $this->assertEquals(12, $newRoot->getRight());

        $this->assertEquals(1, Category::firstWhere('name', 'Child 2.1')->getLevel());

        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nullify_parent_column_makes_it_root()
    {
        $node = Category::firstWhere('name', 'Child 2');

        $node->parent_id = null;

        $node->save();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(9, $node->getLeft());
        $this->assertEquals(12, $node->getRight());

        $this->assertEquals(1, Category::firstWhere('name', 'Child 2.1')->getLevel());

        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nullify_parent_column_on_new_nodes()
    {
        $node = new Category(['name' => 'Root 3']);

        $node->parent_id = null;

        $node->save();

        $node->reload();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(13, $node->getLeft());
        $this->assertEquals(14, $node->getRight());

        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_new_category_with_null_parent()
    {
        $node = new Category(['name' => 'Root 3']);
        $this->assertTrue($node->isRoot());

        $node->save();
        $this->assertTrue($node->isRoot());

        $node->makeRoot();
        $this->assertTrue($node->isRoot());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_child_of()
    {
        Category::firstWhere('name', 'Child 1')->makeChildOf(Category::firstWhere('name', 'Child 3'));

        $this->assertEquals(Category::firstWhere('name', 'Child 3'), Category::firstWhere('name', 'Child 1')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_child_of_appends_at_the_end()
    {
        $newChild = create(Category::class, [
            'name' => 'Child 4',
            'parent_id' => null,
        ]);

        $newChild->makeChildOf(Category::firstWhere('name', 'Root 1'));
        $lastChild = Category::firstWhere('name', 'Root 1')->children()->get()->last();

        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());
        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_child_of_moves_with_subtree()
    {
        Category::firstWhere('name', 'Child 2')->makeChildOf(Category::firstWhere('name', 'Child 1'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(Category::firstWhere('name', 'Child 1')->getKey(), Category::firstWhere('name', 'Child 2')->getParentId());

        $this->assertEquals(3, Category::firstWhere('name', 'Child 2')->getLeft());
        $this->assertEquals(6, Category::firstWhere('name', 'Child 2')->getRight());

        $this->assertEquals(2, Category::firstWhere('name', 'Child 1')->getLeft());
        $this->assertEquals(7, Category::firstWhere('name', 'Child 1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_child_of_swapping_roots()
    {
        $newRoot = create(Category::class, [
            'name' => 'Root 3',
            'parent_id' => null,
        ]);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        Category::firstWhere('name', 'Root 2')->makeChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Category::firstWhere('name', 'Root 2')->getParentId());

        $this->assertEquals(12, Category::firstWhere('name', 'Root 2')->getLeft());
        $this->assertEquals(13, Category::firstWhere('name', 'Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_child_of_swapping_roots_with_subtrees()
    {
        $newRoot = create(Category::class, [
            'name' => 'Root 3',
            'parent_id' => null,
        ]);

        Category::firstWhere('name', 'Root 1')->makeChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Category::firstWhere('name', 'Root 1')->getParentId());

        $this->assertEquals(4, Category::firstWhere('name', 'Root 1')->getLeft());
        $this->assertEquals(13, Category::firstWhere('name', 'Root 1')->getRight());

        $this->assertEquals(8, Category::firstWhere('name', 'Child 2.1')->getLeft());
        $this->assertEquals(9, Category::firstWhere('name', 'Child 2.1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_first_child_of()
    {
        Category::firstWhere('name', 'Child 1')->makeFirstChildOf(Category::firstWhere('name', 'Child 3'));

        $this->assertEquals(Category::firstWhere('name', 'Child 3'), Category::firstWhere('name', 'Child 1')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_first_child_of_appends_at_the_beginning()
    {
        $newChild = create(Category::class, [
            'name' => 'Child 4',
            'parent_id' => null,
        ]);

        $newChild->makeFirstChildOf(Category::firstWhere('name', 'Root 1'));
        $lastChild = Category::firstWhere('name', 'Root 1')->children()->get()->first();

        $this->assertEquals($newChild->getAttributes(), $lastChild->getAttributes());
        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_first_child_of_moves_with_subtree()
    {
        Category::firstWhere('name', 'Child 2')->makeFirstChildOf(Category::firstWhere('name', 'Child 1'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(Category::firstWhere('name', 'Child 1')->getKey(), Category::firstWhere('name', 'Child 2')->getParentId());

        $this->assertEquals(3, Category::firstWhere('name', 'Child 2')->getLeft());
        $this->assertEquals(6, Category::firstWhere('name', 'Child 2')->getRight());

        $this->assertEquals(2, Category::firstWhere('name', 'Child 1')->getLeft());
        $this->assertEquals(7, Category::firstWhere('name', 'Child 1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_first_child_of_swapping_roots()
    {
        $newRoot = create(Category::class, [
            'name' => 'Root 3',
            'parent_id' => null,
        ]);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        Category::firstWhere('name', 'Root 2')->makeFirstChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Category::firstWhere('name', 'Root 2')->getParentId());

        $this->assertEquals(12, Category::firstWhere('name', 'Root 2')->getLeft());
        $this->assertEquals(13, Category::firstWhere('name', 'Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_first_child_of_swapping_roots_with_subtrees()
    {
        $newRoot = create(Category::class, [
            'name' => 'Root 3',
            'parent_id' => null,
        ]);

        Category::firstWhere('name', 'Root 1')->makeFirstChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Category::firstWhere('name', 'Root 1')->getParentId());

        $this->assertEquals(4, Category::firstWhere('name', 'Root 1')->getLeft());
        $this->assertEquals(13, Category::firstWhere('name', 'Root 1')->getRight());

        $this->assertEquals(8, Category::firstWhere('name', 'Child 2.1')->getLeft());
        $this->assertEquals(9, Category::firstWhere('name', 'Child 2.1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_last_child_of()
    {
        Category::firstWhere('name', 'Child 1')->makeLastChildOf(Category::firstWhere('name', 'Child 3'));

        $this->assertEquals(Category::firstWhere('name', 'Child 3'), Category::firstWhere('name', 'Child 1')->parent()->first());

        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_last_child_of_appends_at_the_end()
    {
        $newChild = create(Category::class, [
            'name' => 'Child 4',
            'parent_id' => null,
        ]);

        $newChild->makeLastChildOf(Category::firstWhere('name', 'Root 1'));
        $lastChild = Category::firstWhere('name', 'Root 1')->children()->get()->last();

        $this->assertEquals($newChild->fresh(), $lastChild->fresh());
        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_last_child_of_moves_with_subtree()
    {
        Category::firstWhere('name', 'Child 2')->makeLastChildOf(Category::firstWhere('name', 'Child 1'));

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals(Category::firstWhere('name', 'Child 1')->getKey(), Category::firstWhere('name', 'Child 2')->getParentId());

        $this->assertEquals(3, Category::firstWhere('name', 'Child 2')->getLeft());
        $this->assertEquals(6, Category::firstWhere('name', 'Child 2')->getRight());

        $this->assertEquals(2, Category::firstWhere('name', 'Child 1')->getLeft());
        $this->assertEquals(7, Category::firstWhere('name', 'Child 1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_last_child_of_swapping_roots()
    {
        $newRoot = create(Category::class, [
            'name' => 'Root 3',
            'parent_id' => null,
        ]);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        Category::firstWhere('name', 'Root 2')->makeLastChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Category::firstWhere('name', 'Root 2')->getParentId());

        $this->assertEquals(12, Category::firstWhere('name', 'Root 2')->getLeft());
        $this->assertEquals(13, Category::firstWhere('name', 'Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_last_child_of_swapping_roots_with_subtrees()
    {
        $newRoot = create(Category::class, [
            'name' => 'Root 3',
            'parent_id' => null,
        ]);

        Category::firstWhere('name', 'Root 1')->makeLastChildOf($newRoot);

        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Category::firstWhere('name', 'Root 1')->getParentId());

        $this->assertEquals(4, Category::firstWhere('name', 'Root 1')->getLeft());
        $this->assertEquals(13, Category::firstWhere('name', 'Root 1')->getRight());

        $this->assertEquals(8, Category::firstWhere('name', 'Child 2.1')->getLeft());
        $this->assertEquals(9, Category::firstWhere('name', 'Child 2.1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_unpersisted_node_cannot_be_moved()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('A new node cannot be moved.');
        $unpersisted = new Category(['name' => 'Unpersisted']);

        $unpersisted->moveToRightOf(Category::firstWhere('name', 'Root 1'));
    }

    /**
     * @expectedException Baum\MoveNotPossibleException
     */

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_unpersisted_node_cannot_be_made_child()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('A new node cannot be moved.');
        $unpersisted = new Category(['name' => 'Unpersisted']);

        $unpersisted->makeChildOf(Category::firstWhere('name', 'Root 1'));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nodes_cannot_be_moved_to_itself()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('A node cannot be moved to itself.');
        $node = Category::firstWhere('name', 'Child 1');

        $node->moveToRightOf($node);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nodes_cannot_be_made_child_of_themselves()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('A node cannot be moved to itself.');
        $node = Category::firstWhere('name', 'Child 1');

        $node->makeChildOf($node);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nodes_cannot_be_moved_to_descendants_of_themselves()
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('A node cannot be moved to a descendant of itself (inside moved tree).');
        $node = Category::firstWhere('name', 'Root 1');
        $node->makeChildOf(Category::firstWhere('name', 'Child 2.1'));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_depth_is_updated_when_made_child()
    {
        $a = create(Category::class, [
            'name' => 'A',
            'parent_id' => null,
        ]);
        $b = create(Category::class, [
            'name' => 'B',
            'parent_id' => null,
        ]);
        $c = create(Category::class, [
            'name' => 'C',
            'parent_id' => null,
        ]);
        $d = create(Category::class, [
            'name' => 'D',
            'parent_id' => null,
        ]);

        // a > b > c > d
        $b->makeChildOf($a);
        $c->makeChildOf($b);
        $d->makeChildOf($c);

        $a->reload();
        $b->reload();
        $c->reload();
        $d->reload();

        $this->assertEquals(0, $a->getDepth());
        $this->assertEquals(1, $b->getDepth());
        $this->assertEquals(2, $c->getDepth());
        $this->assertEquals(3, $d->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_depth_is_updated_on_descendants_when_parent_moves()
    {
        $a = create(Category::class, [
            'name' => 'A',
            'parent_id' => null,
        ]);
        $b = create(Category::class, [
            'name' => 'B',
            'parent_id' => null,
        ]);
        $c = create(Category::class, [
            'name' => 'C',
            'parent_id' => null,
        ]);
        $d = create(Category::class, [
            'name' => 'D',
            'parent_id' => null,
        ]);

        // a > b > c > d
        $b->makeChildOf($a);
        $c->makeChildOf($b);
        $d->makeChildOf($c);

        $a->reload();
        $b->reload();
        $c->reload();
        $d->reload();

        $b->moveToRightOf($a);

        $a->reload();
        $b->reload();
        $c->reload();
        $d->reload();

        $this->assertEquals(0, $b->getDepth());
        $this->assertEquals(1, $c->getDepth());
        $this->assertEquals(2, $d->getDepth());
    }
}
