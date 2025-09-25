<?php

namespace App\Http\Controllers;

use App\Models\WorkTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WorkTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $workTimes = WorkTime::orderBy('name')->get();
            return view('admin.settings.work-times.index', compact('workTimes'));
        } catch (\Exception $e) {
            Log::error('Error loading work times', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error loading work times: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        return view('admin.settings.work-times.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'late_threshold' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            WorkTime::create([
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'late_threshold' => $request->late_threshold,
                'description' => $request->description,
                'is_active' => $request->has('is_active')
            ]);

            return redirect()->route('admin.settings.work-times.index')
                ->with('success', 'Work time has been created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating work time', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error creating work time: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $workTime = WorkTime::findOrFail($id);
            return view('admin.settings.work-times.show', compact('workTime'));
        } catch (\Exception $e) {
            Log::error('Error showing work time', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Work time not found');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $workTime = WorkTime::findOrFail($id);
            return view('admin.settings.work-times.edit', compact('workTime'));
        } catch (\Exception $e) {
            Log::error('Error loading edit form', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Work time not found');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'late_threshold' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $workTime = WorkTime::findOrFail($id);

            $workTime->update([
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'late_threshold' => $request->late_threshold,
                'description' => $request->description,
                'is_active' => $request->has('is_active')
            ]);

            return redirect()->route('admin.settings.work-times.index')
                ->with('success', 'Work time has been updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating work time', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Error updating work time: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $workTime = WorkTime::findOrFail($id);

            // Check if any users are assigned to this shift
            if ($workTime->users()->count() > 0) {
                return back()->with('error', 'Cannot delete work time that has users assigned to it');
            }

            $workTime->delete();

            Log::info('Work time deleted', [
                'work_time_id' => $id,
                'work_time_name' => $workTime->name,
                'admin_id' => session('admin_id')
            ]);

            return redirect()->route('admin.settings.work-times.index')
                ->with('success', 'Work time has been deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting work time', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Error deleting work time: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status of work time
     */
    public function toggleStatus($id)
    {
        try {
            $workTime = WorkTime::findOrFail($id);
            $workTime->update(['is_active' => !$workTime->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Work time status updated successfully',
                'is_active' => $workTime->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling work time status', ['error' => $e->getMessage(), 'id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating work time status'
            ], 500);
        }
    }
}
