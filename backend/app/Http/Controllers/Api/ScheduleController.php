<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    /**
     * Get all schedules with optional filters
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['lawyer', 'creator']);

        // Filter by lawyer
        if ($request->lawyer_id) {
            $query->where('lawyer_id', $request->lawyer_id);
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
        Schedule::where('hearing_date', '<', now())
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
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
        })->get(['id', 'first_name', 'last_name', 'email']);

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
            'title' => 'required|string|max:255',
            'case_number' => 'required|string|max:100',
            'court_location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'hearing_date' => 'required|date',
            'status' => 'required|in:scheduled,completed,cancelled,rescheduled'
        ]);

        // Check if hearing date is in the past
        $hearingDate = Carbon::parse($data['hearing_date']);
        if ($hearingDate->isPast()) {
            throw ValidationException::withMessages([
                'hearing_date' => ['Cannot create a schedule on a past date. Please select a future date.']
            ]);
        }

        $data['created_by'] = auth()->id();
        
        // Force status to 'scheduled' for new schedules
        $data['status'] = 'scheduled';

        $schedule = Schedule::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Schedule created successfully',
            'data' => $schedule->load(['lawyer', 'creator'])
        ]);
    }

    /**
     * Show a single schedule
     */
    public function show($id)
    {
        $schedule = Schedule::with(['lawyer', 'creator'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $schedule
        ]);
    }

    /**
     * Update a schedule - For past dates, creates a new schedule instead
     */
    public function update(Request $request, $id)
    {
        $originalSchedule = Schedule::findOrFail($id);

        $data = $request->validate([
            'lawyer_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'case_number' => 'required|string|max:100',
            'court_location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'hearing_date' => 'required|date',
            'status' => 'required|in:scheduled,completed,cancelled,rescheduled'
        ]);

        $newHearingDate = Carbon::parse($data['hearing_date']);
        $originalDate = Carbon::parse($originalSchedule->hearing_date);

        // CASE 1: Updating a FUTURE schedule
        if (!$originalDate->isPast()) {
            // Cannot change to past date
            if ($newHearingDate->isPast()) {
                throw ValidationException::withMessages([
                    'hearing_date' => ['Cannot reschedule to a past date. Please select a future date.']
                ]);
            }

            // Update the existing schedule
            $originalSchedule->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'data' => $originalSchedule->load(['lawyer', 'creator'])
            ]);
        }
        
        // CASE 2: Updating a PAST schedule - Create a new schedule instead
        else {
            // Cannot create new schedule on past date
            if ($newHearingDate->isPast()) {
                throw ValidationException::withMessages([
                    'hearing_date' => ['Cannot reschedule to a past date. Please select a future date.']
                ]);
            }

            // Create new schedule with same data but new date
            $newScheduleData = [
                'lawyer_id' => $data['lawyer_id'],
                'title' => $data['title'],
                'case_number' => $data['case_number'],
                'court_location' => $data['court_location'],
                'description' => $data['description'],
                'notes' => $data['notes'],
                'hearing_date' => $data['hearing_date'],
                'status' => 'scheduled', // Always set to scheduled for new schedules
                'created_by' => auth()->id()
            ];

            $newSchedule = Schedule::create($newScheduleData);

            // Optionally update the original schedule status to 'rescheduled'
            $originalSchedule->update(['status' => 'rescheduled']);

            return response()->json([
                'success' => true,
                'message' => 'Schedule has been rescheduled. A new schedule has been created with the updated date.',
                'data' => $newSchedule->load(['lawyer', 'creator']),
                'original_schedule_id' => $originalSchedule->id,
                'is_rescheduled' => true
            ]);
        }
    }

    /**
     * Delete a schedule - Cannot delete past schedules
     */
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        
        $hearingDate = Carbon::parse($schedule->hearing_date);
        if ($hearingDate->isPast()) {
            throw ValidationException::withMessages([
                'schedule' => ['Cannot delete a past schedule. You can mark it as completed or cancelled instead.']
            ]);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully'
        ]);
    }

    /**
     * Update schedule status only - For Reschedule/Cancel/Complete buttons
     */
    public function updateStatus(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled,rescheduled'
        ]);

        $schedule->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Schedule status updated successfully',
            'data' => $schedule->load(['lawyer', 'creator'])
        ]);
    }

    /**
     * Get upcoming schedules (next 5)
     */
    public function upcomingSchedules()
    {
        $schedules = Schedule::with(['lawyer'])
            ->where('hearing_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('hearing_date')
            ->limit(5)
            ->get([
                'id',
                'title',
                'hearing_date',
                'status',
                'lawyer_id',
                'court_location'
            ]);

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }
}