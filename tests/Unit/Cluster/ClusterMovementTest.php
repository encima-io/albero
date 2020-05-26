<?php

namespace Encima\Albero\Tests\Unit\Cluster;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\MoveNotPossibleException;
use Encima\Albero\Tests\Models\Cluster\Cluster;
use Encima\Albero\Tests\Seeders\Cluster\ClusterSeeder;

class ClusterMovementTest extends TestCase
{
    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_left(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 2')->moveLeft();

        $this->assertNull(Cluster::firstWhere('name', 'Child 2')->getLeftSibling());

        $this->assertEquals(Cluster::firstWhere('name', 'Child 1'), Cluster::firstWhere('name', 'Child 2')->getRightSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_left_raises_an_exception_when_not_possible(): void
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('Could not resolve target node. This node cannot move any further to the left.');
        $this->seed(ClusterSeeder::class);
        $node = Cluster::firstWhere('name', 'Child 2');

        $node->moveLeft();
        $node->moveLeft();
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_left_does_not_change_depth(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 2')->moveLeft();

        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_left_with_subtree(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Root 2')->moveLeft();

        $this->assertNull(Cluster::firstWhere('name', 'Root 2')->getLeftSibling());
        $this->assertEquals(Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Root 2')->getRightSibling());
        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(0, Cluster::firstWhere('name', 'Root 1')->getDepth());
        $this->assertEquals(0, Cluster::firstWhere('name', 'Root 2')->getDepth());

        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 1')->getDepth());
        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 3')->getDepth());

        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_left_of(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 3')->moveToLeftOf(Cluster::firstWhere('name', 'Child 1'));

        $this->assertNull(Cluster::firstWhere('name', 'Child 3')->getLeftSibling());

        $this->assertEquals(Cluster::firstWhere('name', 'Child 1'), Cluster::firstWhere('name', 'Child 3')->getRightSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_left_of_raises_an_exception_when_not_possible(): void
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('Could not resolve target node. This node cannot move any further to the left.');
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 1')->moveToLeftOf(Cluster::firstWhere('name', 'Child 1')->getLeftSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_left_of_does_not_change_depth(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 2')->moveToLeftOf(Cluster::firstWhere('name', 'Child 1'));

        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_left_of_with_subtree(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Root 2')->moveToLeftOf(Cluster::firstWhere('name', 'Root 1'));

        $this->assertNull(Cluster::firstWhere('name', 'Root 2')->getLeftSibling());
        $this->assertEquals(Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Root 2')->getRightSibling());
        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(0, Cluster::firstWhere('name', 'Root 1')->getDepth());
        $this->assertEquals(0, Cluster::firstWhere('name', 'Root 2')->getDepth());

        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 1')->getDepth());
        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 3')->getDepth());

        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_right(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 2')->moveRight();

        $this->assertNull(Cluster::firstWhere('name', 'Child 2')->getRightSibling());

        $this->assertEquals(Cluster::firstWhere('name', 'Child 3'), Cluster::firstWhere('name', 'Child 2')->getLeftSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_right_raises_an_exception_when_not_possible(): void
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('Could not resolve target node. This node cannot move any further to the right.');
        $this->seed(ClusterSeeder::class);
        $node = Cluster::firstWhere('name', 'Child 2');

        $node->moveRight();
        $node->moveRight();
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_right_does_not_change_depth(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 2')->moveRight();

        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_right_with_subtree(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Root 1')->moveRight();

        $this->assertNull(Cluster::firstWhere('name', 'Root 1')->getRightSibling());
        $this->assertEquals(Cluster::firstWhere('name', 'Root 2'), Cluster::firstWhere('name', 'Root 1')->getLeftSibling());
        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(0, Cluster::firstWhere('name', 'Root 1')->getDepth());
        $this->assertEquals(0, Cluster::firstWhere('name', 'Root 2')->getDepth());

        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 1')->getDepth());
        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 3')->getDepth());

        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_right_of(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 1')->moveToRightOf(Cluster::firstWhere('name', 'Child 3'));

        $this->assertNull(Cluster::firstWhere('name', 'Child 1')->getRightSibling());

        $this->assertEquals(Cluster::firstWhere('name', 'Child 3'), Cluster::firstWhere('name', 'Child 1')->getLeftSibling());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_right_of_raises_an_exception_when_not_possible(): void
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('Could not resolve target node. This node cannot move any further to the right.');
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 3')->moveToRightOf(Cluster::firstWhere('name', 'Child 3')->getRightSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_right_of_does_not_change_depth(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 2')->moveToRightOf(Cluster::firstWhere('name', 'Child 3'));

        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_move_to_right_of_with_subtree(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Root 1')->moveToRightOf(Cluster::firstWhere('name', 'Root 2'));

        $this->assertNull(Cluster::firstWhere('name', 'Root 1')->getRightSibling());
        $this->assertEquals(Cluster::firstWhere('name', 'Root 2'), Cluster::firstWhere('name', 'Root 1')->getLeftSibling());
        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(0, Cluster::firstWhere('name', 'Root 1')->getDepth());
        $this->assertEquals(0, Cluster::firstWhere('name', 'Root 2')->getDepth());

        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 1')->getDepth());
        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 2')->getDepth());
        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 3')->getDepth());

        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 2.1')->getDepth());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_root(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 2')->makeRoot();

        $newRoot = Cluster::firstWhere('name', 'Child 2');

        $this->assertNull($newRoot->parent()->first());
        $this->assertEquals(0, $newRoot->getLevel());
        $this->assertEquals(9, $newRoot->getLeft());
        $this->assertEquals(12, $newRoot->getRight());

        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 2.1')->getLevel());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nullify_parent_column_makes_it_root(): void
    {
        $this->seed(ClusterSeeder::class);
        $node = Cluster::firstWhere('name', 'Child 2');

        $node->parent_id = null;

        $node->save();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(9, $node->getLeft());
        $this->assertEquals(12, $node->getRight());

        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 2.1')->getLevel());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nullify_parent_column_on_new_nodes(): void
    {
        $this->seed(ClusterSeeder::class);
        $node = new Cluster(['name' => 'Root 3']);

        $node->parent_id = null;

        $node->save();

        $node->reload();

        $this->assertNull($node->parent()->first());
        $this->assertEquals(0, $node->getLevel());
        $this->assertEquals(13, $node->getLeft());
        $this->assertEquals(14, $node->getRight());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_new_cluster_with_null_parent(): void
    {
        $this->seed(ClusterSeeder::class);
        $node = new Cluster(['name' => 'Root 3']);
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
    public function it_make_child_of(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 1')->makeChildOf(Cluster::firstWhere('name', 'Child 3'));

        $this->assertEquals(Cluster::firstWhere('name', 'Child 3'), Cluster::firstWhere('name', 'Child 1')->parent()->first());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_child_of_appends_at_the_end(): void
    {
        $this->seed(ClusterSeeder::class);
        $newChild = Cluster::create(['name' => 'Child 4']);

        $newChild->makeChildOf(Cluster::firstWhere('name', 'Root 1'));

        $lastChild = Cluster::firstWhere('name', 'Root 1')->children()->get()->last();
        $this->assertEquals(
            $newChild->getAttributes(),
            $lastChild->getAttributes()
        );

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_child_of_moves_with_subtree(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 2')->makeChildOf(Cluster::firstWhere('name', 'Child 1'));

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(Cluster::firstWhere('name', 'Child 1')->getKey(), Cluster::firstWhere('name', 'Child 2')->getParentId());

        $this->assertEquals(3, Cluster::firstWhere('name', 'Child 2')->getLeft());
        $this->assertEquals(6, Cluster::firstWhere('name', 'Child 2')->getRight());

        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 1')->getLeft());
        $this->assertEquals(7, Cluster::firstWhere('name', 'Child 1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_child_of_swapping_roots(): void
    {
        $this->seed(ClusterSeeder::class);
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        Cluster::firstWhere('name', 'Root 2')->makeChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::firstWhere('name', 'Root 2')->getParentId());

        $this->assertEquals(12, Cluster::firstWhere('name', 'Root 2')->getLeft());
        $this->assertEquals(13, Cluster::firstWhere('name', 'Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_child_of_swapping_roots_with_subtrees(): void
    {
        $this->seed(ClusterSeeder::class);
        $newRoot = Cluster::create(['name' => 'Root 3']);

        Cluster::firstWhere('name', 'Root 1')->makeChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::firstWhere('name', 'Root 1')->getParentId());

        $this->assertEquals(4, Cluster::firstWhere('name', 'Root 1')->getLeft());
        $this->assertEquals(13, Cluster::firstWhere('name', 'Root 1')->getRight());

        $this->assertEquals(8, Cluster::firstWhere('name', 'Child 2.1')->getLeft());
        $this->assertEquals(9, Cluster::firstWhere('name', 'Child 2.1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_first_child_of(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 1')->makeFirstChildOf(Cluster::firstWhere('name', 'Child 3'));

        $this->assertEquals(Cluster::firstWhere('name', 'Child 3'), Cluster::firstWhere('name', 'Child 1')->parent()->first());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_first_child_of_appends_at_the_beginning(): void
    {
        $this->seed(ClusterSeeder::class);
        $newChild = Cluster::create(['name' => 'Child 4']);

        $newChild->makeFirstChildOf(Cluster::firstWhere('name', 'Root 1'));

        $lastChild = Cluster::firstWhere('name', 'Root 1')->children()->get()->first();
        $this->assertEquals(
            $newChild->getAttributes(),
            $lastChild->getAttributes()
        );

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_first_child_of_moves_with_subtree(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 2')->makeFirstChildOf(Cluster::firstWhere('name', 'Child 1'));

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(Cluster::firstWhere('name', 'Child 1')->getKey(), Cluster::firstWhere('name', 'Child 2')->getParentId());

        $this->assertEquals(3, Cluster::firstWhere('name', 'Child 2')->getLeft());
        $this->assertEquals(6, Cluster::firstWhere('name', 'Child 2')->getRight());

        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 1')->getLeft());
        $this->assertEquals(7, Cluster::firstWhere('name', 'Child 1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_first_child_of_swapping_roots(): void
    {
        $this->seed(ClusterSeeder::class);
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        Cluster::firstWhere('name', 'Root 2')->makeFirstChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::firstWhere('name', 'Root 2')->getParentId());

        $this->assertEquals(12, Cluster::firstWhere('name', 'Root 2')->getLeft());
        $this->assertEquals(13, Cluster::firstWhere('name', 'Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_first_child_of_swapping_roots_with_subtrees(): void
    {
        $this->seed(ClusterSeeder::class);
        $newRoot = Cluster::create(['name' => 'Root 3']);

        Cluster::firstWhere('name', 'Root 1')->makeFirstChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::firstWhere('name', 'Root 1')->getParentId());

        $this->assertEquals(4, Cluster::firstWhere('name', 'Root 1')->getLeft());
        $this->assertEquals(13, Cluster::firstWhere('name', 'Root 1')->getRight());

        $this->assertEquals(8, Cluster::firstWhere('name', 'Child 2.1')->getLeft());
        $this->assertEquals(9, Cluster::firstWhere('name', 'Child 2.1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_last_child_of(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 1')->makeLastChildOf(Cluster::firstWhere('name', 'Child 3'));

        $this->assertEquals(Cluster::firstWhere('name', 'Child 3'), Cluster::firstWhere('name', 'Child 1')->parent()->first());

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_last_child_of_appends_at_the_end(): void
    {
        $this->seed(ClusterSeeder::class);
        $newChild = Cluster::create(['name' => 'Child 4']);

        $newChild->makeLastChildOf(Cluster::firstWhere('name', 'Root 1'));

        $lastChild = Cluster::firstWhere('name', 'Root 1')->children()->get()->last();
        $this->assertEquals(
            $newChild->getAttributes(),
            $lastChild->getAttributes()
        );

        $this->assertTrue(Cluster::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_last_child_of_moves_with_subtree(): void
    {
        $this->seed(ClusterSeeder::class);
        Cluster::firstWhere('name', 'Child 2')->makeLastChildOf(Cluster::firstWhere('name', 'Child 1'));

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals(Cluster::firstWhere('name', 'Child 1')->getKey(), Cluster::firstWhere('name', 'Child 2')->getParentId());

        $this->assertEquals(3, Cluster::firstWhere('name', 'Child 2')->getLeft());
        $this->assertEquals(6, Cluster::firstWhere('name', 'Child 2')->getRight());

        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 1')->getLeft());
        $this->assertEquals(7, Cluster::firstWhere('name', 'Child 1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_last_child_of_swapping_roots(): void
    {
        $this->seed(ClusterSeeder::class);
        $newRoot = Cluster::create(['name' => 'Root 3']);

        $this->assertEquals(13, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());

        Cluster::firstWhere('name', 'Root 2')->makeLastChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::firstWhere('name', 'Root 2')->getParentId());

        $this->assertEquals(12, Cluster::firstWhere('name', 'Root 2')->getLeft());
        $this->assertEquals(13, Cluster::firstWhere('name', 'Root 2')->getRight());

        $this->assertEquals(11, $newRoot->getLeft());
        $this->assertEquals(14, $newRoot->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_make_last_child_of_swapping_roots_with_subtrees(): void
    {
        $this->seed(ClusterSeeder::class);
        $newRoot = Cluster::create(['name' => 'Root 3']);

        Cluster::firstWhere('name', 'Root 1')->makeLastChildOf($newRoot);

        $this->assertTrue(Cluster::isValidNestedSet());

        $this->assertEquals($newRoot->getKey(), Cluster::firstWhere('name', 'Root 1')->getParentId());

        $this->assertEquals(4, Cluster::firstWhere('name', 'Root 1')->getLeft());
        $this->assertEquals(13, Cluster::firstWhere('name', 'Root 1')->getRight());

        $this->assertEquals(8, Cluster::firstWhere('name', 'Child 2.1')->getLeft());
        $this->assertEquals(9, Cluster::firstWhere('name', 'Child 2.1')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_unpersisted_node_cannot_be_moved(): void
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('A new node cannot be moved');
        $this->seed(ClusterSeeder::class);
        $unpersisted = new Cluster(['name' => 'Unpersisted']);

        $unpersisted->moveToRightOf(Cluster::firstWhere('name', 'Root 1'));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_unpersisted_node_cannot_be_made_child(): void
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('A new node cannot be moved.');
        $this->seed(ClusterSeeder::class);
        $unpersisted = new Cluster(['name' => 'Unpersisted']);

        $unpersisted->makeChildOf(Cluster::firstWhere('name', 'Root 1'));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nodes_cannot_be_moved_to_itself(): void
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('A node cannot be moved to itself.');
        $this->seed(ClusterSeeder::class);
        $node = Cluster::firstWhere('name', 'Child 1');

        $node->moveToRightOf($node);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nodes_cannot_be_made_child_of_themselves(): void
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('A node cannot be moved to itself.');
        $this->seed(ClusterSeeder::class);
        $node = Cluster::firstWhere('name', 'Child 1');

        $node->makeChildOf($node);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_nodes_cannot_be_moved_to_descendants_of_themselves(): void
    {
        $this->expectException(MoveNotPossibleException::class);
        $this->expectExceptionMessage('A node cannot be moved to a descendant of itself (inside moved tree).');
        $this->seed(ClusterSeeder::class);
        $node = Cluster::firstWhere('name', 'Root 1');

        $node->makeChildOf(Cluster::firstWhere('name', 'Child 2.1'));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_depth_is_updated_when_made_child(): void
    {
        $this->seed(ClusterSeeder::class);
        $a = Cluster::create(['name' => 'A']);
        $b = Cluster::create(['name' => 'B']);
        $c = Cluster::create(['name' => 'C']);
        $d = Cluster::create(['name' => 'D']);

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
    public function it_depth_is_updated_on_descendants_when_parent_moves(): void
    {
        $this->seed(ClusterSeeder::class);
        $a = Cluster::create(['name' => 'A']);
        $b = Cluster::create(['name' => 'B']);
        $c = Cluster::create(['name' => 'C']);
        $d = Cluster::create(['name' => 'D']);

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
