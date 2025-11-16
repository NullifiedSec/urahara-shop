<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * OrderController
 * 
 * Handles API requests for order management.
 * All operations require authentication.
 */
class OrderController extends Controller
{
    /**
     * Get the authenticated user's orders.
     */
    public function index(): JsonResponse
    {
        $orders = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($orders);
    }

    /**
     * Create a new order from cart items.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_address' => 'required|array',
            'billing_address' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Get cart items
        $cartItems = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        // Validate stock availability
        foreach ($cartItems as $cartItem) {
            if ($cartItem->quantity > $cartItem->product->quantity) {
                return response()->json([
                    'message' => "Insufficient stock for {$cartItem->product->name}",
                ], 400);
            }
        }

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = $cartItems->sum('subtotal');
            $tax = 0; // Calculate tax based on your business logic
            $shipping = 0; // Calculate shipping based on your business logic
            $total = $subtotal + $tax + $shipping;

            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => Order::generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'total' => $total,
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'] ?? $validated['shipping_address'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items and update product quantities
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_sku' => $cartItem->product->sku,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'subtotal' => $cartItem->subtotal,
                ]);

                // Update product quantity
                $cartItem->product->decrement('quantity', $cartItem->quantity);
            }

            // Clear cart
            CartItem::where('user_id', Auth::id())->delete();

            DB::commit();

            $order->load('items.product');

            return response()->json($order, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create order'], 500);
        }
    }

    /**
     * Get order details.
     */
    public function show(string $id): JsonResponse
    {
        $order = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json($order);
    }

    /**
     * Update order status.
     * 
     * Note: Should be protected by admin middleware in production.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update($validated);

        return response()->json($order);
    }

    /**
     * Cancel an order.
     */
    public function destroy(string $id): JsonResponse
    {
        $order = Order::where('user_id', Auth::id())
            ->findOrFail($id);

        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_PROCESSING])) {
            return response()->json(['message' => 'Order cannot be cancelled'], 400);
        }

        DB::beginTransaction();

        try {
            // Restore product quantities
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('quantity', $item->quantity);
                }
            }

            $order->update(['status' => Order::STATUS_CANCELLED]);

            DB::commit();

            return response()->json(['message' => 'Order cancelled successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to cancel order'], 500);
        }
    }
}
