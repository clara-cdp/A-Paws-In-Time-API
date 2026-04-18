<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => 'required|string|min:3|max:45',
        ];
    }

    // This replaces the manual count() check in your controller
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->user()->games()->count() >= 3) {
                $validator->errors()->add('game', 'You have reached the maximum number of save slots (3).');
                }
            });
        }
    }