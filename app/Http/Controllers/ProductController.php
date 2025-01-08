<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Validation\ValidationException;
use App\Traits\HandlesApiExceptions;

class ProductController extends Controller
{
    use HandlesApiExceptions;

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

    public function store(ProductStoreRequest $request)
    {
        Log::info('Incoming request data: ', $request->all());

        Log::info('Validated data: ', $request->validated());
        try {
            $product = $this->product->storeProduct($request->validated());
            return SuccessResource::make([
                'message' => 'Product created successfully.',
                'data' => $product,
            ])->response()->setStatusCode(201);
        } catch (\Throwable $e) {
            $exceptionData = $this->handleException($e);
            return ErrorResource::make($exceptionData['data'])
                ->response()
                ->setStatusCode($exceptionData['status_code']);
        }
    }


    public function show($id)
    {
        try {
            $product = $this->product->getOneProduct($id);
            return ProductResource::make($product);
        } catch (\Throwable $e) {
            $exceptionData = $this->handleException($e);
            return ErrorResource::make($exceptionData['data'])
                ->response()
                ->setStatusCode($exceptionData['status_code']);
        }
    }

    public function update(ProductUpdateRequest $request, $id)
    {
        try {
            $product = $this->product->updateProduct($request->validated(), $id);

            return SuccessResource::make([
                'message' => 'Product updated successfully.',
                'data' => $product,
            ])->response()->setStatusCode(200);
        } catch (\Throwable $e) {
            $exceptionData = $this->handleException($e);
            return ErrorResource::make($exceptionData['data'])
                ->response()
                ->setStatusCode($exceptionData['status_code']);
        }
    }


    public function destroy($id)
    {
        try {
            $product = $this->product->destroyProduct($id);

            return SuccessResource::make([
                'message' => 'The resource has been successfully eliminated.',
                'data' => $product,
            ])->response()->setStatusCode(200);
        } catch (\Throwable $e) {
            $exceptionData = $this->handleException($e);
            return ErrorResource::make($exceptionData['data'])
                ->response()
                ->setStatusCode($exceptionData['status_code']);
        }
    }
}
