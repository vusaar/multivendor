<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SearchLog;
use Illuminate\Http\Request;

class SearchLogController extends Controller
{
    public function index()
    {
        $logs = SearchLog::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.search-logs.index', compact('logs'));
    }

    public function show(SearchLog $searchLog)
    {
        return response()->json($searchLog);
    }
}
