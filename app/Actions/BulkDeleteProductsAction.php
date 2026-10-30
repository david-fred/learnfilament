<?php

namespace App\Actions;

use App\Models\Product;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BulkDeleteProductsAction
{
    public function handle(Collection $products): void
    {
        if ($products->contains(fn (Product $product) => $product->orders()->exists())) {
            throw new DomainException('One or more selected products have existing orders.');
        }

        DB::transaction(function () use ($products) {
            foreach ($products as $product) {
                $product->delete();
            }
        });
    }
}