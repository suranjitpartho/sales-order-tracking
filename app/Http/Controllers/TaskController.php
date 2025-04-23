<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\StatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

Class TaskController extends Controller
{
    // Index
    public function index()
    {
        $tasks = Task::latest()->get();
        return view('tasks.index', compact('tasks'));
    }


    // Create
    public function create()
    {
        return view('tasks.create');
    }


    // Store
    public function store(Request $request)
    {

        // Validate Form Input
        $validated = $request->validate([
            'order_date' => 'required|date',
            'product_id' => 'required|string|max:6',
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

        Task::create($validated);
        return redirect()->route('tasks.index')->with('success', 'Order added successfully!');
    }

    // Store Status
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


    // Show Task
    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }
    
    // Show StatusLog
    public function showStatus(Task $task)
    {
        return view('tasks.order-status', compact('task'));
    }




    // Edit
    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }
    

    // Update
    public function update(Request $request, Task $task)
    {
        // Validate Form Input
        $validated = $request->validate([
            'order_date' => 'required|date',
            'product_id' => 'required|string|max:6',
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
            
        $task->update($validated);
        return redirect()->route('tasks.index')->with('success', 'Order updated successfully!');
    }



    // Remove
    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Order deleted successfully!');
    }



    // Dashboard
    public function dashboard()
    {
        $totalSales = Task::sum('total_sales');
        $totalOrders = Task::count();

        $ordersByProduct = Task::select('product_id', DB::raw('count(*) as total'))
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $genderDistribution = Task::select('buyer_gender', DB::raw('count(*) as total'))
            ->groupBy('buyer_gender')
            ->pluck('total', 'buyer_gender');

        $ordersByDate = Task::selectRaw("DATE_FORMAT(order_date, '%b %Y') as month_label, MIN(order_date) as date_key, COUNT(*) as total")
            ->groupBy('month_label')
            ->orderBy('date_key')
            ->pluck('total', 'month_label');
        
        $ordersByLocation = Task::select('order_location', DB::raw('count(*) as total'))
            ->groupBy('order_location')
            ->pluck('total', 'order_location');

        return view('dashboard.index', compact(
            'totalSales',
            'totalOrders',
            'ordersByProduct',
            'genderDistribution',
            'ordersByDate',
            'ordersByLocation'
        ));
    }

}
