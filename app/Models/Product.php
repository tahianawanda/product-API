<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
}
