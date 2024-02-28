<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getProduct(Request $request)
    {
        $param = $request->input('param');
        if ($param) {
            $url = "https://dummyjson.com/products/search?q=$param";
        } else {
            $url = "https://dummyjson.com/products/search?q=iPhone";
        }

        $data = file_get_contents($url);
        $jsonProducts = json_decode($data, true);

        foreach ($jsonProducts['products'] as $productData) {
            $products[] = $productData;
        }
        foreach ($products as $product) {
            $newProduct['title']= $product['title'];
            $newProduct['description'] = $product['description'];
            $newProduct['price'] = $product['price'];
            $createdProduct = Product::firstOrCreate($newProduct);

            foreach ($product['images'] as $image) {
                if ($image) {
                    $dataImage['url'] = $image;
                    $dataImage['product_id'] = $createdProduct->id;
                    Image::firstOrCreate($dataImage);
                }
            }
        }

        $products = Product::query()
        ->where('title', 'LIKE', '%' . $param . '%')
        ->get();

        return ProductResource::collection($products);
    }

    public function postEntity(Request $request)
    {
        $type = $request->input('type');

        if ($type == 'product') {

            $data = $request->validate([
                'title' => 'required | string | unique:products',
                'description' => 'nullable | string',
                'price' => 'required | integer',
            ], [
                    'title.unique' => 'The title has already been taken',
                    'title.required' => 'The title is missing',
                    'price.required' => 'The price is missing',
                ]
            );

            return ProductResource::make(Product::create($data));
        } else {
            return response()->json(['status' => 'type of product not found']);
        }


    }
}
