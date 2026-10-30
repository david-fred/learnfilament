<?php

namespace App\Actions;

use App\Models\Tag;

use Illuminate\Support\Facades\Validator;

class CreateTagAction
{
    public function handle(array $attributes): Tag
    {
        $validated = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:50', 'unique:tags'],
        ])->validate();

        return Tag::create($validated);
    }
}