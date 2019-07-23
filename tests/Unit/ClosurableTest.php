<?php

namespace Codrasil\Closurable\Tests\Unit;

use Codrasil\Closurable\Model;
use Codrasil\Closurable\Relations\ClosurablyRelatedTo;
use Codrasil\Closurable\Tests\Unit\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ClosurableTest extends TestCase
{
    /**
     * @test
     * @group  unit:nestable
     * @return void
     */
    public function it_can_return_the_closurably_related_class()
    {
        // Arrangements
        $model = $this->getMockBuilder(Model::class)->getMockForAbstractClass();

        // Actions
        $actual = $model->closurablyRelatedTo();

        // Assertions
        $this->assertInstanceOf(ClosurablyRelatedTo::class, $actual);
    }

    /**
     * @test
     * @group  unit:nestable
     * @return void
     */
    public function it_can_return_the_closurables()
    {
        // Arrangements
        $model = $this->getMockBuilder(Model::class)->getMockForAbstractClass();

        // Actions
        $actual = $model->closurables();

        // Assertions
        $this->assertInstanceOf(AdjacentlyRelatedTo::class, $actual);
    }
}
