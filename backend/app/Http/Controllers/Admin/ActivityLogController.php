<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    // Fetch all activity logs
    public function index()
    {
        $logs = ActivityLog::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json($logs);
    }
}
