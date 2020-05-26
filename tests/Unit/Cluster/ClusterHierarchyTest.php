<?php

namespace Encima\Albero\Tests\Unit\Cluster;

use Encima\Albero\Tests\TestCase;
use Encima\Albero\Tests\Models\Cluster\Cluster;
use Encima\Albero\Tests\Models\Cluster\OrderedCluster;
use Encima\Albero\Tests\Seeders\Cluster\ClusterSeeder;
use Encima\Albero\Tests\Seeders\Cluster\OrderedClusterSeeder;

class ClusterHierarchyTest extends TestCase
{
    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_all_static()
    {
        $this->seed(ClusterSeeder::class);
        $results = Cluster::all();
        $expected = Cluster::query()->orderBy('left')->get();

        $this->assertEquals($results, $expected);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_all_static_with_custom_order()
    {
        $this->seed(OrderedClusterSeeder::class);
        $results = OrderedCluster::all();
        $expected = OrderedCluster::query()->orderBy('name')->get();

        $this->assertEquals($results, $expected);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_roots_static()
    {
        $this->seed(ClusterSeeder::class);
        $query = Cluster::whereNull('parent_id')->get();

        $roots = Cluster::roots()->get();

        $this->assertEquals($query->count(), $roots->count());
        $this->assertCount(2, $roots);

        foreach ($query->pluck('id') as $node) {
            $this->assertContains($node, $roots->pluck('id'));
        }
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_roots_static_with_custom_order()
    {
        $this->seed(OrderedClusterSeeder::class);
        $cluster = OrderedCluster::create(['name' => 'A new root is born']);
        $cluster->syncOriginal(); // ¿? --> This should be done already !?

        $roots = OrderedCluster::roots()->get();

        $this->assertCount(3, $roots);
        $this->assertEquals(
            $cluster->getAttributes(),
            $roots->first()->getAttributes()
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_root_static()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertEquals(Cluster::root(), Cluster::firstWhere('name', 'Root 1'));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_all_leaves_static()
    {
        $this->seed(ClusterSeeder::class);
        $allLeaves = Cluster::allLeaves()->get();

        $this->assertCount(4, $allLeaves);

        $leaves = $allLeaves->pluck('name');

        $this->assertContains('Child 1', $leaves);
        $this->assertContains('Child 2.1', $leaves);
        $this->assertContains('Child 3', $leaves);
        $this->assertContains('Root 2', $leaves);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_all_trunks_static()
    {
        $this->seed(ClusterSeeder::class);
        $allTrunks = Cluster::allTrunks()->get();

        $this->assertCount(1, $allTrunks);

        $trunks = $allTrunks->pluck('name');
        $this->assertContains('Child 2', $trunks);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_root()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertEquals(Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Root 1')->getRoot());
        $this->assertEquals(Cluster::firstWhere('name', 'Root 2'), Cluster::firstWhere('name', 'Root 2')->getRoot());

        $this->assertEquals(Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Child 1')->getRoot());
        $this->assertEquals(Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Child 2')->getRoot());
        $this->assertEquals(Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Child 2.1')->getRoot());
        $this->assertEquals(Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Child 3')->getRoot());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_root_equals_self_if_unpersisted()
    {
        $cluster = new Cluster();

        $this->assertEquals($cluster->getRoot(), $cluster);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_root_equals_value_if_set_if_unpersisted()
    {
        $this->seed(ClusterSeeder::class);
        $parent = Cluster::roots()->first();

        $child = new Cluster();
        $child->setAttribute($child->getParentColumnName(), $parent->getKey());

        $this->assertEquals($child->getRoot(), $parent);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_root()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertTrue(Cluster::firstWhere('name', 'Root 1')->isRoot());
        $this->assertTrue(Cluster::firstWhere('name', 'Root 2')->isRoot());

        $this->assertFalse(Cluster::firstWhere('name', 'Child 1')->isRoot());
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2')->isRoot());
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2.1')->isRoot());
        $this->assertFalse(Cluster::firstWhere('name', 'Child 3')->isRoot());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_leaves()
    {
        $this->seed(ClusterSeeder::class);
        $leaves = [Cluster::firstWhere('name', 'Child 1'), Cluster::firstWhere('name', 'Child 2.1'), Cluster::firstWhere('name', 'Child 3')];

        $this->assertEquals($leaves, Cluster::firstWhere('name', 'Root 1')->getLeaves()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_leaves_in_iteration()
    {
        $this->seed(ClusterSeeder::class);
        $node = Cluster::firstWhere('name', 'Root 1');

        $expectedIds = [
            '5d7ce1fd-6151-46d3-a5b3-0ebb9988dc57',
            '3315a297-af87-4ad3-9fa5-19785407573d',
            '054476d2-6830-4014-a181-4de010ef7114',
        ];

        foreach ($node->getLeaves() as $i => $leaf) {
            $this->assertEquals($expectedIds[$i], $leaf->getKey());
        }
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_trunks()
    {
        $this->seed(ClusterSeeder::class);
        $trunks = [Cluster::firstWhere('name', 'Child 2')];

        $this->assertEquals($trunks, Cluster::firstWhere('name', 'Root 1')->getTrunks()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_trunks_in_iteration()
    {
        $this->seed(ClusterSeeder::class);
        $node = Cluster::firstWhere('name', 'Root 1');

        $expectedIds = ['07c1fc8c-53b5-4fe7-b9c4-e09f266a455c'];

        foreach ($node->getTrunks() as $i => $trunk) {
            $this->assertEquals($expectedIds[$i], $trunk->getKey());
        }
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_leaf()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertTrue(Cluster::firstWhere('name', 'Child 1')->isLeaf());
        $this->assertTrue(Cluster::firstWhere('name', 'Child 2.1')->isLeaf());
        $this->assertTrue(Cluster::firstWhere('name', 'Child 3')->isLeaf());
        $this->assertTrue(Cluster::firstWhere('name', 'Root 2')->isLeaf());

        $this->assertFalse(Cluster::firstWhere('name', 'Root 1')->isLeaf());
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2')->isLeaf());

        $new = new Cluster();
        $this->assertFalse($new->isLeaf());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_trunk()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertFalse(Cluster::firstWhere('name', 'Child 1')->isTrunk());
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2.1')->isTrunk());
        $this->assertFalse(Cluster::firstWhere('name', 'Child 3')->isTrunk());
        $this->assertFalse(Cluster::firstWhere('name', 'Root 2')->isTrunk());

        $this->assertFalse(Cluster::firstWhere('name', 'Root 1')->isTrunk());
        $this->assertTrue(Cluster::firstWhere('name', 'Child 2')->isTrunk());

        $new = new Cluster();
        $this->assertFalse($new->isTrunk());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_without_node_scope()
    {
        $this->seed(ClusterSeeder::class);
        $child = Cluster::firstWhere('name', 'Child 2.1');

        $expected = [Cluster::firstWhere('name', 'Root 1'), $child];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutNode(Cluster::firstWhere('name', 'Child 2'))->get()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_without_self_scope()
    {
        $this->seed(ClusterSeeder::class);
        $child = Cluster::firstWhere('name', 'Child 2.1');

        $expected = [Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Child 2')];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutSelf()->get()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_without_root_scope()
    {
        $this->seed(ClusterSeeder::class);
        $child = Cluster::firstWhere('name', 'Child 2.1');

        $expected = [Cluster::firstWhere('name', 'Child 2'), $child];

        $this->assertEquals($expected, $child->ancestorsAndSelf()->withoutRoot()->get()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_limit_depth_scope()
    {
        $this->seed(ClusterSeeder::class);
        (new ClusterSeeder())->nestUptoAt(Cluster::firstWhere('name', 'Child 2.1'), 10);

        $node = Cluster::firstWhere('name', 'Child 2');

        $descendancy = $node->descendants()->pluck('id');

        $this->assertEmpty($node->descendants()->limitDepth(0)->pluck('id'));
        $this->assertEquals($node, $node->descendantsAndSelf()->limitDepth(0)->first());

        $this->assertEquals(
            array_slice($descendancy->toArray(), 0, 3),
            $node->descendants()->limitDepth(3)->pluck('id')->toarray()
        );
        $this->assertEquals(
            array_slice($descendancy->toArray(), 0, 5),
            $node->descendants()->limitDepth(5)->pluck('id')->toarray()
        );
        $this->assertEquals(
            array_slice($descendancy->toArray(), 0, 7),
            $node->descendants()->limitDepth(7)->pluck('id')->toarray()
        );
        $this->assertEquals(
            $descendancy,
            $node->descendants()->limitDepth(1000)->pluck('id')
        );
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_ancestors_and_self()
    {
        $this->seed(ClusterSeeder::class);
        $child = Cluster::firstWhere('name', 'Child 2.1');

        $expected = [Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Child 2'), $child];

        $this->assertEquals($expected, $child->getAncestorsAndSelf()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_ancestors_and_self_without_root()
    {
        $this->seed(ClusterSeeder::class);
        $child = Cluster::firstWhere('name', 'Child 2.1');

        $expected = [Cluster::firstWhere('name', 'Child 2'), $child];

        $this->assertEquals($expected, $child->getAncestorsAndSelfWithoutRoot()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_ancestors()
    {
        $this->seed(ClusterSeeder::class);
        $child = Cluster::firstWhere('name', 'Child 2.1');

        $expected = [Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Child 2')];

        $this->assertEquals($expected, $child->getAncestors()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_ancestors_without_root()
    {
        $this->seed(ClusterSeeder::class);
        $child = Cluster::firstWhere('name', 'Child 2.1');

        $expected = [Cluster::firstWhere('name', 'Child 2')];

        $this->assertEquals($expected, $child->getAncestorsWithoutRoot()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_descendants_and_self()
    {
        $this->seed(ClusterSeeder::class);
        $parent = Cluster::firstWhere('name', 'Root 1');

        $expected = [
            $parent,
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ];

        $this->assertCount(count($expected), $parent->getDescendantsAndSelf());

        $this->assertEquals($expected, $parent->getDescendantsAndSelf()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_descendants_and_self_with_limit()
    {
        $this->seed(ClusterSeeder::class);
        (new ClusterSeeder())->nestUptoAt(Cluster::firstWhere('name', 'Child 2.1'), 3);

        $parent = Cluster::firstWhere('name', 'Root 1');

        $this->assertEquals([$parent], $parent->getDescendantsAndSelf(0)->all());

        $this->assertEquals([
            $parent,
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendantsAndSelf(1)->all());

        $this->assertEquals([
            $parent,
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendantsAndSelf(2)->all());

        $this->assertEquals([
            $parent,
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 2.1.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendantsAndSelf(3)->all());

        $this->assertEquals([
            $parent,
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 2.1.1'),
            Cluster::firstWhere('name', 'Child 2.1.1.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendantsAndSelf(4)->all());

        $this->assertEquals([
            $parent,
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 2.1.1'),
            Cluster::firstWhere('name', 'Child 2.1.1.1'),
            Cluster::firstWhere('name', 'Child 2.1.1.1.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendantsAndSelf(10)->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_descendants()
    {
        $this->seed(ClusterSeeder::class);
        $parent = Cluster::firstWhere('name', 'Root 1');

        $expected = [
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ];

        $this->assertCount(count($expected), $parent->getDescendants());

        $this->assertEquals($expected, $parent->getDescendants()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_descendants_with_limit()
    {
        $this->seed(ClusterSeeder::class);
        (new ClusterSeeder())->nestUptoAt(Cluster::firstWhere('name', 'Child 2.1'), 3);

        $parent = Cluster::firstWhere('name', 'Root 1');

        $this->assertEmpty($parent->getDescendants(0)->all());

        $this->assertEquals([
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendants(1)->all());

        $this->assertEquals([
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendants(2)->all());

        $this->assertEquals([
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 2.1.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendants(3)->all());

        $this->assertEquals([
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 2.1.1'),
            Cluster::firstWhere('name', 'Child 2.1.1.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendants(4)->all());

        $this->assertEquals([
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 2.1.1'),
            Cluster::firstWhere('name', 'Child 2.1.1.1'),
            Cluster::firstWhere('name', 'Child 2.1.1.1.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendants(5)->all());

        $this->assertEquals([
            Cluster::firstWhere('name', 'Child 1'),
            Cluster::firstWhere('name', 'Child 2'),
            Cluster::firstWhere('name', 'Child 2.1'),
            Cluster::firstWhere('name', 'Child 2.1.1'),
            Cluster::firstWhere('name', 'Child 2.1.1.1'),
            Cluster::firstWhere('name', 'Child 2.1.1.1.1'),
            Cluster::firstWhere('name', 'Child 3'),
        ], $parent->getDescendants(10)->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_descendants_recurses_children()
    {
        $this->seed(ClusterSeeder::class);
        $a = Cluster::create(['name' => 'A']);
        $b = Cluster::create(['name' => 'B']);
        $c = Cluster::create(['name' => 'C']);

        // a > b > c
        $b->makeChildOf($a);
        $c->makeChildOf($b);

        $a->reload();
        $b->reload();
        $c->reload();

        $this->assertEquals(1, $a->children()->count());
        $this->assertEquals(1, $b->children()->count());
        $this->assertEquals(2, $a->descendants()->count());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_immediate_descendants()
    {
        $this->seed(ClusterSeeder::class);
        $expected = [Cluster::firstWhere('name', 'Child 1'), Cluster::firstWhere('name', 'Child 2'), Cluster::firstWhere('name', 'Child 3')];

        $this->assertEquals($expected, Cluster::firstWhere('name', 'Root 1')->getImmediateDescendants()->all());

        $this->assertEquals([Cluster::firstWhere('name', 'Child 2.1')], Cluster::firstWhere('name', 'Child 2')->getImmediateDescendants()->all());

        $this->assertEmpty(Cluster::firstWhere('name', 'Root 2')->getImmediateDescendants()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_self_or_ancestor_of()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertTrue(Cluster::firstWhere('name', 'Root 1')->isSelfOrAncestorOf(Cluster::firstWhere('name', 'Child 1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Root 1')->isSelfOrAncestorOf(Cluster::firstWhere('name', 'Child 2.1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 2')->isSelfOrAncestorOf(Cluster::firstWhere('name', 'Child 2.1')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2.1')->isSelfOrAncestorOf(Cluster::firstWhere('name', 'Child 2')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 1')->isSelfOrAncestorOf(Cluster::firstWhere('name', 'Child 2')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 1')->isSelfOrAncestorOf(Cluster::firstWhere('name', 'Child 1')));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_ancestor_of()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertTrue(Cluster::firstWhere('name', 'Root 1')->isAncestorOf(Cluster::firstWhere('name', 'Child 1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Root 1')->isAncestorOf(Cluster::firstWhere('name', 'Child 2.1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 2')->isAncestorOf(Cluster::firstWhere('name', 'Child 2.1')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2.1')->isAncestorOf(Cluster::firstWhere('name', 'Child 2')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 1')->isAncestorOf(Cluster::firstWhere('name', 'Child 2')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 1')->isAncestorOf(Cluster::firstWhere('name', 'Child 1')));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_self_or_descendant_of()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertTrue(Cluster::firstWhere('name', 'Child 1')->isSelfOrDescendantOf(Cluster::firstWhere('name', 'Root 1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 2.1')->isSelfOrDescendantOf(Cluster::firstWhere('name', 'Root 1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 2.1')->isSelfOrDescendantOf(Cluster::firstWhere('name', 'Child 2')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2')->isSelfOrDescendantOf(Cluster::firstWhere('name', 'Child 2.1')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2')->isSelfOrDescendantOf(Cluster::firstWhere('name', 'Child 1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 1')->isSelfOrDescendantOf(Cluster::firstWhere('name', 'Child 1')));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_is_descendant_of()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertTrue(Cluster::firstWhere('name', 'Child 1')->isDescendantOf(Cluster::firstWhere('name', 'Root 1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 2.1')->isDescendantOf(Cluster::firstWhere('name', 'Root 1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 2.1')->isDescendantOf(Cluster::firstWhere('name', 'Child 2')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2')->isDescendantOf(Cluster::firstWhere('name', 'Child 2.1')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2')->isDescendantOf(Cluster::firstWhere('name', 'Child 1')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 1')->isDescendantOf(Cluster::firstWhere('name', 'Child 1')));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_siblings_and_self()
    {
        $this->seed(ClusterSeeder::class);
        $child = Cluster::firstWhere('name', 'Child 2');

        $expected = [Cluster::firstWhere('name', 'Child 1'), $child, Cluster::firstWhere('name', 'Child 3')];
        $this->assertEquals($expected, $child->getSiblingsAndSelf()->all());

        $expected = [Cluster::firstWhere('name', 'Root 1'), Cluster::firstWhere('name', 'Root 2')];
        $this->assertEquals($expected, Cluster::firstWhere('name', 'Root 1')->getSiblingsAndSelf()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_siblings()
    {
        $this->seed(ClusterSeeder::class);
        $child = Cluster::firstWhere('name', 'Child 2');

        $expected = [Cluster::firstWhere('name', 'Child 1'), Cluster::firstWhere('name', 'Child 3')];

        $this->assertEquals($expected, $child->getSiblings()->all());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_left_sibling()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertEquals(Cluster::firstWhere('name', 'Child 1'), Cluster::firstWhere('name', 'Child 2')->getLeftSibling());
        $this->assertEquals(Cluster::firstWhere('name', 'Child 2'), Cluster::firstWhere('name', 'Child 3')->getLeftSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_left_sibling_of_first_root_is_null()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertNull(Cluster::firstWhere('name', 'Root 1')->getLeftSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_left_sibling_with_none_is_null()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertNull(Cluster::firstWhere('name', 'Child 2.1')->getLeftSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_left_sibling_of_leftmost_node_is_null()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertNull(Cluster::firstWhere('name', 'Child 1')->getLeftSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_right_sibling()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertEquals(Cluster::firstWhere('name', 'Child 3'), Cluster::firstWhere('name', 'Child 2')->getRightSibling());
        $this->assertEquals(Cluster::firstWhere('name', 'Child 2'), Cluster::firstWhere('name', 'Child 1')->getRightSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_right_sibling_of_roots()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertEquals(Cluster::firstWhere('name', 'Root 2'), Cluster::firstWhere('name', 'Root 1')->getRightSibling());
        $this->assertNull(Cluster::firstWhere('name', 'Root 2')->getRightSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_right_sibling_with_none_is_null()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertNull(Cluster::firstWhere('name', 'Child 2.1')->getRightSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_right_sibling_of_rightmost_node_is_null()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertNull(Cluster::firstWhere('name', 'Child 3')->getRightSibling());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_inside_subtree()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertFalse(Cluster::firstWhere('name', 'Child 1')->insideSubtree(Cluster::firstWhere('name', 'Root 2')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2')->insideSubtree(Cluster::firstWhere('name', 'Root 2')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 3')->insideSubtree(Cluster::firstWhere('name', 'Root 2')));

        $this->assertTrue(Cluster::firstWhere('name', 'Child 1')->insideSubtree(Cluster::firstWhere('name', 'Root 1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 2')->insideSubtree(Cluster::firstWhere('name', 'Root 1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 2.1')->insideSubtree(Cluster::firstWhere('name', 'Root 1')));
        $this->assertTrue(Cluster::firstWhere('name', 'Child 3')->insideSubtree(Cluster::firstWhere('name', 'Root 1')));

        $this->assertTrue(Cluster::firstWhere('name', 'Child 2.1')->insideSubtree(Cluster::firstWhere('name', 'Child 2')));
        $this->assertFalse(Cluster::firstWhere('name', 'Child 2.1')->insideSubtree(Cluster::firstWhere('name', 'Root 2')));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_level()
    {
        $this->seed(ClusterSeeder::class);
        $this->assertEquals(0, Cluster::firstWhere('name', 'Root 1')->getLevel());
        $this->assertEquals(1, Cluster::firstWhere('name', 'Child 1')->getLevel());
        $this->assertEquals(2, Cluster::firstWhere('name', 'Child 2.1')->getLevel());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_returns_an_eloquent_collection()
    {
        $this->seed(ClusterSeeder::class);
        $categories = Cluster::all()->toHierarchy();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $categories);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_returns_hierarchical_data()
    {
        $this->seed(ClusterSeeder::class);
        $categories = Cluster::all()->toHierarchy();

        $this->assertEquals(2, $categories->count());

        $first = $categories->first();
        $this->assertEquals('Root 1', $first->name);
        $this->assertEquals(3, $first->children->count());

        $first_lvl2 = $first->children->first();
        $this->assertEquals('Child 1', $first_lvl2->name);
        $this->assertEquals(0, $first_lvl2->children->count());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_nests_correctly()
    {
        $this->seed(ClusterSeeder::class);
        // Prune all categories
        Cluster::query()->delete();

        // Build a sample tree structure:
        //
        //   - A
        //     |- A.1
        //     |- A.2
        //   - B
        //     |- B.1
        //     |- B.2
        //         |- B.2.1
        //         |- B.2.2
        //           |- B.2.2.1
        //         |- B.2.3
        //     |- B.3
        //   - C
        //     |- C.1
        //     |- C.2
        //   - D
        //
        $a = Cluster::create(['name' => 'A']);
        $b = Cluster::create(['name' => 'B']);
        $c = Cluster::create(['name' => 'C']);
        $d = Cluster::create(['name' => 'D']);

        $ch = Cluster::create(['name' => 'A.1']);
        $ch->makeChildOf($a);

        $ch = Cluster::create(['name' => 'A.2']);
        $ch->makeChildOf($a);

        $ch = Cluster::create(['name' => 'B.1']);
        $ch->makeChildOf($b);

        $ch = Cluster::create(['name' => 'B.2']);
        $ch->makeChildOf($b);

        $ch2 = Cluster::create(['name' => 'B.2.1']);
        $ch2->makeChildOf($ch);

        $ch2 = Cluster::create(['name' => 'B.2.2']);
        $ch2->makeChildOf($ch);

        $ch3 = Cluster::create(['name' => 'B.2.2.1']);
        $ch3->makeChildOf($ch2);

        $ch2 = Cluster::create(['name' => 'B.2.3']);
        $ch2->makeChildOf($ch);

        $ch = Cluster::create(['name' => 'B.3']);
        $ch->makeChildOf($b);

        $ch = Cluster::create(['name' => 'C.1']);
        $ch->makeChildOf($c);

        $ch = Cluster::create(['name' => 'C.2']);
        $ch->makeChildOf($c);

        $this->assertTrue(Cluster::isValidNestedSet());

        // Build expectations (expected trees/subtrees)
        $expectedWholeTree = [
      'A' => ['A.1' => null, 'A.2' => null],
      'B' => [
        'B.1' => null,
        'B.2' =>
        [
          'B.2.1' => null,
          'B.2.2' => ['B.2.2.1' => null],
          'B.2.3' => null,
        ],
        'B.3' => null,
      ],
      'C' => ['C.1' => null, 'C.2' => null],
      'D' => null,
    ];

        $expectedSubtreeA = ['A' => ['A.1' => null, 'A.2' => null]];

        $expectedSubtreeB = [
      'B' => [
        'B.1' => null,
        'B.2' =>
        [
          'B.2.1' => null,
          'B.2.2' => ['B.2.2.1' => null],
          'B.2.3' => null,
        ],
        'B.3' => null,
      ],
    ];

        $expectedSubtreeC = ['C.1' => null, 'C.2' => null];

        $expectedSubtreeD = ['D' => null];

        // Perform assertions
        $wholeTree = hmap(Cluster::all()->toHierarchy()->toArray());
        $this->assertEquals($expectedWholeTree, $wholeTree);

        $subtreeA = hmap(Cluster::firstWhere('name', 'A')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertEquals($expectedSubtreeA, $subtreeA);

        $subtreeB = hmap(Cluster::firstWhere('name', 'B')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertEquals($expectedSubtreeB, $subtreeB);

        $subtreeC = hmap(Cluster::firstWhere('name', 'C')->getDescendants()->toHierarchy()->toArray());
        $this->assertEquals($expectedSubtreeC, $subtreeC);

        $subtreeD = hmap(Cluster::firstWhere('name', 'D')->getDescendantsAndSelf()->toHierarchy()->toArray());
        $this->assertEquals($expectedSubtreeD, $subtreeD);

        $this->assertTrue(Cluster::firstWhere('name', 'D')->getDescendants()->toHierarchy()->isEmpty());
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_nests_correctly_not_sequential()
    {
        $this->seed(ClusterSeeder::class);
        $parent = Cluster::firstWhere('name', 'Child 1');

        $parent->children()->create(['name' => 'Child 1.1']);

        $parent->children()->create(['name' => 'Child 1.2']);

        $this->assertTrue(Cluster::isValidNestedSet());

        $expected = [
      'Child 1' => [
        'Child 1.1' => null,
        'Child 1.2' => null,
      ],
    ];

        $parent->reload();
        $this->assertEquals($expected, hmap($parent->getDescendantsAndSelf()->toHierarchy()->toArray()));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_to_hierarchy_nests_correctly_with_order()
    {
        $this->seed(OrderedClusterSeeder::class);

        $expectedWhole = [
      'Root A' => null,
      'Root Z' => [
        'Child A' => null,
        'Child C' => null,
        'Child G' => ['Child G.1' => null],
      ],
    ];
        $this->assertEquals($expectedWhole, hmap(OrderedCluster::all()->toHierarchy()->toArray()));

        $expectedSubtreeZ = [
      'Root Z' => [
        'Child A' => null,
        'Child C' => null,
        'Child G' => ['Child G.1' => null],
      ],
    ];
        $this->assertEquals($expectedSubtreeZ, hmap(Cluster::firstWhere('name', 'Root Z', 'OrderedCluster')->getDescendantsAndSelf()->toHierarchy()->toArray()));
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_get_nested_list()
    {
        $this->seed(ClusterSeeder::class);
        $seperator = ' ';
        $nestedList = Cluster::getNestedList('name', 'id', $seperator);

        $expected = [
      '7461d8f5-2ea9-4788-99c4-9d0244f0bfb1' => str_repeat($seperator, 0).'Root 1',
      '5d7ce1fd-6151-46d3-a5b3-0ebb9988dc57' => str_repeat($seperator, 1).'Child 1',
      '07c1fc8c-53b5-4fe7-b9c4-e09f266a455c' => str_repeat($seperator, 1).'Child 2',
      '3315a297-af87-4ad3-9fa5-19785407573d' => str_repeat($seperator, 2).'Child 2.1',
      '054476d2-6830-4014-a181-4de010ef7114' => str_repeat($seperator, 1).'Child 3',
      '3bb62314-9e1e-49c6-a5cb-17a9ab9b1b9a' => str_repeat($seperator, 0).'Root 2',
    ];

        $this->assertEquals($expected, $nestedList);
    }
}
