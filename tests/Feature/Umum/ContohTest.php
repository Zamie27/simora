<?php

namespace Tests\Feature\Umum;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContohTest extends TestCase
{
    use RefreshDatabase;

    public function test_mengembalikan_respons_sukses()
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }
}
