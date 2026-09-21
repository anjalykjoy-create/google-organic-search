<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->input('query', ''));

        if ($query === '') {
            return response()->json([
                'success' => false,
                'message' => 'Search keyword is required.'
            ], 422);
        }

        $apiKey = config('services.serpapi.key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'SerpApi API key is missing.'
            ], 500);
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->get(
                    'https://serpapi.com/search.json',
                    [
                        'engine' => 'google',
                        'q' => $query,
                        'api_key' => $apiKey,
                        'num' => 10,
                    ]
                );

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'SerpApi error: ' .
                        ($response->json('error') ?? 'Unknown error')
                ], $response->status());
            }

            $data = $response->json();

            $organicResults = $data['organic_results'] ?? [];

            $results = collect($organicResults)
                ->take(10)
                ->map(function ($item, $index) {
                    return [
                        'position' => $index + 1,
                        'title' => $item['title'] ?? '',
                        'link' => $item['link'] ?? '',
                        'snippet' => $item['snippet'] ?? '',
                    ];
                })
                ->values()
                ->all();

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results
            ]);

        }catch (\Throwable $e) {

    return response()->json([
        'success' => false,
        'message' => 'Unable to connect to search service.',
        'error' => $e->getMessage()
    ], 500);
        }
    }
}