<?php

namespace Noxomix\LaravelRollo;

class Rollo
{
    public function greet(string $name = 'World'): string
    {
        return "Hello, {$name}! This is Rollo.";
    }
    
    public function isEnabled(): bool
    {
        return config('rollo.enabled', true);
    }
}