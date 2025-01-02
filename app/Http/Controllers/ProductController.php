<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Store a new product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($request->all());

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }

    /**
     * Update product by id
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if(!isset($product)){
            return response()->json([
                'message' => 'Product not found',
            ]);
        }

        $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'stock' => 'integer|min:0',
        ]);

        $product->update($request->all());

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }

    /**
     * Get all products
     */
    public function index()
    {
        $products = Product::whereNull('deleted_at')->get();

        return response()->json($products);
    }

    /**
     * Show product by id
     */
    public function show($id)
    {
        $product = Product::find($id);

        if(isset($product)){
            return response()->json($product);
        }else{
            return response()->json([
                'message' => 'Product not found',
            ]);
        }
    }

    /**
     * Destroy product by id (soft delete)
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Add product to Cart
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Get cart data from session
        $cart = session()->get('cart', []);

        // Check if product_id exist in cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] == $request->product_id) {
                $item['quantity'] += $request->quantity;
                $found = true;
                break;
            }
        }

        // If It doesn´t exist, add to cart
        if (!$found) {
            $cart[] = [
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ];
        }

        session(['cart' => $cart]);

        return response()->json([
            'message' => 'Product added to cart',
            'cart' => $cart,
        ]);
    }

}
