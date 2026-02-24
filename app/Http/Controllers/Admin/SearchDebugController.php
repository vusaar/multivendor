<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationLog;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\StorefrontProductController;

class SearchDebugController extends Controller
{
    public function index()
    {
        $logs = ApplicationLog::where(function($query) {
                $query->where('source', 'like', '%Search%')
                      ->orWhere('message', 'like', '%user_query%');
            })
            ->latest()
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.search_debug.index', compact('logs'));
    }

    public function replay($id)
    {
        $log = ApplicationLog::findOrFail($id);
        $data = is_array($log->message) ? $log->message : json_decode($log->message, true);

        if (!$data || !isset($data['user_query'])) {
            return response()->json(['error' => 'Invalid log format for replay'], 400);
        }

        $userQuery = $data['user_query'];
        $structured = $data['structured_query'] ?? [];

        // 1. Raw Meilisearch Debug
        // Reconstruct the search query exactly as search2 does
        $queryStr = $userQuery ?: ($structured['item'] ?? '*');
        
        
        // Re-apply filters matching search2 logic
       
        
        
      

        // 2. Full API Simulation
        // Create a mock request to pass to the actual controller
        $mockRequest = new Request();
        $mockRequest->merge([
            'user_query' => $userQuery,
            'structured_query' => $structured,
            'debug' => true
        ]);

        $apiController = new StorefrontProductController();
        $apiResponse = $apiController->search2($mockRequest);
        $apiData = $apiResponse->getData(true); // Get JSON array

        return response()->json([
            'log_data' => $data,
            'raw_meilisearch' => $apiData['raw_results'],
            'api_response' => $apiData['search_results']
        ]);
    }
}
