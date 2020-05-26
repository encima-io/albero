<?php

namespace Encima\Albero\Tests\Models\Cluster;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftCluster extends Cluster
{
    use SoftDeletes;

    public $timestamps = true;
}
