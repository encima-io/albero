<?php

namespace Encima\Albero\Tests\Unit\Cluster;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\Tests\Models\Cluster\Cluster;
use Encima\Albero\Tests\Seeders\Cluster\ClusterSeeder;

class ClusterColumnsTest extends TestCase
{
    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_key_is_non_numeric()
    {
        $this->seed(ClusterSeeder::class);
        $root = Cluster::root();

        $this->assertTrue(is_string($root->getKey()));
        $this->assertFalse(is_numeric($root->getKey()));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_parent_key_is_non_numeric()
    {
        $this->seed(ClusterSeeder::class);
        $child1 = Cluster::firstWhere('name', 'Child 1');

        $this->assertTrue(is_string($child1->getParentId()));
        $this->assertFalse(is_numeric($child1->getParentId()));
    }
}
