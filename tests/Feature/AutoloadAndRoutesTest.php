<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AutoloadAndRoutesTest extends TestCase
{
    /**
     * Basic smoke test that the application can list routes (bootstraps the app).
     * This will fail early if route files are missing or the app cannot boot.
     *
     * @return void
     */
    public function test_route_list_runs()
    {
        $exit = Artisan::call('route:list', ['--no-ansi' => true]);
        $this->assertIsInt($exit);
        $this->assertEquals(0, $exit, 'artisan route:list should exit with code 0');
    }
}
