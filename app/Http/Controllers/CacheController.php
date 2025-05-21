<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function getFromCache(Request $request)
    {
        $url = $request->query('data');
        
        if (!$url) {
            return response()->json(['error' => 'URL parameter is required'], 400);
        }

        // Check if data exists in cache
        $cachedData = DB::table('cache')->where('url', $url)->first();

        if ($cachedData) {
            return response()->json(json_decode($cachedData->data));
        }

        try {
            // Fetch data from the actual URL
            $response = Http::get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Store in cache
                DB::table('cache')->updateOrInsert(
                    ['url' => $url],
                    ['data' => json_encode($data)]
                );

                return response()->json($data);
            }

            return response()->json(['error' => 'Failed to fetch data from source'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
