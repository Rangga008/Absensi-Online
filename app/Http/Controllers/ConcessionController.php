<?php

namespace App\Http\Controllers;

use App\Models\Concession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ConcessionController extends Controller
{
    /**
     * Display concession form for user
     */
    public function createForUser()
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }
        
        return view('user.concession.create');
    }

    /**
     * Store concession for user
     */
    public function storeForUser(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'reason' => 'required|in:sakit,izin,cuti',
            'description' => 'required|string|min:5|max:500',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        DB::beginTransaction();
        try {
            $concession = Concession::create([
                'user_id' => session('user_id'),
                'reason' => $validated['reason'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'pending'
            ]);

            DB::commit();
            
            return redirect()->route('user.home')
                   ->with('success', 'Pengajuan izin berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User concession error: '.$e->getMessage());
            return back()->withInput()
                   ->with('error', 'Gagal menyimpan pengajuan izin');
        }
    }

    /**
     * Show concession history for user
     */
    public function userHistory(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $search = $request->get('search');
        
        $concessions = Concession::where('user_id', session('user_id'))
                        ->when($search, function($q) use ($search) {
                            $q->where(function($query) use ($search) {
                                $query->where('reason', 'like', "%$search%")
                                      ->orWhere('description', 'like', "%$search%")
                                      ->orWhere('status', 'like', "%$search%");
                            });
                        })
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return view('user.concession.history', compact('concessions'));
    }

    /**
     * Display a listing of concessions for admin
     */
    public function index()
    {
        // Check if user is admin
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $concessions = Concession::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $users = User::where('role_id', '!=', 1) // Exclude admin jika perlu
                ->orderBy('name')
                ->get();

            return view('admin.concession.index', compact('concessions', 'users'));
        } catch (\Exception $e) {
            Log::error('Error loading concessions index: '.$e->getMessage());
            return back()->with('error', 'Gagal memuat data pengajuan izin');
        }
    }

    /**
     * Show the form for creating a new concession (admin)
     */
    public function create()
{
    if (!session('is_admin')) {
        return redirect()->route('admin.login');
    }

    try {
        // Ambil semua user dengan role, bukan hanya role_id = 4
        $users = User::with('role')->get();
        
        return view('admin.concession.create', compact('users'));
    } catch (\Exception $e) {
        Log::error('Error loading concession create form: '.$e->getMessage());
        return back()->with('error', 'Gagal memuat form pengajuan izin');
    }
}

    /**
     * Store a newly created concession (admin)
     */
    public function store(Request $request)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|in:sakit,izin,cuti',
            'description' => 'required|string|min:5|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        DB::beginTransaction();
        try {
            Concession::create([
                'user_id' => $validated['user_id'],
                'reason' => $validated['reason'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'pending',
                'approved_by' => session('user_id')
            ]);

            DB::commit();
            return redirect()->route('admin.concession.index')
                   ->with('success', 'Concession created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin concession error: '.$e->getMessage());
            return back()->withInput()
                   ->with('error', 'Failed to create concession');
        }
    }

    /**
     * Display the specified concession
     */
    public function show($id)
{
    if (!session('is_admin')) {
        return redirect()->route('admin.login');
    }

    try {
        // Load concession dengan relasi user dan approver
        $concession = Concession::with(['user', 'approver'])
            ->findOrFail($id);
            
        return view('admin.concession.show', compact('concession'));
    } catch (\Exception $e) {
        Log::error('Error loading concession: '.$e->getMessage(), [
            'id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Gagal memuat detail pengajuan izin: ' . $e->getMessage());
    }
}

    /**
     * Show the form for editing the specified concession
     */
    public function edit($id)
{
    if (!session('is_admin')) {
        return redirect()->route('admin.login');
    }

    try {
        $concession = Concession::with(['user', 'approver'])->findOrFail($id);
        
        // Ambil semua user dengan role karyawan (sesuaikan role_id)
        $users = User::whereIn('role_id', [1, 2, 3, 4]) // Role karyawan
                   ->orderBy('name')
                   ->get();

        return view('admin.concession.edit', compact('concession', 'users'));
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Concession not found: '.$e->getMessage(), ['id' => $id]);
        return redirect()->route('admin.concessions.index')
               ->with('error', 'Data pengajuan izin tidak ditemukan');
               
    } catch (\Exception $e) {
        Log::error('Error loading concession edit form: '.$e->getMessage(), [
            'id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->route('admin.concessions.index')
               ->with('error', 'Gagal memuat form edit: ' . $e->getMessage());
    }
}

    /**
     * Update the specified concession
     */
    public function update(Request $request, $id)
{
    if (!session('is_admin')) {
        return redirect()->route('admin.login');
    }

    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'reason' => 'required|in:sakit,izin,cuti',
        'description' => 'required|string|min:5|max:500',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'status' => 'required|in:pending,approved,rejected'
    ]);

    DB::beginTransaction();
    try {
        $concession = Concession::findOrFail($id);
        
        // Add approval tracking if status changed to approved/rejected
        if (in_array($validated['status'], ['approved', 'rejected']) && $concession->status !== $validated['status']) {
            $validated['approved_by'] = session('user_id');
            $validated['approved_at'] = now();
        }

        $concession->update($validated);
        DB::commit();

        Log::info('Concession updated', ['id' => $id, 'status' => $validated['status']]);
        return redirect()->route('admin.concessions.index')
               ->with('success', 'Pengajuan izin berhasil diperbarui!');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating concession: '.$e->getMessage(), ['id' => $id, 'data' => $validated]);
        return back()->withInput()
               ->with('error', 'Gagal memperbarui pengajuan izin. Silakan coba lagi.');
    }
}

    /**
     * Remove the specified concession
     */
    public function destroy($id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        DB::beginTransaction();
        try {
            $concession = Concession::findOrFail($id);
            $concession->delete();
            DB::commit();

            Log::info('Concession deleted', ['id' => $id]);
            return redirect()->route('admin.concessions.index')
                   ->with('success', 'Pengajuan izin berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting concession: '.$e->getMessage(), ['id' => $id]);
            return back()->with('error', 'Gagal menghapus pengajuan izin. Silakan coba lagi.');
        }
    }

    /**
     * Approve concession
     */
    public function approve($id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        DB::beginTransaction();
        try {
            $concession = Concession::findOrFail($id);
            $concession->update([
                'status' => 'approved',
                'approved_by' => session('user_id'),
                'approved_at' => now()
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Pengajuan izin disetujui!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving concession: '.$e->getMessage(), ['id' => $id]);
            return back()->with('error', 'Gagal menyetujui pengajuan izin');
        }
    }

    /**
     * Reject concession
     */
    public function reject($id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        DB::beginTransaction();
        try {
            $concession = Concession::findOrFail($id);
            $concession->update([
                'status' => 'rejected',
                'approved_by' => session('user_id'),
                'approved_at' => now()
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Pengajuan izin ditolak!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting concession: '.$e->getMessage(), ['id' => $id]);
            return back()->with('error', 'Gagal menolak pengajuan izin');
        }
    }

    /**
     * Export concession to PDF with kopsurat
     */
    public function exportPdf($id)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $concession = Concession::with(['user', 'approver'])->findOrFail($id);

            $pdf = Pdf::loadView('admin.concession.export_pdf', compact('concession'));
            $filename = 'pengajuan-izin-' . $concession->user->name . '-' . $concession->id . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error exporting concession to PDF: '.$e->getMessage(), ['id' => $id]);
            return back()->with('error', 'Gagal mengekspor pengajuan izin ke PDF');
        }
    }
}
