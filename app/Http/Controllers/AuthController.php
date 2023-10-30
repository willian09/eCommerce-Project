<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function login()
    {
        return view('front.account.login');
    }
    
    public function register()
    {
        return view('front.account.register');
    }

    public function processRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ]); 

        if ($validator->passes()) {

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'You have been registred successfully');

            return response()->json([
                'status' => true,
            ]);

        }else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->passes()) {
            
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
                
                if (session()->has('url.intended')) {
                    return redirect(session()->get('url.intended'));
                }   

                return redirect()->route('account.profile');

            } else {
                return redirect()->route('account.login')
                    ->withInput($request->only('email'))
                    ->with('error', 'Either email/password is not correct');
            }

        } else {
            return redirect()->route('account.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
    }

    public function profile()
    {   
        $userId = Auth::user()->id;
        $countries = Country::orderBy('name', 'ASC')->get();
        $user = User::where('id', $userId)->first();
        $address = CustomerAddress::where('user_id', $userId)->first();

        return view('front.account.profile', [
            'user' => $user,
            'countries' => $countries,
            'address' => $address
        ]);
    }

    public function updateProfile(Request $request)
    {
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5',
            'email' => 'required|email|unique:users,email,' . $userId . ',id',
            'phone' => 'required'
        ]);

        if ($validator->passes()) {
            $user = User::find($userId);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
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

    public function updateAddress(Request $request)
    {
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:2',
            'last_name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required',
            'country_id' => 'required',
            'address' => 'required|min:8',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
        ]);

        if ($validator->passes()) {
            CustomerAddress::updateOrCreate(
                ['user_id' => $userId],
                [
                    'user_id' => $userId,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'mobile' => $request->mobile,
                    'country_id' => $request->country_id,
                    'address' => $request->address,
                    'apartment' => $request->apartment,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip' => $request->zip,
                ]
            );

            session()->flash('success', 'Address updated successfully');
            
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

    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login')
        ->with('success', 'You successfully logged out');
    }

    public function orders()
    {
        $data = [];
        $user = Auth::user();
        
        $orders = Order::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
        
        $data['orders'] = $orders;
        
        return view('front.account.order', $data);
    }
    
    public function orderDetail(string $id)
    {
        $data = [];        
        $user = Auth::user();

        $order = Order::where('user_id', $user->id)->where('id', $id)->first();
        $data['order'] = $order;

        $orderItems = OrderItem::where('order_id', $id)->get();
        $data['orderItems'] = $orderItems;

        $orderItemsCount = OrderItem::where('order_id', $id)->count();
        $data['orderItemsCount'] = $orderItemsCount;
        
        foreach ($orderItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $products[$item->product_id] = $product;
            }
        }
        $data['products'] = $products;

        return view('front.account.order-details', $data);
    }
    
    public function wishlist()
    {
        $wishlists = Wishlist::where('user_id', Auth::user()->id)->with('product')->get();
        $data = [
            'wishlists' => $wishlists,
        ];

        return view('front.account.wishlist', $data);
    }

    public function removeProductFromWishlist(Request $request)
    {
        $wishlist = Wishlist::where('user_id', Auth::user()->id)->where('product_id', $request->id)->first();
        
        if ($wishlist == null) {
            session()->flash('error', 'Product already removed');
            return response()->json([
                'status' => true,
            ]);
        } else {
            Wishlist::where('user_id', Auth::user()->id)->where('product_id', $request->id)->delete();
            session()->flash('success', 'Product removed successfully');
            return response()->json([
                'status' => true,
            ]);
        }
    }

    public function changePassword()
    {
        return view('front.account.change-password');
    }

    public function processChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|min:8|same:new_password'
        ]);

        if ($validator->passes()) {

            $user = User::select('id', 'password')->where('id', Auth::user()->id)->first();

            if (!Hash::check($request->old_password, $user->password)) {
                session()->flash('error', 'Your old password is not correct, please try again');
                return response()->json([
                    'status' => true,
                ]);
            }

            User::where('id', $user->id)->update([
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

    public function forgotPassword()
    {
        return view('front.account.forgot-password');
    }

    public function processForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.forgotPassword')->withInput()->withErrors($validator);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now()
        ]);

        //Send email Here
        $user = User::where('email', $request->email)->first();

        $formData = [
            'token' => $token,
            'user' => $user,
            'mailsubject' => 'You have requested to reset your password'
        ];

        Mail::to($request->email)->send(new ResetPassword($formData));
        return redirect()->route('account.login')->with('success', 'Please check your inbox to reset your password.');
    }

    public function resetPassword($token)
    {
        $tokenExist = DB::table('password_reset_tokens')->where('token', $token)->first();

        if ($tokenExist == null) {
            return redirect()->route('account.forgotPassword')->with('errors', 'Invalid request');
        }

        return view('front.account.reset-password', [
            'token' => $token
        ]);
    }

    public function processResetPassword(Request $request)
    {
        $token = $request->token;

        $tokenObj = DB::table('password_reset_tokens')->where('token', $token)->first();

        if ($tokenObj == null) {
            return redirect()->route('account.forgotPassword')->with('error', 'Invalid request');
        }

        $user = User::where('email', $tokenObj->email)->first();

        $validator = Validator::make($request->all(),[
            'new_password' => 'required|min:8',
            'confirm_new_password' => 'required|same:new_password'
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.resetPassword', $token)->withInput()->withErrors($validator);
        }
        
        User::where('id', $user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return redirect()->route('account.login')->with('success', 'You have successfully updated your password');
    }
}
