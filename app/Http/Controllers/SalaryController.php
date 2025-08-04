<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\Request;

class SalaryController extends Controller
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
        $salaries = Salary::orderBy('created_at', 'desc')->get();
        return view('admin.salary.index', compact('salaries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::all();
        return view('admin.salary.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $user = User::find($request->user_id);

        Salary::create([
            'user_id' => $request->user_id,
            'month' => $request->month,
            'year' => $request->year,
            'amount' => $request->amount,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect('/salary')->with('message', 'New salary for ' . $user->name . ' has been added!');
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
        $salary = Salary::find($id);
        $users = User::all();
        return view('admin.salary.edit', compact('salary', 'users'));
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
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $user = User::find($request->user_id);

        Salary::where('id', $id)->update([
            'user_id' => $request->user_id,
            'month' => $request->month,
            'year' => $request->year,
            'amount' => $request->amount,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect('/salary')->with('message', 'Salary for ' . $user->name . ' has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $salary = Salary::find($id);
        $user = User::find($salary->user_id);
        Salary::where('id', $id)->delete();
        return redirect('salary')->with('message', 'Salary for ' . $user->name . ' has been deleted!');
    }
}
