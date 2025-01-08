<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test para index (Obtener lista de productos)
    public function testReturnsAListOfProducts()
    {
        // Crear productos de prueba
        Product::factory()->count(5)->create();

        // Realizar una solicitud GET para obtener los productos
        $response = $this->getJson(route('products.index'));

        // Verificar el código de estado
        $response->assertStatus(200);

        // Verificar la estructura de la respuesta
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'created_at', 'updated_at'],
            ],
        ]);
    }

    // Test para store (Crear un nuevo producto)
    public function testCreatesANewProductSuccessfully()
    {
        // Datos válidos para la creación de un producto
        $data = [
            'name' => 'New Product',
            'price' => 100.0,
        ];

        // Realizar la solicitud POST para crear el producto
        $response = $this->postJson(route('products.store'), $data);

        // Verificar que la respuesta sea un código 201 (creación exitosa)
        $response->assertStatus(201);

        // Verificar que los datos del producto estén presentes en la respuesta
        $response->assertJsonFragment([
            'name' => 'New Product',
            'price' => 100.0,
        ]);
    }

    // Test para store con error de validación
    public function testReturnsAnErrorWhenValidationFailsDuringProductCreation()
    {
        // Datos inválidos (sin nombre)
        $data = [
            'price' => 100.0,
        ];

        // Realizar la solicitud POST para crear el producto
        $response = $this->postJson(route('products.store'), $data);

        // Verificar que la respuesta sea un código 422 (error de validación)
        $response->assertStatus(422);

        // Verificar que la respuesta contenga los errores de validación
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred.',
            'errors' => [
                'name' => ['The name field is required.'],
            ],
        ]);
    }

    // Test para show (Obtener un producto específico)
    public function testReturnsASpecificProduct()
    {
        // Crear un producto de prueba
        $product = Product::factory()->create();

        // Realizar la solicitud GET para obtener el producto
        $response = $this->getJson(route('products.show', $product->id));

        // Verificar que la respuesta sea un código 200
        $response->assertStatus(200);

        // Verificar que los datos del producto estén presentes en la respuesta
        $response->assertJsonFragment([
            'name' => $product->name,
            'price' => $product->price,
        ]);
    }

    // Test para show con producto no encontrado
    public function testReturnsAnErrorWhenTheProductIsNotFound()
    {
        // ID de un producto que no existe
        $nonExistentProductId = 999;

        // Realizar la solicitud GET para obtener el producto
        $response = $this->getJson(route('products.show', $nonExistentProductId));

        // Verificar que la respuesta sea un error con código 404
        $response->assertStatus(404);

        // Verificar que la respuesta contenga el formato de error adecuado
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred.',
            'errors' => [
                'details' => 'Product not found.',
            ],
        ]);
    }

    // Test para update (Actualizar un producto existente)
    public function testUpdatesAnExistingProductSuccessfully()
    {
        // Crear un producto de prueba
        $product = Product::factory()->create();

        // Datos de actualización para el producto
        $data = [
            'name' => 'Updated Product',
            'price' => 150.0,
        ];

        // Realizar la solicitud PUT para actualizar el producto
        $response = $this->putJson(route('products.update', $product->id), $data);

        // Verificar que la respuesta sea un código 200
        $response->assertStatus(200);

        // Verificar que los datos actualizados estén presentes en la respuesta
        $response->assertJsonFragment([
            'name' => 'Updated Product',
            'price' => 150.0,
        ]);
    }

    // Test para update con producto no encontrado
    public function testReturnsAnErrorWhenTheProductToUpdateIsNotFound()
    {
        // ID de un producto que no existe
        $nonExistentProductId = 999;

        // Datos de actualización
        $data = [
            'name' => 'Updated Product',
            'price' => 150.0,
        ];

        // Realizar la solicitud PUT para actualizar el producto
        $response = $this->putJson(route('products.update', $nonExistentProductId), $data);

        // Verificar que la respuesta sea un error con código 404
        $response->assertStatus(404);

        // Verificar que la respuesta contenga el formato de error adecuado
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred.',
            'errors' => [
                'details' => 'Product not found.',
            ],
        ]);
    }

    // Test para destroy (Eliminar un producto)
    public function testDeletesAProductSuccessfully()
    {
        // Crear un producto de prueba
        $product = Product::factory()->create();

        // Realizar la solicitud DELETE para eliminar el producto
        $response = $this->deleteJson(route('products.destroy', $product->id));

        // Verificar que la respuesta sea un código 200
        $response->assertStatus(200);

        // Verificar que el producto ha sido eliminado de la base de datos
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);

        // Verificar el mensaje de éxito
        $response->assertJson([
            'success' => true,
            'message' => 'The resource has been successfully eliminated.',
        ]);
    }

    // Test para destroy con producto no encontrado
    public function testReturnsAnErrorWhenTheProductToDeleteIsNotFound()
    {
        // ID de un producto que no existe
        $nonExistentProductId = 999;

        // Realizar la solicitud DELETE para eliminar el producto
        $response = $this->deleteJson(route('products.destroy', $nonExistentProductId));

        // Verificar que la respuesta sea un error con código 404
        $response->assertStatus(404);

        // Verificar que la respuesta contenga el formato de error adecuado
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred.',
            'errors' => [
                'details' => 'Product not found.',
            ],
        ]);
    }
}
