<?php

namespace Encima\Albero\Tests\Models\Cluster;

class MultiScopedCluster extends Cluster
{
    protected $scoped = ['company_id', 'language'];
}
