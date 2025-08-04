<?php

namespace App\Http\Controllers;

use App\Models\Concession;
use App\Models\User;
use Illuminate\Http\Request;

class ConcessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!session()->has('username')) {
            return back();
        }
        $concessions = Concession::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.concession.index', compact('concessions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (session('role_id') == 3) {
            abort(404);
        }
        $users = User::all();
        return view('admin.concession.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (session('role_id') == 3) {
            abort(404);
        }
        $request->validate([
            'description' => 'required',
        ]);

        $user = User::find($request->user_id);

        Concession::create([
            'user_id' => $request->user_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect('concession')->with('message', 'New concession for <b>' . $user->name . '</b> has been created!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (session('role_id') == 3) {
            abort(404);
        }
        $users = User::all();
        $concession = Concession::find($id);
        return view('admin.concession.edit', compact('concession', 'users'));
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
        if (session('role_id') == 3) {
            abort(404);
        }
        $request->validate([
            'description' => 'required',
        ]);

        $user = User::find($request->user_id);

        Concession::where('id', $id)->update([
            'user_id' => $request->user_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect('concession')->with('message', 'Concession from <b>' . $user->name . '</b> has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (session('role_id') == 3) {
            abort(404);
        }
        $concession = Concession::find($id);
        $user = User::find($concession->user->id);
        Concession::where('id', $id)->delete();
        return redirect('concession')->with('message', 'Concession from <strong>' . $user->name . '</strong> has been deleted!');
    }
}
