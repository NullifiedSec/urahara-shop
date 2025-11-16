<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * CartController
 * 
 * Handles API requests for shopping cart management.
 * All operations require authentication.
 */
class CartController extends Controller
{
    /**
     * Get the authenticated user's cart.
     */
    public function index(): JsonResponse
    {
        $cartItems = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        // Calculate totals
        $subtotal = $cartItems->sum('subtotal');
        $total = $subtotal; // Add tax/shipping calculation here if needed

        return response()->json([
            'items' => $cartItems,
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }

    /**
     * Add an item to the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Check if product is in stock
        $product = Product::findOrFail($validated['product_id']);
        if (!$product->isInStock()) {
            return response()->json(['message' => 'Product is out of stock'], 400);
        }

        // Check if item already exists in cart
        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cartItem) {
            // Update quantity
            $cartItem->quantity += $validated['quantity'];
            $cartItem->save();
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        $cartItem->load('product');

        return response()->json($cartItem, 201);
    }

    /**
     * Update a cart item.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $cartItem = CartItem::where('user_id', Auth::id())
            ->findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Check stock availability
        if ($validated['quantity'] > $cartItem->product->quantity) {
            return response()->json(['message' => 'Insufficient stock'], 400);
        }

        $cartItem->update($validated);
        $cartItem->load('product');

        return response()->json($cartItem);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(string $id): JsonResponse
    {
        $cartItem = CartItem::where('user_id', Auth::id())
            ->findOrFail($id);

        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart'], 200);
    }

    /**
     * Clear the entire cart.
     */
    public function clear(): JsonResponse
    {
        CartItem::where('user_id', Auth::id())->delete();

        return response()->json(['message' => 'Cart cleared'], 200);
    }
}
