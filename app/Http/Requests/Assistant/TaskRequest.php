<?php

namespace App\Http\Requests\Assistant;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:' . implode(',', Task::priorities())],
            'status' => ['required', 'in:' . implode(',', Task::statuses())],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
