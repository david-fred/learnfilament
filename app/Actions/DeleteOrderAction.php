<?php

namespace App\Actions;

use App\Models\Order;

class DeleteOrderAction
{
    public function handle(Order $order): void
    {
        $order->delete();
    }
}