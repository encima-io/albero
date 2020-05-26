<?php

namespace Encima\Albero\Tests\Models\Category;

use Encima\Albero\HasNestedSets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoftCategory extends Model
{
    use SoftDeletes, HasNestedSets;

    protected $table = 'categories';
    public $timestamps = false;
    public $guarded = [
        'parent_id',
        'left',
        'right',
        'depth',
    ];
    protected $casts = [
        'parent_id' => 'integer',
        'left' => 'integer',
        'right' => 'integer',
        'depth' => 'integer',
    ];
}
