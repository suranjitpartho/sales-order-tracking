<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\StatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


Class TaskController extends Controller
{
    // INDEX - Show all orders
    public function index()
    {
        $tasks = Task::latest()->get();
        return view('tasks.index', compact('tasks'));
    }


    // CREATE - Show form to create a new order
    public function create()
    {
        return view('tasks.create');
    }


    // STORE - This method handles the form submission for creating a new order
    public function store(Request $request)
    {

        // Validate Form Input
        $validated = $request->validate([
            'order_date' => 'required|date',
            // 'product_id' => 'required|string|size:6',
            'product_id' => 'required|in:SX9001,SX9002,SX9003,SX9004,SX9005,SX9006',
            'product_category' => 'required|in:clothing,ornaments,other',
            'buyer_gender' => 'required|in:male,female',
            'buyer_age' => 'required|integer|min:0',
            'order_location' => 'required|string',
            'international_shipping' => 'sometimes|boolean',
            'sales_price' => 'required|numeric|min:0',
            'shipping_charges' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        // Handle Checkbox value
        $validated['international_shipping'] = $request->has('international_shipping');

        // Handle Shipping Charges
        $validated['shipping_charges'] = $validated['international_shipping']
            ? ($validated['shipping_charges'] ?? 0)
            : 0;

        // Calculated Fields
        $validated['sales_per_unit'] = $validated['sales_price'] + $validated['shipping_charges'];
        $validated['total_sales'] = $validated['sales_per_unit'] * $validated['quantity'];

        // Create a new order
        $task = Task::create($validated);

        // Immediately log initial status as "Pending"
        $task->statuslog()->create([
            'status' => 'Pending',
            'changed_at' => now(),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Order added successfully!');
    }

    // STORE ORDER STATUS - This method handles the form submission for updating the order status
    public function storeStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Shipped,Delivered,Cancelled'
        ]);

        StatusLog::create([
            'task_id' => $task->id,
            'status' => $validated['status'],
            'changed_at' => now(),
        ]);
        return redirect()->route('tasks.index')->with('success', 'Order status updated successfully.');
    }


    // SHOW - Show a single order
    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }
    
    // SHOW STATUS LOGS - Show order status logs
    public function showStatus(Task $task)
    {
        // $statusLogs = $task->statuslog()->orderBy('changed_at', 'desc')->get();
        return view('tasks.order-status', compact('task'));
    }


    // EDIT - Show form to edit an order
    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }
    

    // UPDATE - This method handles the form submission for updating an order
    public function update(Request $request, Task $task)
    {
        // Validate Form Input
        $validated = $request->validate([
            'order_date' => 'required|date',
            'product_id' => 'required|in:SX9001,SX9002,SX9003,SX9004,SX9005,SX9006',
            'product_category' => 'required|in:clothing,ornaments,other',
            'buyer_gender' => 'required|in:male,female',
            'buyer_age' => 'required|integer|min:0',
            'order_location' => 'required|string',
            'international_shipping' => 'sometimes|boolean',
            'sales_price' => 'required|numeric|min:0',
            'shipping_charges' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ], [
            'product_id.size' => 'Product ID must be exactly 6 characters.',
        ]);

        // Handle Checkbox value
        $validated['international_shipping'] = $request->has('international_shipping');

        // Handle Shipping Charges
        $validated['shipping_charges'] = $validated['international_shipping']
        ? ($validated['shipping_charges'] ?? 0)
        : 0;

        // Calculated Fields
        $validated['sales_per_unit'] = $validated['sales_price'] + $validated['shipping_charges'];
        $validated['total_sales'] = $validated['sales_per_unit'] * $validated['quantity'];
            
        $task->update($validated);
        return redirect()->route('tasks.index')->with('success', 'Order updated successfully!');
    }


    // DESTROY - Delete an order
    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Order deleted successfully!');
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
        $query = Task::query();
        if ($start && $end) {
            $query->whereBetween('order_date', [$start->toDateString(), $end->toDateString()]);
        }

        // Use the query for all aggregations
        $totalSales = (clone $query)->sum('total_sales');
        $totalOrders = (clone $query)->count();
        $totalQuantity = (clone $query)->sum('quantity');
        $totalShippingCharges = (clone $query)->sum('shipping_charges');

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
                $prevSales = Task::whereBetween('order_date', [
                                $prevStart->toDateString(),
                                $prevEnd->toDateString()
                            ])->sum('total_sales');
                if ($prevSales > 0) {
                    $salesDelta = ($totalSales - $prevSales) / $prevSales * 100;
                }
            }
        
        $ordersDelta = null;
            if ($filter === 'this_month') {
                $prevStart = $start->copy()->subMonth()->startOfMonth();
                $prevEnd   = $start->copy()->subMonth()->endOfMonth();
                $prevOrders = Task::whereBetween('order_date', [
                                $prevStart->toDateString(),
                                $prevEnd->toDateString()
                            ])->count();
                if ($prevOrders > 0) {
                    $ordersDelta = ($totalOrders - $prevOrders) / $prevOrders * 100;
                }
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
            'ordersByLocation'
        ));
    }

}
