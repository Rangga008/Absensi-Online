<?php

namespace App\Http\Controllers;

use App\Models\Concession;
use App\Models\User;
use Illuminate\Http\Request;

class ConcessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!session('is_admin') && session('role_id') != 3) {
            return redirect()->route('admin.login');
        }

        if (session('role_id') == 3) { // If user is karyawan
            $concessions = Concession::where('user_id', session('user_id'))
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);
        } else {
            $concessions = Concession::with('user')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);
        }
        
        return view('admin.concession.index', compact('concessions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $users = User::where('role_id', 4)->get(); // Only show karyawan users
        return view('admin.concession.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|in:sakit,izin,cuti',
            'description' => 'required|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        Concession::create([
            'user_id' => $request->user_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'pending'
        ]);

        return redirect()->route('admin.concessions.index')
               ->with('success', 'Concession created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $concession = Concession::findOrFail($id);
        $users = User::where('role_id', 4)->get(); // Only show karyawan users
        return view('admin.concession.edit', compact('concession', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|in:sakit,izin,cuti',
            'description' => 'required|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $concession = Concession::findOrFail($id);
        $concession->update($request->all());

        return redirect()->route('admin.concessions.index')
               ->with('success', 'Concession updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $concession = Concession::findOrFail($id);
        $concession->delete();

        return redirect()->route('admin.concessions.index')
               ->with('success', 'Concession deleted successfully!');
    }
}