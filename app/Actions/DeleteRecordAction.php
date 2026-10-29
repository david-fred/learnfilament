<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeleteRecordAction
{
    public function handle(Model $model): ?bool
    {
        return DB::transaction(fn () => $model->delete());
    }
}