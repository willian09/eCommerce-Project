<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DiscountCouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $discountCoupons = DiscountCoupon::orderBy('id', 'ASC');

            if (!empty($request->get('keyword'))) {
                $discountCoupons = $discountCoupons->where('name', 'like','%' . $request->get('keyword') . '%');
                $discountCoupons = $discountCoupons->orWhere('code', 'like','%' . $request->get('keyword') . '%');
            }

        $discountCoupons = $discountCoupons->paginate(10);
        return view('admin.discount.list', compact('discountCoupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.discount.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required|numeric',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            // Starting date must be greator than current date
            if (!empty($request->starts_at)) {
                $now = Carbon::now();
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);

                if($startAt->lte($now) == true) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['starts_at' => 'Start date can not be less than current date time'],
                    ]);
                }
            }

            // Expiry date must be greater than start date
            if (!empty($request->starts_at) && !empty($request->expires_at)) {
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);

                if($expiresAt->gt($startAt) == false) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expire date must be greater than start date time'],
                    ]);
                }
            }
            
            $discountCoupon = new DiscountCoupon();
            $discountCoupon->code = $request->code;
            $discountCoupon->name = $request->name;
            $discountCoupon->description = $request->description;
            $discountCoupon->max_uses = $request->max_uses;
            $discountCoupon->max_uses_user = $request->max_uses_user;
            $discountCoupon->type = $request->type;
            $discountCoupon->discount_amount = $request->discount_amount;
            $discountCoupon->min_amount = $request->min_amount;
            $discountCoupon->status = $request->status;
            $discountCoupon->starts_at = $request->starts_at;
            $discountCoupon->expires_at = $request->expires_at;
            $discountCoupon->save();

            $message = 'Discount Coupun created successfully';
            session()->flash('success', $message);

            return response()->json([
                'status' => true,
                'message' => $message
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
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
    public function edit(string $id)
    {
        $discountCoupon = DiscountCoupon::find($id);
        if (empty($discountCoupon)) {
            return redirect()->route('discount.index');
        }
        
        return view('admin.discount.edit', compact('discountCoupon'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $discountCoupon = DiscountCoupon::find($id);

        if ($discountCoupon == null) {
            session()->flash('error', 'Record not found');
            return response()->json([
                'status' => true
            ]);
        }

        $validator = Validator::make($request->all(),[
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required|numeric',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            // Starting date must be greator than current date
            if (!empty($request->starts_at)) {
                $now = Carbon::now();
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);

                if($startAt->lte($now) == true) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['starts_at' => 'Start date can not be less than current date time'],
                    ]);
                }
            }

            // Expiry date must be greater than start date
            if (!empty($request->starts_at) && !empty($request->expires_at)) {
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);

                if($expiresAt->gt($startAt) == false) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expire date must be greater than start date time'],
                    ]);
                }
            }
            
            $discountCoupon = DiscountCoupon::find($id);
            $discountCoupon->code = $request->code;
            $discountCoupon->name = $request->name;
            $discountCoupon->description = $request->description;
            $discountCoupon->max_uses = $request->max_uses;
            $discountCoupon->max_uses_user = $request->max_uses_user;
            $discountCoupon->type = $request->type;
            $discountCoupon->discount_amount = $request->discount_amount;
            $discountCoupon->min_amount = $request->min_amount;
            $discountCoupon->status = $request->status;
            $discountCoupon->starts_at = $request->starts_at;
            $discountCoupon->expires_at = $request->expires_at;
            $discountCoupon->save();

            $message = 'Discount Coupun updated successfully';
            session()->flash('success', $message);

            return response()->json([
                'status' => true,
                'message' => $message
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $discountCoupon = DiscountCoupon::find($id);
        if (empty($discountCoupon)) {
            session()->flash('error', 'Discount Code not found');
            return response()->json([
                'status' => true,
                'message' => 'Discount Code not found'
            ]);
        }

        $discountCoupon->delete();

        session()->flash('success', 'Discount Code deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Discount Code deleted successfully'
        ]);
    }
}