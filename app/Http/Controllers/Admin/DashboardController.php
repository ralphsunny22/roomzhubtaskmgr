<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Order;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function adminDashboard()
    {
        $users = User::count();

        $products = Product::count();
        $productPending = Product::where('status', 'pending')->count();
        $productFeatured = Product::where('status', 'featured')->count();

        $vendors = Vendor::count();
        $vendorPending = Vendor::where('status', 'pending')->count();
        $vendorConfirmed = Vendor::where('status', 'confirmed')->count();
        $vendorSuspended = Vendor::where('status', 'suspended')->count();

        $customers = Order::distinct('created_by')->count();

        $orders = Order::count();
        $orderPending = Order::where('status', 'pending')->count();
        $orderDelivered = Order::where('status', 'delivered')->count();
        $orderCancelled = Order::where('status', 'cancelled')->count();

        return view('backend.dashboard', compact('products', 'productPending', 'productFeatured', 'users',
        'vendors', 'vendorPending', 'vendorConfirmed', 'vendorSuspended', 'customers',
        'orders', 'orderPending', 'orderDelivered', 'orderCancelled'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function allCustomer()
    {
        // Fetch distinct 'created_by' customers along with their relationship
        $customers = Order::with('customer')
            ->select('created_by') // Select only the 'created_by' column
            ->distinct()
            ->get();

        $allStatus = [
            ['name'=>'active', 'bgColor'=>'success'],
            ['name'=>'inactive', 'bgColor'=>'danger'],
        ]; //'completed', 'pending', 'draft'

        return view('backend.customer.allCustomer', compact('customers', 'allStatus'));
    }


    public function allVendor($status="")
    {
        if ($status=="") {
            $vendors = Vendor::with('user')->get();
        }
        if ($status=="pending") {
            $vendors = Vendor::with('user')->where('status', 'pending')->get();
        }
        if ($status=="confirmed") {
            $vendors = Vendor::with('user')->where('status', 'confirmed')->get();
        }
        if ($status=="suspended") {
            $vendors = Vendor::with('user')->where('status', 'suspended')->get();
        }


        $allStatus = [
            ['name'=>'confirmed', 'bgColor'=>'success'],
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'suspended', 'bgColor'=>'danger'],
        ];

        return view('backend.vendor.allVendor', compact('vendors', 'allStatus', 'status'));
    }

    public function allProduct($status="")
    {
        if ($status=="") {
            $products = Product::with(['createdBy','category'])->get();
        } else {
            $products = Product::with(['createdBy','category'])->where('status',$status)->get();
        }


        $allStatus = [
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'featured', 'bgColor'=>'success'],
        ];

        return view('backend.product.allProduct', compact('products', 'allStatus', 'status'));
    }

    public function allOrder($status="")
    {
        if ($status=="") {
            $orders = Order::with(['customer','vendor'])->get();
        } else {
            $orders = Order::with(['customer','vendor'])->where('status',$status)->get();
        }

        $allStatus = [
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'featured', 'bgColor'=>'success'],
        ];

        return view('backend.order.allOrder', compact('orders', 'allStatus', 'status'));
    }

    public function orderDetail(string $order_id)
    {
        $order = Order::find($order_id);
        $status = $order->status;

        $cartItems = $order->products;

        // Calculate total
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $allStatus = [
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'in progress', 'bgColor'=>'info'],
            ['name'=>'delivered', 'bgColor'=>'success'],
            ['name'=>'cancelled', 'bgColor'=>'danger'],
        ];
        return view('backend.order.orderDetail', compact('order', 'cartItems', 'total', 'allStatus', 'status'));
    }

    public function productDetail(string $product_id)
    {
        $product = Product::with('vendor')->where('id', $product_id)->first();
        $status = $product->status;

        $allStatus = [
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'in progress', 'bgColor'=>'info'],
            ['name'=>'delivered', 'bgColor'=>'success'],
            ['name'=>'cancelled', 'bgColor'=>'danger'],
        ];
        $alternateImages = json_decode($product->alternate_images);
        return view('backend.product.productDetail', compact('product', 'allStatus', 'alternateImages'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
