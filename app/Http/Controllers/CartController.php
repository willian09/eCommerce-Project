<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\DiscountCoupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $product = Product::with('product_images')->find($request->id);
        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ]);
        }

        if (Cart::count() > 0) {
            $cartContent = Cart::content();
            $productAlreadyExist = false;

            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }

            if ($productAlreadyExist == false) {
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
                $status = true;
                $message = '<b>' . $product->title . ' </b>added in your cart successfully';
                session()->flash('success', $message);
            } else {
                $status = false;
                $message = $product->title . ' already added in cart';
            }
            
        } else {            
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);         
            $status = true;
            $message = '<strong>' . $product->title . ' </strong>added in your cart successfully';
            session()->flash('success', $message);            
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function cart()
    {
        $cartContent = Cart::content();
        $data ['cartContent'] = $cartContent;
        return view('front.cart', $data);
    }

    public function updateCart(Request $request)
    {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);

        $product = Product::find($itemInfo->id);

        if ($product->track_qty == 'Yes') {
            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                session()->flash('success', $message);            
            } else {
                $message = 'Requested quantity ('.$qty.') not available in stock';
                $status = false;
                session()->flash('error', $message);            
            }
        } else {
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
            session()->flash('success', $message);            
        }
        
        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function deleteItem(Request $request)
    {
        $itemInfo = Cart::get($request->rowId);
        
        if ($itemInfo == null) {
            $errorMessage = 'Item not found in cart';
            session()->flash('error', $errorMessage);            
            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }

        Cart::remove($request->rowId);

            $successMessage = 'Item removed from cart successfully';
            session()->flash('success', $successMessage);            
            return response()->json([
                'status' => true,
                'message' => $successMessage
            ]);
    }

    public function checkout()
{
    $discount = 0;

    if (Cart::count() == 0) {
        return redirect()->route('front.cart');
    }

    if (Auth::check() == false) {
        if (!session()->has('url.intended')) {
            session(['url.intended' => url()->current()]);
        }
        return redirect()->route('account.login');
    }

    $customerAddress = CustomerAddress::where('user_id', Auth::user()->id)->first();
    session()->forget('url.intended');

    $countries = Country::orderBy('name', 'ASC')->get();
    $subTotal = Cart::subtotal();
    $subTotal = str_replace(',', '', $subTotal);

    if (session()->has('code')) {
        $code = session('code');
        if (isset($code->type)) {
            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }
        }
    }

    $userCountryId = $customerAddress->country_id;

    // Calcular a taxa de envio com base na lógica do país
    $totalQty = 0;
    $totalShippingCharge = 0;
    $grandTotal = 0;
    foreach (Cart::content() as $item) {
        $totalQty += $item->qty;
    }

    $shippingInfo = ShippingCharge::where('country_id', $userCountryId)->first();

    if ($shippingInfo) {
        $totalShippingCharge = $totalQty * $shippingInfo->amount;
    } else {
        $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
        
        if ($shippingInfo) {
            $totalShippingCharge = $totalQty * $shippingInfo->amount;
        } else {
            $grandTotal = $subTotal - $discount;
            $totalShippingCharge = 0;
        }
    }

    $grandTotal = round(($subTotal + $totalShippingCharge) - $discount, 2);

    return view('front.checkout', [
        'countries' => $countries,
        'customerAddress' => $customerAddress,
        'totalShippingCharge' => $totalShippingCharge,
        'grandTotal' => $grandTotal,
        'discount' => $discount
    ]);
}

    public function processCheckout(Request $request)
    {
        // step 1 - Apply Validation

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:2',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required|min:8',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please fix the errors',
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        // step 2 - Save User Address

        $user = Auth::user();

        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
            ]
        );

        // step 3 - Store Data in Orders Table

        if ($request->payment_method == 'cod') {

            $promoCode = '';
            $promoCodeId = NULL;
            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2, '.', '');
            
            // Apply discount here
            if (session()->has('code')) {
                $code = session('code');
                if (isset($code->type)) {
                    if ($code->type == 'percent') {
                        $discount = ($code->discount_amount / 100) * $subTotal;
                    } else {
                        $discount = $code->discount_amount;
                    }
                }
                $promoCode = $code->code;
                $promoCodeId = $code->id;
            }

            // Calculate Shipping
            $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();

            $totalQty = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            if ($shippingInfo != null) {
                $shipping = $totalQty * $shippingInfo->amount;
                $grandTotal = ((double)$subTotal - $discount) + $shipping;

                
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                $shipping = $totalQty * $shippingInfo->amount;
                $grandTotal = ((double)$subTotal - $discount) + $shipping;
            }

            $order = new Order;
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->discount = $discount;
            $order->coupon_code_id = $promoCodeId;
            $order->coupon_code = $promoCode;
            $order->payment_status = 'not paid';
            $order->status = 'pending';
            $order->user_id = $user->id;
            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->country_id = $request->country;
            $order->address = $request->address;
            $order->apartment = $request->apartment;
            $order->city = $request->city;
            $order->state = $request->state;
            $order->zip = $request->zip;
            $order->notes = $request->oreder_notes;
            $order->save();

            // step 4 - Store Order Items in Order Items Table

            foreach (Cart::content() as $item) {
                $orderItem = new OrderItem;
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->name = $item->name;
                $orderItem->qty = $item->qty;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price * $item->qty;
                $orderItem->save();

                // Update Product Stock
                $productData = Product::find($item->id);
                if ($productData->track_qty == 'Yes') {
                    $currentQty = $productData->qty;
                    $updatedQty = $currentQty - $item->qty;
                    $productData->qty = $updatedQty;
                    $productData->save();                    
                }
            }
            
            // Send Order Email
            orderEmail($order->id, 'customer');

            session()->flash('success', 'You have successfully placed your order');

            Cart::destroy();
            session()->forget('code');

            return response()->json([
                'message' => 'Order save successfully',
                'orderId' => $order->id,
                'status' => true
            ]);

        } else {
              
        }

    }

    public function thankyou($id)
    {
        return view('front.thankyou', [
            'id' => $id
        ]);
    }

    public function getOrderSummery(Request $request)
    {
        $subTotal = Cart::subtotal(2, '.', '');
        $discount = 0;
        $discountString = '';

        // Apply discount here
        if (session()->has('code')) {
            $code = session('code');
            if (isset($code->type)) {
                if ($code->type == 'percent') {
                    $discount = ($code->discount_amount / 100) * $subTotal;
                } else {
                    $discount = $code->discount_amount;
                }
            }
            $discountString = '<div class="mt-4" id="discount-div">
                <strong>' . session()->get('code')->code . '</strong>
                <a class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></a>
            </div>';
        }


        if ($request->country_id > 0) {

            $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();

            $totalQty = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            if ($shippingInfo != null) {

                $shippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal = ((double)$subTotal-$discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2, ',', '.'),
                    'discount' => number_format($discount, 2, ',', '.'),
                    'shippingCharge' => number_format($shippingCharge, 2, ',', '.'),
                    'discountString' => $discountString
                ]);
                
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

                $shippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal = ((double)$subTotal-$discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2, ',', '.'),
                    'discount' => number_format($discount, 2, ',', '.'),
                    'shippingCharge' => number_format($shippingCharge, 2, ',', '.'),
                    'discountString' => $discountString
                ]);
            }

        } else {
            return response()->json([
                'status' => true,
                'grandTotal' => number_format($subTotal-$discount, 2, ',', '.'),
                'discount' => number_format($discount, 2, ',', '.'),
                'shippingCharge' => number_format(0, 2, ',', '.'),
                'discountString' => $discountString
            ]);
        }
    }

    public function applyDiscount(Request $request)
    {
        $code = DiscountCoupon::where('code', $request->code)->first();

        if ($code == null) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid discount coupon'
            ]);
        }

        // Check if coupon start date is valid or not
        $now = Carbon::now();
   
        if ($code->starts_at != "") {
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $code->starts_at);

            if ($now->lt($startDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon'
                ]);
            }
        }
        
        if ($code->expires_at != "") {
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $code->expires_at);

            if ($now->gt($endDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon'
                ]);
            }
        }

        // Check if coupon max uses is valid
        if ($code->max_uses > 0) {
            $couponUsed = Order::where('coupon_code_id', $code->id)->count();
            
            if ($couponUsed >= $code->max_uses) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon'
                ]);
            }            
        }        
        
        // Check if coupon max uses user is valid
        if ($code->max_uses_user > 0) {
            $couponUsedByUser = Order::where(['coupon_code_id' => $code->id, 'user_id' => Auth::user()->id])->count();

            if ($couponUsedByUser >= $code->max_uses_user) {
                return response()->json([
                    'status' => false,
                    'message' => 'You already used this coupon'
                ]);
            }
        }    
        
        // Check if min amount condition is valid
        $subTotal = Cart::subtotal(2, '.', '');
        if ($code->min_amount > 0) {
            if ($subTotal < $code->min_amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your min amount must be ' . number_format($code->min_amount, 2, ',', '.') . ' €'
                ]);
            }
        }    

        session()->put('code', $code);
        return $this->getOrderSummery($request);
    }

    public function removeDiscount(Request $request)
    {
        session()->forget('code');
        return $this->getOrderSummery($request);
    }
}
