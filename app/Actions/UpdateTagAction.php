<?php

namespace App\Actions;

use App\Models\Tag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateTagAction
{
    public function handle(Tag $tag, array $attributes): Tag
    {
        $validated = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:50', Rule::unique('tags')->ignore($tag->id)],
        ])->validate();

        $tag->update($validated);

        return $tag;
    }
}