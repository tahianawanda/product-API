<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ErrorResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductController extends Controller
{
    private Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function index()
    {
        $products = $this->product->getAllProducts();
        
        return ProductCollection::make($products);
    }

    public function store(ProductStoreRequest $request): JsonResource
    {
        $product = Product::create($request->validated());

        return new ProductResource($product);
    }

    public function show($id)
    {
        try{
            $product = $this->product->getOneProduct($id);

            return ProductResource::make($product);
        } catch(ModelNotFoundException $th){
            
            $error = [
                'error' => 'Not Found',
                'message' => 'Product not found with the given ID.',
                'code' => 404,
            ];

            return ErrorResource::make($error)->response()->setStatusCode(404);
        }  
    }

    public function update(ProductUpdateRequest $request, Product $product): JsonResource
    {
        $product->update($request->validated());

        return new ProductResource($product);
    }

    public function destroy(Request $request, Product $product): JsonResource
    {
        $product->delete();

        return new JsonResource(['message' => 'Product deleted successfully']);
    }
}
