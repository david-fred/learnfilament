<?php

namespace App\Actions;

use App\Models\Product;
use DomainException;

class DeleteProductAction
{
    public function handle(Product $product): void
    {
        if ($product->orders()->exists()) {
            throw new DomainException('This product has existing orders.');
        }

        $product->delete();
    }
}