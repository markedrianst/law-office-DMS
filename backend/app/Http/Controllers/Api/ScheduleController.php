<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Get all schedules with optional filters
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['lawyer', 'creator']);

        // Filter by lawyer (ensure user has role = lawyer)
        if ($request->lawyer_id) {
            $query->whereHas('lawyer.role', function ($q) {
                $q->where('role', 'lawyer');
            })->where('lawyer_id', $request->lawyer_id);
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by start date
        if ($request->start_date) {
            $query->whereDate('hearing_date', '>=', $request->start_date);
        }

        // Filter by end date
        if ($request->end_date) {
            $query->whereDate('hearing_date', '<=', $request->end_date);
        }

        // Auto-update past hearings to "completed" if status not updated
        Schedule::where('hearing_end_date', '<', now())
            ->where('status', '!=', 'completed')
            ->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('hearing_date')->get()
        ]);
    }

    /**
     * Get all lawyers
     */
    public function lawyers()
    {
        $lawyers = User::whereHas('role', function ($query) {
            $query->where('role', 'lawyer');
        })->get();

        return response()->json([
            'success' => true,
            'data' => $lawyers
        ]);
    }

    /**
     * Store a new schedule
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'lawyer_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'case_number' => 'nullable|string',
            'court_location' => 'nullable|string',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'hearing_date' => 'required|date',
            'hearing_end_date' => 'nullable|date|after_or_equal:hearing_date',
            'status' => 'required'
        ]);

        $data['created_by'] = auth()->id();

        Schedule::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Schedule created successfully'
        ]);
    }

    /**
     * Show a single schedule
     */
    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => Schedule::with(['lawyer', 'creator'])->findOrFail($id)
        ]);
    }

    /**
     * Update a schedule
     */
    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $data = $request->validate([
            'lawyer_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'case_number' => 'nullable|string',
            'court_location' => 'nullable|string',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'hearing_date' => 'required|date',
            'hearing_end_date' => 'nullable|date|after_or_equal:hearing_date',
            'status' => 'required'
        ]);

        $schedule->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully'
        ]);
    }

    /**
     * Delete a schedule
     */
    public function destroy($id)
    {
        Schedule::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully'
        ]);
    }

    /**
     * Get upcoming schedules (next 5)
     */
    public function upcomingSchedules()
    {
        $data = Schedule::where('hearing_date', '>=', now())
            ->orderBy('hearing_date')
            ->limit(5)
            ->get([
                'id',
                'title',
                'hearing_date as start_time',
                'hearing_end_date as end_time',
                'status'
            ]);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
