<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The login page renders without requiring any seeded data.
     */
    public function test_the_login_page_loads(): void
    {
        $this->get('/login')->assertStatus(200);
    }
}
