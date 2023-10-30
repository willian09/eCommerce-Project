<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function changePassword()
    {
        return view('admin.change-password');
    }

    public function processChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|min:8|same:new_password'
        ]);

        $id = Auth::guard('admin')->user()->id;
        $admin = User::where('id', $id)->first();

        if ($validator->passes()) {            

            if (!Hash::check($request->old_password, $admin->password)) {
                session()->flash('error', 'Your old password is not correct, please try again');
                return response()->json([
                    'status' => true,
                ]);
            }

            User::where('id', $id)->update([
                'password' => Hash::make($request->new_password)
            ]);
            session()->flash('success', 'Your have successfully changed your password');
            return response()->json([
                'status' => true,
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function changeSettings()
    {
        $userId = Auth::user()->id;
        $user = User::where('id', $userId)->first();

        return view('admin.settings', ['user' => $user]);
    }

    public function processChangeSettings(Request $request)
{
    $userId = Auth::user()->id;
    
    $validator = Validator::make($request->all(), [
        'name' => 'required|min:5',
        'email' => 'required|email|unique:users,email,' . $userId . ',id',
    ]);
    
    if ($validator->passes()) {
        $user = User::find($userId);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        session()->flash('success', 'Profile updated successfully');
        
        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ]);
    }
}

}
