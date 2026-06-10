<?php

namespace App\Http\Requests\Accounting;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:' . implode(',', Transaction::types())],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'occurred_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:Cash,Bank,Mobile Money'],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
        ];
    }
}
