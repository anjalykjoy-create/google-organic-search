<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_search_page_is_available(): void
    {
        $response = $this->get('/search');

        $response->assertStatus(200);
    }
}