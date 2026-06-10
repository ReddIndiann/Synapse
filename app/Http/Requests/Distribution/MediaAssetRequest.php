<?php

namespace App\Http\Requests\Distribution;

use App\Models\MediaAsset;
use Illuminate\Foundation\Http\FormRequest;

class MediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:' . implode(',', MediaAsset::statuses())],
        ];

        if ($this->isMethod('POST')) {
            $rules['file'] = [
                'required',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,mp3,wav,flac,pdf,doc,docx,zip',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'The file must be a valid media or document type (jpg, png, mp4, mp3, pdf, etc.).',
            'file.max' => 'The file size must not exceed 10MB.',
        ];
    }
}
