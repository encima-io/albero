<?php

namespace Encima\Albero\Tests\Seeders\Category;

use Illuminate\Database\Seeder;
use Encima\Albero\Tests\Models\Category\OrderedCategory;

class OrderedCategorySeeder extends Seeder
{
    public function run()
    {
        create(OrderedCategory::class, [
            'parent_id' => null,
            'name' => 'Root Z',
            'left' => 1,
            'right' => 10,
            'depth' => 0,
        ]);
        create(OrderedCategory::class, [
            'parent_id' => 1,
            'name' => 'Child C',
            'left' => 2,
            'right' => 3,
            'depth' => 1,
        ]);
        create(OrderedCategory::class, [
            'parent_id' => 1,
            'name' => 'Child G',
            'left' => 4,
            'right' => 7,
            'depth' => 1,
        ]);
        create(OrderedCategory::class, [
            'parent_id' => 3,
            'name' => 'Child G.1',
            'left' => 5,
            'right' => 6,
            'depth' => 2,
        ]);
        create(OrderedCategory::class, [
            'parent_id' => 1,
            'name' => 'Child A',
            'left' => 8,
            'right' => 9,
            'depth' => 1,
        ]);
        create(OrderedCategory::class, [
            'parent_id' => null,
            'name' => 'Root A',
            'left' => 11,
            'right' => 12,
            'depth' => 0,
        ]);
    }
}
