<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


Class OrderController extends Controller
{
    // INDEX - Show all orders
    public function index()
    {
        $orders = Order::latest()->get();
        return view('orders.index', compact('orders'));
    }


    // CREATE - Show form to create a new order
    public function create()
    {
        return view('orders.create');
    }


    // STORE - Create a new order
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'product_id' => 'required|in:SX9001,SX9002,SX9003,SX9004,SX9005,SX9006',
            'product_category' => 'required|in:clothing,ornaments,other',
            'buyer_gender' => 'required|in:male,female',
            'buyer_age' => 'required|integer|min:0',
            'order_location' => 'required|string',
            'international_shipping' => 'sometimes|boolean',
            'base_price' => 'required|numeric|min:0',
            'shipping_fee' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        $validated['international_shipping'] = $request->has('international_shipping');
        $validated['shipping_fee'] = $validated['international_shipping']
            ? ($validated['shipping_fee'] ?? 0)
            : 0;
        $validated['unit_price'] = $validated['base_price'] + $validated['shipping_fee'];
        $validated['final_amount'] = $validated['unit_price'] * $validated['quantity'];
        $validated['status'] = 'pending';
        $order = Order::create($validated);

        return redirect()->route('orders.index')->with('success', 'Order added successfully!');
    }

    // SHOW - Show a single order
    public function show(Order $order)
    {
        return view('orders.show', compact('order'));
    }

    // EDIT - Show form to edit an order
    public function edit(Order $order)
    {
        return view('orders.edit', compact('order'));
    }
    
    // UPDATE - Updating an order
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'product_id' => 'required|in:SX9001,SX9002,SX9003,SX9004,SX9005,SX9006',
            'product_category' => 'required|in:clothing,ornaments,other',
            'buyer_gender' => 'required|in:male,female',
            'buyer_age' => 'required|integer|min:0',
            'order_location' => 'required|string',
            'international_shipping' => 'sometimes|boolean',
            'base_price' => 'required|numeric|min:0',
            'shipping_fee' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ], [
            'product_id.size' => 'Product ID must be exactly 6 characters.',
        ]);

        $validated['international_shipping'] = $request->has('international_shipping');
        $validated['shipping_fee'] = $validated['international_shipping']
        ? ($validated['shipping_fee'] ?? 0)
        : 0;
        $validated['unit_price'] = $validated['base_price'] + $validated['shipping_fee'];
        $validated['final_amount'] = $validated['unit_price'] * $validated['quantity'];
        $order->update($validated);
        return redirect()->route('orders.index')->with('success', 'Order updated successfully!');
    }

    // DESTROY - Delete an order
    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted successfully!');
    }

    // UPDATE STATUS - Update the status of an order
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $previousStatus = $order->status;
        $newStatus = $validated['status'];

        if ($previousStatus !== $newStatus) {
            // Update the order status
            $order->update(['status' => $newStatus]);

            // Create a log entry
            OrderStatusLog::create([
                'order_id' => $order->id,
                'previous_status' => $previousStatus,
                'changed_status' => $newStatus,
                'changed_at' => now(),
                'changed_by' => Auth::id(),
            ]);
        }

        return redirect()->route('orders.index')->with('success', 'Order status updated successfully!');
    }



    // DASHBOARD
    public function dashboard(Request $request)
    {
        // Read the filter param (default = lifetime)
        $filter = $request->get('filter', 'lifetime');

        // Determine the date range based on the filter
        switch ($filter) {
            case 'this_month':
                $start = Carbon::now()->startOfMonth();
                $end   = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end   = Carbon::now()->subMonth()->endOfMonth();
                break;
            default: // lifetime
                $start = null;
                $end   = null;
        }

        // Build a base qury, applying the daye filter
        $query = Order::query();
        if ($start && $end) {
            $query->whereBetween('order_date', [$start->toDateString(), $end->toDateString()]);
        }

        // Use the query for all aggregations
        $totalSales = (clone $query)->sum('final_amount');
        $totalOrders = (clone $query)->count();
        $totalQuantity = (clone $query)->sum('quantity');
        $totalShippingCharges = (clone $query)->sum('shipping_fee');

        $ordersByProduct = (clone $query)
            ->select('product_id', DB::raw('count(*) as total'))
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $genderDistribution = (clone $query)
            ->select('buyer_gender', DB::raw('count(*) as total'))
            ->groupBy('buyer_gender')
            ->pluck('total', 'buyer_gender');

        $ordersByDate = (clone $query)
            ->selectRaw("DATE_FORMAT(order_date, '%b %Y') as month_label, MIN(order_date) as date_key, COUNT(*) as total")
            ->groupBy('month_label')
            ->orderBy('date_key')
            ->pluck('total', 'month_label');
        
        $ordersByLocation = (clone $query)
            ->select('order_location', DB::raw('count(*) as total'))
            ->groupBy('order_location')
            ->pluck('total', 'order_location');

        // Delta Aggregations : compare with previous period
        $salesDelta = null;
            if ($filter === 'this_month') {
                $prevStart = $start->copy()->subMonth()->startOfMonth();
                $prevEnd   = $start->copy()->subMonth()->endOfMonth();
                $prevSales = Order::whereBetween('order_date', [
                                $prevStart->toDateString(),
                                $prevEnd->toDateString()
                            ])->sum('final_amount');
                if ($prevSales > 0) {
                    $salesDelta = ($totalSales - $prevSales) / $prevSales * 100;
                }
            }
        
        $ordersDelta = null;
            if ($filter === 'this_month') {
                $prevStart = $start->copy()->subMonth()->startOfMonth();
                $prevEnd   = $start->copy()->subMonth()->endOfMonth();
                $prevOrders = Order::whereBetween('order_date', [
                                $prevStart->toDateString(),
                                $prevEnd->toDateString()
                            ])->count();
                if ($prevOrders > 0) {
                    $ordersDelta = ($totalOrders - $prevOrders) / $prevOrders * 100;
                }
            }

        // Age Distribution
        $raw = (clone $query)
            ->selectRaw("
                CASE
                    WHEN buyer_age BETWEEN 0  AND 10 THEN '0–10'
                    WHEN buyer_age BETWEEN 11 AND 20 THEN '11–20'
                    WHEN buyer_age BETWEEN 21 AND 30 THEN '21–30'
                    WHEN buyer_age BETWEEN 31 AND 40 THEN '31–40'
                    WHEN buyer_age BETWEEN 41 AND 50 THEN '41–50'
                    WHEN buyer_age BETWEEN 51 AND 60 THEN '51–60'
                    ELSE '61+' END AS age_range,
                COUNT(*) AS total
            ")
            ->groupBy('age_range')
            ->pluck('total','age_range')
            ->toArray();
        $allBuckets = ['0–10', '11–20', '21–30', '31–40', '41–50', '51–60', '61+'];
        $ageDistribution = [];
        foreach($allBuckets as $bucket) {
            $ageDistribution[$bucket] = $raw[$bucket] ?? 0;
        }

        return view('dashboard.index', compact(
            'filter',
            'totalSales',
            'totalOrders',
            'totalQuantity',
            'totalShippingCharges',
            'salesDelta',
            'ordersDelta',
            'ordersByProduct',
            'genderDistribution',
            'ordersByDate',
            'ordersByLocation',
            'ageDistribution',
        ));
    }
}
