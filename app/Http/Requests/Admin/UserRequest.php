<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . ($userId ? $userId->id ?? $userId : '')],
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,name'],
        ];
    }
}
