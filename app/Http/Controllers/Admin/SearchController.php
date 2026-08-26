<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $fileType = $request->get('file_type', '');
            $dateFrom = $request->get('date_from', '');
            $dateTo = $request->get('date_to', '');

            if (empty($query)) {
                $results = collect();
            } else {
                $results = Folder::query()
                    ->where('name', 'LIKE', '%' . $query . '%')
                    ->when($fileType, fn($q) => $q->where('type', $fileType))
                    ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
                    ->when($dateTo, fn($q) => $q->where('created_at', '<=', $dateTo . ' 23:59:59'))
                    ->latest()
                    ->paginate(20);
            }

            return view('admin.search.index', compact('results', 'query', 'fileType', 'dateFrom', 'dateTo'));
        } catch (\Exception $e) {
            Log::error('SearchController::index failed: ' . $e->getMessage());
            return null;
        }
    }
}
