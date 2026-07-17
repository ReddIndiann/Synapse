<?php

namespace App\Http\Requests\Distribution;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'media_asset_id' => ['required', 'exists:media_assets,id'],
            'distribution_channel_ids' => ['required', 'array', 'min:1'],
            'distribution_channel_ids.*' => ['integer', 'exists:distribution_channels,id'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
            'record_cost' => ['nullable', 'boolean'],
            'estimated_cost_per_channel' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'platform_options' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'record_cost' => $this->boolean('record_cost'),
            'distribution_channel_ids' => array_values(array_unique(array_map('intval', (array) $this->input('distribution_channel_ids', [])))),
        ]);
    }
}
