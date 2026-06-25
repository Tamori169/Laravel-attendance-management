<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class T_13_ShowAttendanceDetailtest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->markTestIncomplete('実装中');

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
