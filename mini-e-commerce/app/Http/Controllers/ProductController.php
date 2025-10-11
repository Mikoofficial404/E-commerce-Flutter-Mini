<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $product = Product::all();

        if ($product->isEmpty()) {
            return response()->json([
                'messages' => 'Data Product Not Found',
                'success' => false,
            ], 422);
        }

        $product = $product->map(function ($product) {
            $product->photo_product = asset('storage/products/' . $product->photo_product);

            return $product;
        });

        return response()->json([
            'messages' => 'Get All Data Products',
            'data' => $product,
            'sucess' => true,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'photo_product' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'messages' => $validator->errors(),
                'sucess' => false,
            ], 422);
        }
        $image = $request->file('photo_product');
        $image->store('products', 'public');
        $product = Product::create([
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'photo_product' => $image->hashName(),
        ]);

        return response()->json([
            'messages' => 'Product Created',
            'success' => true,
            'data' => $product,
        ], 201);
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'messages' => 'Product Not Found',
                'sucess' => false,
            ], 400);
        }

        $product->photo_product = asset('storage/products/' . $product->photo_product);

        return response()->json([
            'messages' => 'Get Product By Id',
            'sucess' => true,
            'data' => $product,
        ], 200);
    }

    public function edit(Request $request, $id)
    {
        $product = Product::find($id);
        if (! $product) {
            return response()->json([
                'messages' => 'Product Not Found',
                'sucess' => false,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'photo_product' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'success' => false,
            ], 422);
        }

        $data = [
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
        ];

        if ($request->hasFile('photo_product')) {
            $image = $request->file('photo_product');
            $image->store('products', 'public');
            if ($product->photo_product) {
                Storage::disk('public')->delete('products/' . $product->photo_product);
            }

            $data['photo_product'] = $image->hashName();
        }

        return response()->json([
            'messages' => 'Product Updated',
            'sucess' => true,
            'data' => $product->update($data),
        ], 200);
    }

    public function delete($id)
    {
        $product = Product::find($id);
        if (! $product) {
            return response()->json([
                'messages' => 'Product Not Found',
                'success' => false,
            ], 404);
        }
        if ($product->produc_photo) {
            Storage::disk('public')->delete('products/' . $product->photo_product);
        }
        $product->delete();

        return response()->json([
            'messages' => 'Product Delted',
            'sucess' => true,
            'data' => $product,
        ], 200);
    }
}
