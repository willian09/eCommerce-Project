<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $orders = Order::latest('orders.created_at')->select('orders.*', 'users.name', 'users.email');
        $orders = $orders->leftJoin('users', 'users.id', 'orders.user_id');

        if ($request->get('keyword') != "") {
            $orders = $orders->where('users.name', 'like', '%' . $request->keyword . '%');
            $orders = $orders->orWhere('users.email', 'like', '%' . $request->keyword . '%');
            $orders = $orders->orWhere('orders.id', 'like', '%' . $request->keyword . '%');
        }

        $orders = $orders->paginate(10);

        return view('admin.orders.list', [
            'orders' => $orders
        ]);
    }
    
    public function detail(string $id)
    {
        $order = Order::select('orders.*', 'countries.name as countryName')
            ->where('orders.id', $id)
            ->leftJoin('countries', 'countries.id', 'orders.country_id')
            ->first();

        $orderItems = OrderItem::where('order_id', $id)
            ->get();

        return view('admin.orders.detail', [
            'order' => $order,
            'orderItems' => $orderItems
        ]);
    }

    public function changeOrderStatus(Request $request, string $id)
    {
        $order = Order::find($id);
        $order->status = $request->status;
        $order->shipped_date = $request->shipped_date;
        $order->save();

        $message = 'Order status updated successfully';
        
        session()->flash('success', $message);
        
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
        
    }

    public function sendInvoiceEmail(Request $request, string $id)
    {
        orderEmail($id, $request->userType);
        
        $message = 'Order status updated successfully';

        session()->flash('success', $message);

        return response()->json([
            'status' => true,
            'message' => $message
        ]);

    }
}