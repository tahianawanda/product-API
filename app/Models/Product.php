<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'price',
        'description',
        'stock',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'price' => 'decimal:2',
    ];

    public function getAllProducts()
    {
        return $this->all();
    }

    public function getOneProduct($id)
    {
        $product = $this->found($id);

        return $product;
    }

    private function found($id)
    {
        try {
            $product = $this->findOrFail($id);

            return $product;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException;
        }
    }

    public function storeProduct(array $data)
    {
        $product = new Product([
            'name' => $data['name'],
            'price' => $data['price'] ?? null,
            'description' => $data['description'] ?? null,
            'stock' => $data['stock'] ?? null,
        ]);

        $product->save();

        return $product;
    }

    public function updateProduct(array $data, $id)
    {
        $product = $this->found($id);

        $filteredData = array_filter($data, fn($value) => !is_null($value));

        $product->update($filteredData);

        return $product;
    }

    public function destroyProduct($id)
    {
        $product = $this->found($id);

        $product->delete();

        return $product;
    }
}
