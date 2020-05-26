<?php

namespace Encima\Albero\Tests\Seeders\Category;

use Illuminate\Database\Seeder;
use Encima\Albero\Tests\Models\Category\MultiScopedCategory;

class MultiScopedCategorySeeder extends Seeder
{
    public function run()
    {
        create(MultiScopedCategory::class, [
            'company_id' => 1,
            'language' => 'en',
            'name' => 'Root 1',
            'left' => 1,
            'right' => 10,
            'depth' => 0,
            'parent_id' => null,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 1,
            'language' => 'en',
            'name' => 'Child 1',
            'left' => 2,
            'right' => 3,
            'depth' => 1,
            'parent_id' => 1,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 1,
            'language' => 'en',
            'name' => 'Child 2',
            'left' => 4,
            'right' => 7,
            'depth' => 1,
            'parent_id' => 1,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 1,
            'language' => 'en',
            'name' => 'Child 2.1',
            'left' => 5,
            'right' => 6,
            'depth' => 2,
            'parent_id' => 3,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 1,
            'language' => 'en',
            'name' => 'Child 3',
            'left' => 8,
            'right' => 9,
            'depth' => 1,
            'parent_id' => 1,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 2,
            'language' => 'en',
            'name' => 'Root 2',
            'left' => 1,
            'right' => 10,
            'depth' => 0,
            'parent_id' => null,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 2,
            'language' => 'en',
            'name' => 'Child 4',
            'left' => 2,
            'right' => 3,
            'depth' => 1,
            'parent_id' => 6,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 2,
            'language' => 'en',
            'name' => 'Child 5',
            'left' => 4,
            'right' => 7,
            'depth' => 1,
            'parent_id' => 6,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 2,
            'language' => 'en',
            'name' => 'Child 5.1',
             'left' => 5,
             'right' => 6,
             'depth' => 2,
             'parent_id' => 8,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 2,
            'language' => 'en',
            'name' => 'Child 6',
            'left' => 8,
            'right' => 9,
            'depth' => 1,
            'parent_id' => 6,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 3,
            'language' => 'fr',
            'name' => 'Racine 1',
            'left' => 1,
            'right' => 10,
            'depth' => 0,
            'parent_id' => null,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 3,
            'language' => 'fr',
            'name' => 'Enfant 1',
            'left' => 2,
            'right' => 3,
            'depth' => 1,
            'parent_id' => 11,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 3,
            'language' => 'fr',
            'name' => 'Enfant 2',
            'left' => 4,
            'right' => 7,
            'depth' => 1,
            'parent_id' => 11,
        ]);

        create(MultiScopedCategory::class, [
            'company_id' => 3,
            'language' => 'fr',
            'name' => 'Enfant 2.1',
            'left' => 5,
            'right' => 6,
            'depth' => 2,
            'parent_id' => 13,
         ]);
        create(MultiScopedCategory::class, [
            'company_id' => 3,
            'language' => 'fr',
            'name' => 'Enfant 3',
            'left' => 8,
            'right' => 9,
            'depth' => 1,
            'parent_id' => 11,
        ]);
        create(MultiScopedCategory::class, [
            'company_id' => 3,
            'language' => 'es',
            'name' => 'Raiz 1',
            'left' => 1,
            'right' => 10,
            'depth' => 0,
            'parent_id' => null,
          ]);
        create(MultiScopedCategory::class, [
            'company_id' => 3,
            'language' => 'es',
            'name' => 'Hijo 1',
            'left' => 2,
            'right' => 3,
            'depth' => 1,
            'parent_id' => 16,
         ]);
        create(MultiScopedCategory::class, [
            'company_id' => 3,
            'language' => 'es',
            'name' => 'Hijo 2',
            'left' => 4,
            'right' => 7,
            'depth' => 1,
            'parent_id' => 16,
         ]);
        create(MultiScopedCategory::class, [
            'company_id' => 3,
            'language' => 'es',
            'name' => 'Hijo 2.1',
            'left' => 5,
            'right' => 6,
            'depth' => 2,
            'parent_id' => 18,
          ]);
        create(MultiScopedCategory::class, [
            'company_id' => 3,
            'language' => 'es',
            'name' => 'Hijo 3',
            'left' => 8,
            'right' => 9,
            'depth' => 1,
            'parent_id' => 16,
         ]);
    }
}
