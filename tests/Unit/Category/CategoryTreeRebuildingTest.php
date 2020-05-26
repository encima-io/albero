<?php

namespace Encima\Albero\Tests\Unit\Category;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\Tests\Models\Category\Category;
use Encima\Albero\Tests\Seeders\Category\CategorySeeder;
use Encima\Albero\Tests\Models\Category\MultiScopedCategory;
use Encima\Albero\Tests\Seeders\Category\MultiScopedCategorySeeder;

class CategoryTreeRebuildingTest extends TestCase
{
    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_rebuild()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        $root = Category::root();
        Category::query()->update(['left' => null, 'right' => null]);
        // $this->assertFalse(Category::isValidNestedSet());

        Category::rebuild();
        $this->assertTrue(Category::isValidNestedSet());

        $this->assertEquals($root, Category::root());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_rebuild_preseves_root_nodes()
    {
        $this->seed(CategorySeeder::class);
        $root1 = create(Category::class, ['name' => 'Test Root 1', 'parent_id' => null]);
        $root2 = create(Category::class, ['name' => 'Test Root 2', 'parent_id' => null]);
        $root3 = create(Category::class, ['name' => 'Test Root 3', 'parent_id' => null]);

        $root2->makeChildOf($root1);
        $root3->makeChildOf($root1);

        $lastRoot = Category::roots()->reOrderBy($root1->getLeftColumnName(), 'desc')->first();

        Category::query()->update(['left' => null, 'right' => null]);
        Category::rebuild();

        $this->assertEquals($lastRoot, Category::roots()->reOrderBy($root1->getLeftColumnName(), 'desc')->first());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_rebuild_recomputes_depth()
    {
        $this->seed(CategorySeeder::class);
        $this->assertTrue(Category::isValidNestedSet());

        Category::query()->update(['left' => null, 'right' => null, 'depth' => 0]);
        $this->assertFalse(Category::isValidNestedSet());

        Category::rebuild();

        $expected = [0, 1, 1, 2, 1, 0];
        $this->assertEquals($expected, Category::all()->map(function ($n) {
            return $n->getDepth();
        })->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_rebuild_with_scope()
    {
        $this->seed(MultiScopedCategorySeeder::class);
        MultiScopedCategory::query()->delete();

        $root = create(MultiScopedCategory::class, [
            'name' => 'A',
            'parent_id' => null,
            'company_id' => 721,
            'language' => 'es',
        ]);
        $child1 = create(MultiScopedCategory::class, [
            'name' => 'A.1',
            'parent_id' => null,
            'company_id' => 721,
            'language' => 'es',
        ]);
        $child2 = create(MultiscopedCategory::class, [
            'name' => 'A.2',
            'parent_id' => null,
            'company_id' => 721,
            'language' => 'es',
        ]);

        $child1->makeChildOf($root);
        $child2->makeChildOf($root);

        MultiscopedCategory::query()->update(['left' => null, 'right' => null]);
        $this->assertFalse(MultiscopedCategory::isValidNestedSet());

        MultiscopedCategory::rebuild();
        $this->assertTrue(MultiscopedCategory::isValidNestedSet());

        $this->assertEquals(
            $root->getAttributes(),
            $root->getAttributes()
        );

        $expected = [$child1->fresh(), $child2->fresh()];
        $this->assertEquals(
            $expected,
            $root->children()->get()->all()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_rebuild_with_multiple_scopes()
    {
        $this->seed(MultiScopedCategorySeeder::class);
        MultiScopedCategory::query()->delete();

        $root1 = create(MultiScopedCategory::class, [
            'name' => 'TL1',
            'parent_id' => null,
            'company_id' => 1,
            'language' => 'en',
        ]);
        $child11 = create(MultiScopedCategory::class, [
            'name' => 'C11',
            'parent_id' => null,
            'company_id' => 1,
            'language' => 'en',
        ]);
        $child12 = create(MultiScopedCategory::class, [
            'name' => 'C12',
            'parent_id' => null,
            'company_id' => 1,
            'language' => 'en',
        ]);
        $child11->makeChildOf($root1);
        $child12->makeChildOf($root1);

        $root2 = create(MultiScopedCategory::class, [
            'name' => 'TL2',
            'parent_id' => null,
            'company_id' => 2,
            'language' => 'en',
        ]);
        $child21 = create(MultiScopedCategory::class, [
            'name' => 'C21',
            'parent_id' => null,
            'company_id' => 2,
            'language' => 'en',
        ]);
        $child22 = create(MultiScopedCategory::class, [
            'name' => 'C22',
            'parent_id' => null,
            'company_id' => 2,
            'language' => 'en',
        ]);
        $child21->makeChildOf($root2);
        $child22->makeChildOf($root2);

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());

        $tree = MultiScopedCategory::query()->orderBy($root1->getKeyName())->get()->all();

        MultiScopedCategory::query()->update(['left' => null, 'right' => null]);
        MultiScopedCategory::rebuild();

        $this->assertTrue(MultiScopedCategory::isValidNestedSet());
        $this->assertEquals($tree, MultiScopedCategory::query()->orderBy($root1->getKeyName())->get()->all());
    }
}
