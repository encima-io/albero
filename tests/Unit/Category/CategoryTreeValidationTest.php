<?php

namespace Encima\Albero\Tests\Unit\Category;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\Tests\Models\Category\Category;
use Encima\Albero\Tests\Seeders\Category\CategorySeeder;

class CategoryTreeValidationTest extends TestCase
{
    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_tree_is_not_valid_with_null_lefts()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->update(['left' => null]);
        $this->assertFalse(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_tree_is_not_valid_with_null_rights()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->update(['right' => null]);
        $this->assertFalse(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_tree_is_not_valid_when_rights_equal_lefts()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        $child2 = Category::firstWhere('name', 'Child 2');
        $child2->right = $child2->left;
        $child2->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_tree_is_not_valid_when_left_equals_parent()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        $child2 = Category::firstWhere('name', 'Child 2');
        $child2->left = Category::firstWhere('name', 'Root 1')->getLeft();
        $child2->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_tree_is_not_valid_when_right_equals_parent()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        $child2 = Category::firstWhere('name', 'Child 2');
        $child2->right = Category::firstWhere('name', 'Root 1')->getRight();
        $child2->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_tree_is_valid_with_missing_middle_node()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->delete(Category::firstWhere('name', 'Child 2')->getKey());
        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_tree_is_not_valid_with_overlapping_roots()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        // Force Root 2 to overlap with Root 1
        $root = Category::firstWhere('name', 'Root 2');
        $root->left = 0;
        $root->save();

        $this->assertFalse(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_node_deletion_does_not_make_tree_invalid()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        Category::firstWhere('name', 'Root 2')->delete();
        $this->assertTrue(Category::isValidNestedSet());

        Category::firstWhere('name', 'Child 1')->delete();
        $this->assertTrue(Category::isValidNestedSet());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_node_deletion_with_subtree_does_not_make_tree_invalid()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        Category::firstWhere('name', 'Child 2')->delete();
        $this->assertTrue(Category::isValidNestedSet());
    }
}
