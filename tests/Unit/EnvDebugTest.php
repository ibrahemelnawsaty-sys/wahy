<?php

namespace Tests\Unit;

use Tests\TestCase;

class EnvDebugTest extends TestCase
{
    public function test_environment_is_testing(): void
    {
        $this->assertEquals('testing', app()->environment(), 'env: ' . app()->environment());
        $this->assertEquals('sqlite', config('database.default'), 'db.default: ' . config('database.default'));
        $this->assertEquals(
            ':memory:',
            config('database.connections.sqlite.database'),
            'sqlite.db: ' . config('database.connections.sqlite.database'),
        );
    }
}
