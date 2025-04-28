<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


// This controller handles the API requests for the Task model.
// It provides methods to retrieve, create, update, and delete tasks.

class TaskApiController extends Controller
{
    public function index() {
        return response()->json(Task::latest()->get());
    }

    public function show($id) {
        $task = Task::find($id);
        return $task
            ? response()->json($task)
            : response()->json(['error' => 'Not found'], 404);
    }

    /* CREATE ORDER - API
    This API method creates a new order in the database.
    It accepts a POST request with the order data in the request body.
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_date'             => 'required|date',
            'product_id'             => 'required|string|max:6',
            'product_category'       => ['required', Rule::in(['clothing','ornaments','other'])],
            'buyer_gender'           => ['required', Rule::in(['male','female'])],
            'buyer_age'              => 'required|integer|min:0',
            'order_location'         => 'required|string',
            'international_shipping' => 'sometimes|boolean',
            'sales_price'            => 'required|numeric|min:0',
            'shipping_charges'       => 'nullable|numeric|min:0',
            'quantity'               => 'required|integer|min:1',
            'remarks'                => 'nullable|string',
        ]);

        $data['international_shipping'] = $request->has('international_shipping');
        $data['shipping_charges'] = $data['international_shipping']
            ? ($data['shipping_charges'] ?? 0)
            : 0;
        $data['sales_per_unit'] = $data['sales_price'] + $data['shipping_charges'];
        $data['total_sales']     = $data['sales_per_unit'] * $data['quantity'];

        $task = Task::create($data);

        return response()->json($task, 201);
    }



    /* UPDATE ORDER - API
    This API method updates an existing order in the database.
    It accepts a PUT or PATCH request with the order ID in the URL and the updated order data in the request body.
    */

    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        if (! $task) {
            return response()->json(['error'=>'Task not found'], 404);
        }

        $data = $request->validate([
            'order_date'             => 'sometimes|date',
            'product_id'             => 'sometimes|string|max:6',
            'product_category'       => ['sometimes', Rule::in(['clothing','ornaments','other'])],
            'buyer_gender'           => ['sometimes', Rule::in(['male','female'])],
            'buyer_age'              => 'sometimes|integer|min:0',
            'order_location'         => 'sometimes|string',
            'international_shipping' => 'sometimes|boolean',
            'sales_price'            => 'sometimes|numeric|min:0',
            'shipping_charges'       => 'nullable|numeric|min:0',
            'quantity'               => 'sometimes|integer|min:1',
            'remarks'                => 'nullable|string',
        ]);

        if (array_key_exists('international_shipping', $data)) {
            $data['international_shipping'] = $request->has('international_shipping');
        }

        if (isset($data['sales_price']) || isset($data['shipping_charges']) || isset($data['quantity'])) {
            $price = $data['sales_price'] ?? $task->sales_price;
            $ship  = $data['shipping_charges'] ?? $task->shipping_charges;
            $qty   = $data['quantity'] ?? $task->quantity;

            $data['sales_per_unit'] = $price + $ship;
            $data['total_sales']    = $data['sales_per_unit'] * $qty;
        }

        $task->update($data);

        return response()->json($task);
    }

    
    /**
     * DELETE ORDER - API
     * This API method deletes an existing order from the database.
     * It accepts a DELETE request with the order ID in the URL.
    */

    public function destroy($id)
    {
        $task = Task::find($id);
        if (! $task) {
            return response()->json(['error'=>'Task not found'], 404);
        }
        $task->delete();
        return response()->noContent();
    }
}