<?php

namespace Encima\Albero\Tests\Models\Category;

use Encima\Albero\HasNestedSets;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasNestedSets;

    /** @var string */
    protected $table = 'categories';

    /** @var bool */
    public $timestamps = false;

    /** @var array */
    public $guarded = [
        'parent_id',
        'left',
        'right',
        'depth',
    ];

    /** @var array */
    protected $casts = [
        'parent_id' => 'integer',
        'left' => 'integer',
        'right' => 'integer',
        'depth' => 'integer',
    ];
}
