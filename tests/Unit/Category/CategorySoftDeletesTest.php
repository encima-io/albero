<?php

namespace Encima\Albero\Tests\Unit\Category;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\Tests\Models\Category\SoftCategory;
use Encima\Albero\Tests\Seeders\Category\CategorySeeder;

class CategorySoftDeletesTest extends TestCase
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
    public function it_reload()
    {
        $node = SoftCategory::firstWhere('name', 'Child 3');

        $node->delete();
        $this->assertTrue($node->trashed());
        $this->assertTrue($node->exists);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_delete_maintains_tree_valid()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child3 = SoftCategory::firstWhere('name', 'Child 3');
        $child3->delete();

        $this->assertTrue($child3->trashed());
        $this->assertTrue(SoftCategory::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_delete_maintains_tree_valid_with_subtrees()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child2 = SoftCategory::firstWhere('name', 'Child 2');
        $child2->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            SoftCategory::firstWhere('name', 'Child 1'),
            SoftCategory::firstWhere('name', 'Child 3'),
        ];
        $this->assertEquals(
            $expected,
            SoftCategory::firstWhere('name', 'Root 1')->getDescendants()->all()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_delete_shifts_indexes()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::firstWhere('name', 'Child 1')->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            SoftCategory::firstWhere('name', 'Child 2'),
            SoftCategory::firstWhere('name', 'Child 2.1'),
            SoftCategory::firstWhere('name', 'Child 3'),
        ];

        $this->assertEquals(
            $expected,
            SoftCategory::firstWhere('name', 'Root 1')->getDescendants()->all()
        );

        $this->assertEquals(1, SoftCategory::firstWhere('name', 'Root 1')->getLeft());
        $this->assertEquals(8, SoftCategory::firstWhere('name', 'Root 1')->getRight());

        $this->assertEquals(2, SoftCategory::firstWhere('name', 'Child 2')->getLeft());
        $this->assertEquals(5, SoftCategory::firstWhere('name', 'Child 2')->getRight());

        $this->assertEquals(3, SoftCategory::firstWhere('name', 'Child 2.1')->getLeft());
        $this->assertEquals(4, SoftCategory::firstWhere('name', 'Child 2.1')->getRight());

        $this->assertEquals(6, SoftCategory::firstWhere('name', 'Child 3')->getLeft());
        $this->assertEquals(7, SoftCategory::firstWhere('name', 'Child 3')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_delete_shifts_indexes_subtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::firstWhere('name', 'Child 2')->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            SoftCategory::firstWhere('name', 'Child 1'),
            SoftCategory::firstWhere('name', 'Child 3'),
        ];
        $this->assertEquals(
            $expected,
            SoftCategory::firstWhere('name', 'Root 1')->getDescendants()->all()
        );

        $this->assertEquals(1, SoftCategory::firstWhere('name', 'Root 1')->getLeft());
        $this->assertEquals(6, SoftCategory::firstWhere('name', 'Root 1')->getRight());

        $this->assertEquals(2, SoftCategory::firstWhere('name', 'Child 1')->getLeft());
        $this->assertEquals(3, SoftCategory::firstWhere('name', 'Child 1')->getRight());

        $this->assertEquals(4, SoftCategory::firstWhere('name', 'Child 3')->getLeft());
        $this->assertEquals(5, SoftCategory::firstWhere('name', 'Child 3')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_delete_shifts_indexes_full_subtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::firstWhere('name', 'Root 1')->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->assertEmpty(SoftCategory::firstWhere('name', 'Root 2')->getSiblings()->all());
        $this->assertEquals(1, SoftCategory::firstWhere('name', 'Root 2')->getLeft());
        $this->assertEquals(2, SoftCategory::firstWhere('name', 'Root 2')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_restore_maintains_tree_valid()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child = SoftCategory::firstWhere('name', 'Child 3');
        $child->delete();

        $this->assertTrue($child->trashed());
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child->restore();
        $this->assertFalse($child->trashed());
        $this->assertTrue(SoftCategory::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_restore_maintains_tree_valid_with_subtrees()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child2 = SoftCategory::firstWhere('name', 'Child 2');
        $child2->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child2->reload();
        $child2->restore();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            SoftCategory::firstWhere('name', 'Child 1'),
            SoftCategory::firstWhere('name', 'Child 2'),
            SoftCategory::firstWhere('name', 'Child 2.1'),
            SoftCategory::firstWhere('name', 'Child 3'),
        ];
        $this->assertEquals(
            $expected,
            SoftCategory::firstWhere('name', 'Root 1')->getDescendants()->all()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_restore_unshifts_indexes()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child = SoftCategory::firstWhere('name', 'Child 1');
        $child->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child->restore();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            SoftCategory::firstWhere('name', 'Child 1'),
            SoftCategory::firstWhere('name', 'Child 2'),
            SoftCategory::firstWhere('name', 'Child 2.1'),
            SoftCategory::firstWhere('name', 'Child 3'),
        ];
        $this->assertEquals(
            $expected,
            SoftCategory::firstWhere('name', 'Root 1')->getDescendants()->all()
        );

        $this->assertEquals(1, SoftCategory::firstWhere('name', 'Root 1')->getLeft());
        $this->assertEquals(10, SoftCategory::firstWhere('name', 'Root 1')->getRight());

        $this->assertEquals(2, $child->getLeft());
        $this->assertEquals(3, $child->getRight());

        $this->assertEquals(4, SoftCategory::firstWhere('name', 'Child 2')->getLeft());
        $this->assertEquals(7, SoftCategory::firstWhere('name', 'Child 2')->getRight());
        $this->assertEquals(5, SoftCategory::firstWhere('name', 'Child 2.1')->getLeft());
        $this->assertEquals(6, SoftCategory::firstWhere('name', 'Child 2.1')->getRight());

        $this->assertEquals(8, SoftCategory::firstWhere('name', 'Child 3')->getLeft());
        $this->assertEquals(9, SoftCategory::firstWhere('name', 'Child 3')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_restore_unshifts_indexes_subtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::firstWhere('name', 'Child 2')->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::withTrashed()->where('name', 'Child 2')->first()->restore();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            SoftCategory::firstWhere('name', 'Child 1'),
            SoftCategory::firstWhere('name', 'Child 2'),
            SoftCategory::firstWhere('name', 'Child 2.1'),
            SoftCategory::firstWhere('name', 'Child 3'),
        ];

        $this->assertEquals(
            $expected,
            SoftCategory::firstWhere('name', 'Root 1')->getDescendants()->all()
        );

        $this->assertEquals(1, SoftCategory::firstWhere('name', 'Root 1')->getLeft());
        $this->assertEquals(10, SoftCategory::firstWhere('name', 'Root 1')->getRight());

        $this->assertEquals(2, SoftCategory::firstWhere('name', 'Child 1')->getLeft());
        $this->assertEquals(3, SoftCategory::firstWhere('name', 'Child 1')->getRight());

        $this->assertEquals(4, SoftCategory::firstWhere('name', 'Child 2')->getLeft());
        $this->assertEquals(7, SoftCategory::firstWhere('name', 'Child 2')->getRight());
        $this->assertEquals(5, SoftCategory::firstWhere('name', 'Child 2.1')->getLeft());
        $this->assertEquals(6, SoftCategory::firstWhere('name', 'Child 2.1')->getRight());

        $this->assertEquals(8, SoftCategory::firstWhere('name', 'Child 3')->getLeft());
        $this->assertEquals(9, SoftCategory::firstWhere('name', 'Child 3')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_restore_unshifts_indexes_full_subtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $root = SoftCategory::firstWhere('name', 'Root 1');
        $root->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $root->restore();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = [
            SoftCategory::firstWhere('name', 'Child 1'),
            SoftCategory::firstWhere('name', 'Child 2'),
            SoftCategory::firstWhere('name', 'Child 2.1'),
            SoftCategory::firstWhere('name', 'Child 3'),
        ];

        $this->assertEquals($expected, $root->getDescendants()->all());

        $this->assertEquals(1, $root->getLeft());
        $this->assertEquals(10, $root->getRight());

        $this->assertEquals(2, SoftCategory::firstWhere('name', 'Child 1')->getLeft());
        $this->assertEquals(3, SoftCategory::firstWhere('name', 'Child 1')->getRight());

        $this->assertEquals(4, SoftCategory::firstWhere('name', 'Child 2')->getLeft());
        $this->assertEquals(7, SoftCategory::firstWhere('name', 'Child 2')->getRight());
        $this->assertEquals(5, SoftCategory::firstWhere('name', 'Child 2.1')->getLeft());
        $this->assertEquals(6, SoftCategory::firstWhere('name', 'Child 2.1')->getRight());

        $this->assertEquals(8, SoftCategory::firstWhere('name', 'Child 3')->getLeft());
        $this->assertEquals(9, SoftCategory::firstWhere('name', 'Child 3')->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_all_static()
    {
        $expected = ['Root 1', 'Child 1', 'Child 2', 'Child 2.1', 'Child 3', 'Root 2'];

        $this->assertEquals($expected, SoftCategory::all()->pluck('name')->toArray());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_all_static_with_soft_deletes()
    {
        SoftCategory::firstWhere('name', 'Child 1')->delete();
        SoftCategory::firstWhere('name', 'Child 3')->delete();

        $expected = ['Root 1', 'Child 2', 'Child 2.1', 'Root 2'];
        $this->assertEquals($expected, SoftCategory::all()->pluck('name')->toArray());
    }
}
