<?php

namespace Encima\Albero\Tests\Unit\Category;

use Encima\Albero\Tests\TestCase;
use Illuminate\Events\Dispatcher;
use Encima\Albero\Tests\Models\Category\Category;
use Encima\Albero\Tests\Seeders\Category\CategorySeeder;

class CategoryCustomEventsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_movement_events_fire()
    {
        $firstChild = Category::firstWhere('name', 'Child 1');
        $otherChild = Category::firstWhere('name', 'Child 3');

        $this->partialMock(Dispatcher::class, function ($mock) use ($firstChild, $otherChild) {
            $mock->shouldReceive('until')
                    ->once()
                    ->with('eloquent.moving: '.get_class($otherChild), $firstChild)
                    ->andReturn(true);
            $mock->shouldReceive('dispatch')
                    ->once()
                    ->with('eloquent.moved: '.get_class($otherChild), $firstChild)
                    ->andReturn(true);
        });
        $firstChild::setEventDispatcher(app(Dispatcher::class));
        $firstChild->moveToRightOf($otherChild);
    }

    /**
     * @test
     * Created: 2020-05-13
     * Updated: 2020-05-13
     */
    public function it_movement_halts_when_returning_false_from_moving()
    {
        $unchanged = Category::firstWhere('name', 'Child 2');

        $this->partialMock(Dispatcher::class, function ($mock) use ($unchanged) {
            $mock->shouldReceive('until')
                    ->once()
                    ->with('eloquent.moving: '.get_class($unchanged), $unchanged)
                    ->andReturn(false);
        });
        $unchanged::setEventDispatcher(app(Dispatcher::class));

        // Force "moving" to return false
        Category::moving(function ($node) {
            return false;
        });
        $unchanged->makeRoot();
        $unchanged->fresh();
        $this->assertSame(1, $unchanged->getParentId());
        $this->assertSame(1, $unchanged->getLevel());
        $this->assertSame(4, $unchanged->getLeft());
        $this->assertSame(7, $unchanged->getRight());
    }
}
