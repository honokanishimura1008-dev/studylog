<?php

namespace App\Http\Requests;

use App\Models\Material;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Material::TYPES))],
            'cover_path' => [
                'nullable',
                'string',
                Rule::in(Material::coverPathsForType((string) $this->input('type'))),
            ],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'メニュー名は必須です。',
            'type.in' => '種別が不正です。',
            'cover_path.in' => '選択した種別では使えない画像です。',
        ];
    }
}
