<?php

namespace Noxomix\LaravelRollo\Tests;

use Noxomix\LaravelRollo\Rollo;

class ExampleTest extends TestCase
{
    public function test_it_can_greet()
    {
        $rollo = new Rollo();
        
        $this->assertEquals('Hello, World! This is Rollo.', $rollo->greet());
        $this->assertEquals('Hello, John! This is Rollo.', $rollo->greet('John'));
    }
    
    public function test_it_can_check_if_enabled()
    {
        $rollo = new Rollo();
        
        $this->assertTrue($rollo->isEnabled());
    }
}