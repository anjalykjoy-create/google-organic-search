<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    public function test_empty_search_query_returns_validation_error(): void
    {
        $response = $this->postJson('/search', [
            'query' => '',
        ]);

        $response->assertStatus(422);

        $response->assertJson([
            'success' => false,
            'message' => 'Search keyword is required.',
        ]);
    }

    public function test_google_search_returns_organic_results(): void
    {
        Http::fake([
            'https://serpapi.com/*' => Http::response([
                'organic_results' => [
                    [
                        'title' => 'Test Result 1',
                        'link' => 'https://example.com/1',
                        'snippet' => 'Test snippet 1',
                    ],
                    [
                        'title' => 'Test Result 2',
                        'link' => 'https://example.com/2',
                        'snippet' => 'Test snippet 2',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/search', [
            'query' => 'Coffee shops in Prague',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'success' => true,
            'query' => 'Coffee shops in Prague',
        ]);

        $response->assertJsonCount(2, 'results');

        $response->assertJsonPath(
            'results.0.position',
            1
        );

        $response->assertJsonPath(
            'results.0.title',
            'Test Result 1'
        );

        $response->assertJsonPath(
            'results.0.link',
            'https://example.com/1'
        );

        $response->assertJsonPath(
            'results.0.snippet',
            'Test snippet 1'
        );
    }

    public function test_search_api_error_is_handled(): void
    {
        Http::fake([
            'https://serpapi.com/*' => Http::response([
                'error' => 'API request failed',
            ], 500),
        ]);

        $response = $this->postJson('/search', [
            'query' => 'Coffee shops in Prague',
        ]);

        $response->assertStatus(500);

        $response->assertJson([
            'success' => false,
        ]);
    }
}