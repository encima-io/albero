<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Encima\Albero\Tests\Models\Category\Category;
use Encima\Albero\Tests\Models\Category\ScopedCategory;
use Encima\Albero\Tests\Models\Category\OrderedCategory;
use Encima\Albero\Tests\Models\Category\MultiScopedCategory;
use Encima\Albero\Tests\Models\Category\OrderedScopedCategory;

$factory->define(Category::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'parent_id' => function () use ($faker) {
            if ($faker->boolean(80)) {
                return create(Category::class, ['parent_id' => null]);
            }
        },
        'language' => $faker->languageCode,
        'company_id' => $faker->randomDigitNotNull,
    ];
});

$factory->define(MultiScopedCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'parent_id' => function () use ($faker) {
            if ($faker->boolean(80)) {
                return create(MultiScopedCategory::class, ['parent_id' => null]);
            }
        },
        'language' => $faker->languageCode,
        'company_id' => $faker->randomDigitNotNull,
    ];
});

$factory->define(OrderedCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'parent_id' => function () use ($faker) {
            if ($faker->boolean(80)) {
                return create(OrderedCategory::class, ['parent_id' => null]);
            }
        },
        'language' => $faker->languageCode,
        'company_id' => $faker->randomDigitNotNull,
    ];
});

$factory->define(OrderedScopedCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'parent_id' => function () use ($faker) {
            if ($faker->boolean(80)) {
                return create(OrderedScopedCategory::class, ['parent_id' => null]);
            }
        },
        'language' => $faker->languageCode,
        'company_id' => $faker->randomDigitNotNull,
    ];
});

$factory->define(ScopedCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'parent_id' => function () use ($faker) {
            if ($faker->boolean(80)) {
                return create(ScopedCategory::class, ['parent_id' => null]);
            }
        },
        'language' => $faker->languageCode,
        'company_id' => $faker->randomDigitNotNull,
    ];
});

$factory->define(SoftCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'parent_id' => function () use ($faker) {
            if ($faker->boolean(80)) {
                return create(SoftCategory::class, ['parent_id' => null]);
            }
        },
        'language' => $faker->languageCode,
        'company_id' => $faker->randomDigitNotNull,
    ];
});
