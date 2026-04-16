<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RolesEnum;

class UpdateAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(RolesEnum::Admin->value) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $targetUser = $this->route('user');

        return [
            'name'      => 'sometimes|string|min:2|max:50',
            'email'     => 'sometimes|email|max:255|unique:users,email,' . ($targetUser?->id),
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'role' => 'sometimes|string',
            'is_active' => 'sometimes|boolean'
        ];
    }

}
