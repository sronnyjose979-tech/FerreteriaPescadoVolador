<?php
namespace App\Services;// esto es para indicar que esta clase pertenece a la carpeta Services
use App\Models\Product; // esto es para indicar que esta clase pertenece a la carpeta Models
class ProductService// esta clase se encarga de manejar la logica de negocio de los productos
{
    public function createProduct(array $validated)// aqui se va crear un producto, se recibe un array de datos validados
    {
        $product = Product::create($validated);
        return $product;
    }
}