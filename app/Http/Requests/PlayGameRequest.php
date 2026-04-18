<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\Verb;

class PlayGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        $game = $this->route('game');
        return $game && $game->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'verb'      => ['required', 'string', Rule::enum(Verb::class)],
            'target_id' => 'required|integer',
            'item_id'   => 'nullable|integer',
        ];
    }
}