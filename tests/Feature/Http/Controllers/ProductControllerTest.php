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
        Product::factory()->count(2)->create();

        // Realizar una solicitud GET para obtener los productos
        $response = $this->getJson(route('products.index'));

        // Verificar el código de estado
        $response->assertStatus(200);

        // Verificar la estructura de la respuesta
        $response->assertJsonStructure([
            'data' => [
                '*' => [ // Asterisco para verificar la estructura de cada elemento del array
                    'id',
                    'type',
                    'attributes' => [
                        'name',
                        'price',
                        'description',
                        'stock',
                    ],
                    'links' => [
                        'self',
                    ],
                ],
            ],
            'links' => [
                'self',
            ],
            'meta' => [
                'articles_count',
            ],
        ]);
    }


    public function testNewProductSuccessfully()
    {
        // Datos válidos 
        $data = [
            'name' => 'New Product',
            'price' => 100.0,
        ];
        // Realizar la solicitud 
        $response = $this->postJson(route('products.store'), $data);
        // Verificar 
        $response->assertStatus(201);
        // Extraer los datos
        $createdProduct = $response->json('data');
        // Verificar si coinciden
        $this->assertEquals($data['name'], $createdProduct['attributes']['name']);
        $this->assertEquals($data['price'], $createdProduct['attributes']['price']);
        // Verificar que el producto existe
        $this->assertDatabaseHas('products', [
            'id' => $createdProduct['id'],
            'name' => $data['name'],
            'price' => $data['price'],
        ]);
    }

    // Test para store con error de validación
    public function testWhenValidationFails()
    {
        // Datos inválidos (sin nombre)
        $data = [
            'price' => 100.0,
        ];

        $response = $this->postJson(route('products.store'), $data);

        $response->assertStatus(422);

        $response->assertJson([
            'message' => 'The name field is required.',
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

        $response = $this->getJson(route('products.show', $nonExistentProductId));

        $response->assertStatus(404);

        $response->assertJson([
            'data' => [
                'success' => false,
                'message' => 'Resource not found.',
                'errors' => [
                    'details' => 'No additional error details are available.',
                ],
            ],
        ]);
    }

    // Test para update (Actualizar un producto existente)
    public function testUpdatesAnExistingProductSuccessfully()
    {
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

        // Refrescar el modelo para obtener los valores actualizados
        $product->refresh();

        // Verificar que los datos actualizados estén presentes en la respuesta
        $response->assertJson([
            'message' => 'Product updated successfully.',
            'data' => [
                'id' => $product->getRouteKey(),
                'type' => 'products',
                'attributes' => [
                    'name' => $product->name,
                    'price' => $product->price,
                    'description' => $product->description,
                    'stock' => $product->stock,
                ],
                'links' => [
                    'self' => route('products.show', $product),
                ],
            ],
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
            'data' => [
                'success' => false,
                'message' => 'Resource not found.',
                'errors' => [
                    'details' => 'No additional error details are available.',
                ],
            ]
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
            'message' => 'The resource has been successfully eliminated.',
            'data' => [
                'id' => $product->getRouteKey(),
                'type' => 'products',
                'attributes' => [
                    'name' => $product->name,
                    'price' => $product->price,
                    'description' => $product->description,
                    'stock' => $product->stock,
                ],
                'links' => [
                    'self' => route('products.show', $product),
                ],
            ],
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
            'data' => [
                'success' => false,
                'message' => 'Resource not found.',
                'errors' => [
                    'details' => 'No additional error details are available.',
                ],
            ]
        ]);
    }
}
