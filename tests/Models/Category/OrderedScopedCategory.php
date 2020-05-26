<?php

namespace Encima\Albero\Tests\Models\Category;

class OrderedScopedCategory extends Category
{
    protected array $scoped = ['company_id'];

    protected $orderColumn = 'name';
}
