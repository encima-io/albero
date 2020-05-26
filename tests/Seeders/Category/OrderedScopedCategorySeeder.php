<?php

namespace Encima\Albero\Tests\Seeders\Category;

use Illuminate\Database\Seeder;
use Encima\Albero\Tests\Models\Category\OrderedScopedCategory;

class OrderedScopedCategorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        OrderedScopedCategory::create([
            'parent_id' => null,
            'company_id' => 1,
            'name' => 'Root 1',
            'left' => 1,
            'right' => 10,
            'depth' => 0,
        ]);
        OrderedScopedCategory::create([
            'parent_id' => 1,
            'company_id' => 1,
            'name' => 'Child 3',
            'left' => 8,
            'right' => 9,
            'depth' => 1,
        ]);
        OrderedScopedCategory::create([
            'parent_id' => 1,
            'company_id' => 1,
            'name' => 'Child 2',
            'left' => 4,
            'right' => 7,
            'depth' => 1,
        ]);
        OrderedScopedCategory::create([
            'parent_id' => 3,
            'company_id' => 1,
            'name' => 'Child 2.1',
            'left' => 5,
            'right' => 6,
            'depth' => 2,
        ]);
        OrderedScopedCategory::create([
            'parent_id' => 1,
            'company_id' => 1,
            'name' => 'Child 1',
            'left' => 2,
            'right' => 3,
            'depth' => 1,
        ]);
        OrderedScopedCategory::create([
            'parent_id' => null,
            'company_id' => 2,
            'name' => 'Root 2',
            'left' => 1,
            'right' => 10,
            'depth' => 0,
        ]);
        OrderedScopedCategory::create([
            'parent_id' => 6,
            'company_id' => 2,
            'name' => 'Child 4',
            'left' => 2,
            'right' => 3,
            'depth' => 1,
        ]);
        OrderedScopedCategory::create([
            'parent_id' => 6,
            'company_id' => 2,
            'name' => 'Child 5',
            'left' => 4,
            'right' => 7,
            'depth' => 1,
        ]);
        OrderedScopedCategory::create([
            'parent_id' => 8,
            'company_id' => 2,
            'name' => 'Child 5.1',
            'left' => 5,
            'right' => 6,
            'depth' => 2,
        ]);
        OrderedScopedCategory::create([
            'parent_id' => 6,
            'company_id' => 2,
            'name' => 'Child 6',
            'left' => 8,
            'right' => 9,
            'depth' => 1,
        ]);
    }
}
