<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Validation\ValidationException;

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

    public function store(ProductStoreRequest $request)
    {
        try {
            $product = $this->product->storeProduct($request->validated());
            return SuccessResource::make([
                'message' => 'Product created successfully.',
                'data' => new ProductResource($product),
            ])->response()->setStatusCode(201);
        } catch (ValidationException $e) {
            $e = [
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => ['details' => $e->getMessage()],
            ];
            return ErrorResource::make($e)
                ->response()
                ->setStatusCode(422);
        } catch (\Exception $e) {
            $error = [
                'success' => false,
                'message' => 'an unexpected error occurred.',
                'errors' => ['details' => $e->getMessage()]
            ];
            return ErrorResource::make($error)
                ->response()
                ->setStatusCode(500);
        }
    }


    public function show($id)
    {
        try {
            $product = $this->product->getOneProduct($id);
            return ProductResource::make($product);
        } catch (ModelNotFoundException $th) {
            $th = [
                'success' => false,
                'message' => 'product not found with the given ID.',
                'errors' => ['details' => $th->getMessage()],
            ];
            return ErrorResource::make($th)
                ->response()
                ->setStatusCode(404);
        }
    }

    public function update(ProductUpdateRequest $request, $id)
    {
        try {
            $product = $this->product->updateProduct($request->validated(), $id);

            return SuccessResource::make([
                'message' => 'Product updated successfully.',
                'data' => ProductResource::make($product),
            ])->response()->setStatusCode(200);
        } catch (BadRequestException $th) {
            return ErrorResource::make([
                'success' => false,
                'message' => 'Bad request error: unable to process the product.',
                'errors' => ['details' => $th->getMessage()],
            ])->response()->setStatusCode(400);
        } catch (ModelNotFoundException $th) {
            return ErrorResource::make([
                'success' => false,
                'message' => 'Product not found with the given ID.',
                'errors' => ['details' => $th->getMessage()],
            ])->response()->setStatusCode(404);
        } catch (\Exception $e) {
            return ErrorResource::make([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => ['details' => $e->getMessage()],
            ])->response()->setStatusCode(500);
        } catch (ValidationException $e) {
            return ErrorResource::make([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => ['details' => $e->getMessage()],
            ])->response()->setStatusCode(422);
        }
    }


    public function destroy($id)
    {
        try {
            $product = $this->product->destroyProduct($id);

            return SuccessResource::make([
                'message' => 'The resource has been successfully eliminated.',
                'data' => ProductResource::make($product)
            ])->response()->setStatusCode(200);
        } catch (BadRequestException $th) {
            return ErrorResource::make([
                'success' => false,
                'message' => 'Bad Request',
                'errors' => ['details' => $th->getMessage()],
            ])->response()->setStatusCode(400);
        } catch (ModelNotFoundException $th) {
            return ErrorResource::make([
                'success' => false,
                'message' => 'Product not found.',
            ])->response()->setStatusCode(404);
        }
    }
}
