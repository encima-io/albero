<?php

namespace Encima\Albero\Tests\Models\Category;

class MultiScopedCategory extends Category
{
    protected array $scoped = ['company_id', 'language'];
}
