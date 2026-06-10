<?php

namespace App\Http\Requests\Distribution;

use Illuminate\Foundation\Http\FormRequest;

class PublishJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'media_asset_id' => ['required', 'exists:media_assets,id'],
            'distribution_channel_id' => ['required', 'exists:distribution_channels,id'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }
}
