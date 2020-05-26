<?php

namespace Encima\Albero\Tests\Seeders\Category;

use Illuminate\Database\Seeder;
use Encima\Albero\Tests\Models\Category\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        create(Category::class, [
            'name' => 'Root 1',
            'left' => 1,
            'right' => 10,
            'depth' => 0,
            'parent_id' => null,
        ]);
        create(Category::class, [
            'name' => 'Child 1',
            'parent_id' => 1,
            'left' => 2,
            'right' => 3,
            'depth' => 1,
        ]);
        create(Category::class, [
            'name' => 'Child 2',
            'parent_id' => 1,
            'left' => 4,
            'right' => 7,
            'depth' => 1,
        ]);
        create(Category::class, [
            'name' => 'Child 2.1',
            'parent_id' => 3,
            'left' => 5,
            'right' => 6,
            'depth' => 2,
        ]);
        create(Category::class, [
            'name' => 'Child 3',
            'parent_id' => 1,
            'left' => 8,
            'right' => 9,
            'depth' => 1,
        ]);
        create(Category::class, [
            'name' => 'Root 2',
            'parent_id' => null,
            'left' => 11,
            'right' => 12,
            'depth' => 0,
        ]);
    }

    public function nestUptoAt($node, $levels = 10, $attrs = [])
    {
        for ($i = 0; $i < $levels; $i++, $node = $new) {
            $new = Category::create(array_merge($attrs, [
                'name' => "{$node->name}.1",
            ]));
            $new->makeChildOf($node);
        }
    }
}
