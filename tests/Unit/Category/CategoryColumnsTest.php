<?php

namespace Encima\Albero\Tests\Unit\Category;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\Tests\Models\Category\Category;
use Encima\Albero\Tests\Models\Category\ScopedCategory;
use Encima\Albero\Tests\Models\Category\OrderedCategory;
use Encima\Albero\Tests\Seeders\Category\CategorySeeder;
use Encima\Albero\Tests\Models\Category\MultiScopedCategory;

class CategoryColumnsTest extends TestCase
{
    // protected function categories($name, $className = 'Category')
    // {
    //     return forward_static_call_array([$className, 'where'], ['name', '=', $name])->first();
    // }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_parent_column_name(): void
    {
        $category = make(Category::class);
        $this->assertSame(
            'parent_id',
            $category->getParentColumnName(),
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_qualified_parent_column_name()
    {
        $category = make(Category::class);

        $this->assertSame(
            'categories.parent_id',
            $category->getQualifiedParentColumnName()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_parent_id()
    {
        $rootCategory = make(Category::class, [
            'parent_id' => null,
        ]);
        $childCategory = make(Category::class, [
            'parent_id' => $rootCategory->id,
        ]);
        $this->assertNull($rootCategory->getParentId());

        $this->assertSame($rootCategory->id, $childCategory->getParentId());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_left_column_name()
    {
        $category = make(Category::class);

        $this->assertSame('left', $category->getLeftColumnName());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_qualified_left_column_name()
    {
        $category = make(Category::class);

        $this->assertSame('categories.left', $category->getQualifiedLeftColumnName());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_left()
    {
        $this->seed(CategorySeeder::class);
        $category = Category::firstWhere('name', 'Root 1');

        $this->assertSame(1, $category->getLeft());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_right_column_name()
    {
        $category = make(Category::class);

        $this->assertSame('right', $category->getRightColumnName());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_qualified_right_column_name()
    {
        $category = make(Category::class);

        $this->assertSame('categories.right', $category->getQualifiedRightColumnName());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_right()
    {
        $this->seed(CategorySeeder::class);
        $category = Category::firstWhere('name', 'Root 1');

        $this->assertSame(10, $category->getRight());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_order_colum_name()
    {
        $category = make(Category::class);

        $this->assertSame(
            $category->getOrderColumnName(),
            $category->getLeftColumnName()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_qualified_order_column_name()
    {
        $category = make(Category::class);

        $this->assertSame(
            $category->getQualifiedOrderColumnName(),
            $category->getQualifiedLeftColumnName()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_order()
    {
        $this->seed(CategorySeeder::class);
        $category = Category::firstWhere('name', 'Root 1');

        $this->assertSame(
            $category->getOrder(),
            $category->getLeft()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_order_column_name_non_default()
    {
        $category = make(OrderedCategory::class);

        $this->assertSame('name', $category->getOrderColumnName());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_qualified_order_column_name_non_default()
    {
        $category = make(OrderedCategory::class);

        $this->assertSame('categories.name', $category->getQualifiedOrderColumnName());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_order_non_default()
    {
        $category = make(OrderedCategory::class, [
            'name' => 'Root 1',
        ]);
        $this->assertSame('Root 1', $category->getOrder());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_scoped_columns()
    {
        $category = make(Category::class);

        $this->assertSame($category->getScopedColumns(), []);

        $category = make(ScopedCategory::class);
        $this->assertSame(['company_id'], $category->getScopedColumns());

        $category = make(MultiScopedCategory::class);
        $this->assertSame(['company_id', 'language'], $category->getScopedColumns());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_gets_qualified_scoped_columns()
    {
        $category = make(Category::class);

        $this->assertSame([], $category->getQualifiedScopedColumns());

        $category = make(ScopedCategory::class);
        $this->assertSame(
            ['categories.company_id'],
            $category->getQualifiedScopedColumns()
        );

        $category = make(MultiScopedCategory::class);

        $this->assertSame(
            ['categories.company_id', 'categories.language'],
            $category->getQualifiedScopedColumns()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_scoped()
    {
        $category = make(Category::class);

        $this->assertFalse($category->isScoped());

        $category = make(ScopedCategory::class);
        $this->assertTrue($category->isScoped());

        $category = make(MultiScopedCategory::class);
        $this->assertTrue($category->isScoped());

        $category = make(OrderedCategory::class);
        $this->assertFalse($category->isScoped());
    }
}
