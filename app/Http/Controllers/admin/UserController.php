<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::orderBy('id', 'ASC');

        if (!empty($request->get('keyword'))) {
            $users = $users->where('name', 'like', '%' . $request->get('keyword') . '%');
            $users = $users->orWhere('email', 'like', '%' . $request->get('keyword') . '%');
        }

        $users = $users->paginate(10);
        return view('admin.users.list', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|numeric',
            'password' => 'required|min:8',
        ]);

        if ($validator->passes()) {

            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->status = $request->status;
            $user->password = Hash::make($request->password);
            $user->save();

            $message = 'User added successfully';
            session()->flash('success', $message);
            return response()->json([
                'status' => true,
                'message' => $message
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request,string $id)
    {
        $user = User::find($id);

        if ($user == null) {
            $message = 'User not found';
            session()->flash('error', $message);
            return redirect()->route('users.index');
        }

        return view('admin.users.edit' ,[
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if ($user == null) {
            $message = 'User not found';
            session()->flash('error', $message);
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id . ',id',
            'phone' => 'required|numeric',
        ]);

        if ($validator->passes()) {

            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->status = $request->status;
            if ($request->password != '') {
                $user->password = Hash::make($request->password);                
            }
            $user->update();

            $message = 'User added successfully';
            session()->flash('success', $message);
            return response()->json([
                'status' => true,
                'message' => $message
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (empty($user)) {
            $message = 'User not found';
            session()->flash('error', $message);
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        }

        $user->delete();

        $message = 'User deleted successfully';
        session()->flash('success', $message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
}
