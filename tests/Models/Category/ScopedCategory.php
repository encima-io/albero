<?php

namespace Encima\Albero\Tests\Models\Category;

class ScopedCategory extends Category
{
    protected array $scoped = ['company_id'];
}
