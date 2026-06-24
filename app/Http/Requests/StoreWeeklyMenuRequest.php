<?php

namespace App\Http\Requests;

use App\Models\Material;
use App\Models\WeeklyMenu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWeeklyMenuRequest extends FormRequest
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
            'dow' => ['required', Rule::in(WeeklyMenu::DOWS)],
            'sort_order' => ['required', 'integer', 'min:1', 'max:99'],
            'material_id' => [
                'required_without:catalog_name',
                'nullable',
                Rule::exists('materials', 'id')->where('user_id', $this->user()->id),
            ],
            'catalog_name' => ['required_without:material_id', 'nullable', 'string', 'max:100'],
            'catalog_type' => [
                'required_with:catalog_name',
                'nullable',
                Rule::in(array_keys(Material::TYPES)),
            ],
            'sets' => ['required', 'integer', 'min:1', 'max:99'],
            'reps' => ['required', 'string', 'max:20', 'regex:/^\d+(-\d+)?$/'],
            'memo' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dow.required' => '曜日は必須です。',
            'sort_order.required' => '順番を入力してください。',
            'material_id.required_without' => '種目を選択してください。',
            'catalog_name.required_without' => '種目を選択してください。',
            'sets.required' => 'セット数を入力してください。',
            'reps.required' => 'レップを入力してください。',
            'reps.regex' => 'レップは「8」または「6-8」の形式で入力してください。',
        ];
    }
}
