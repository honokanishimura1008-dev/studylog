<?php

namespace App\Http\Requests;

use App\Models\MealLog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMealLogRequest extends FormRequest
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
        $rules = [
            'mode' => ['required', Rule::in(['master', 'free', 'quick'])],
            'eaten_on' => ['required', 'date', 'before_or_equal:today'],
            'meal_type' => ['required', Rule::in(array_keys(MealLog::MEAL_TYPES))],
        ];

        $mode = $this->input('mode');
        $isUpdate = $this->route('mealLog') !== null; // 編集は1件単位

        if ($mode === 'master') {
            if ($isUpdate) {
                // 編集対象の1件 + 追加行（任意）を受け取る
                $rules['food_id'] = ['required', Rule::exists('foods', 'id')];
                $rules['amount_g'] = ['required', 'integer', 'min:1', 'max:2000'];
                $rules['items'] = ['nullable', 'array', 'max:20'];
            } else {
                // 新規登録は複数行をまとめて受け取る
                $rules['items'] = ['required', 'array', 'min:1', 'max:20'];
            }

            $rules['items.*.food_id'] = ['required', Rule::exists('foods', 'id')];
            $rules['items.*.amount_g'] = ['required', 'integer', 'min:1', 'max:2000'];
        } elseif ($mode === 'free') {
            if ($isUpdate) {
                // 編集対象の1件 + 追加行（任意）を受け取る
                $rules['food_name_free'] = ['required', 'string', 'max:100'];
                $rules['kcal'] = ['nullable', 'integer', 'min:0', 'max:9999'];
                $rules['protein'] = ['nullable', 'numeric', 'min:0', 'max:999'];
                $rules['fat'] = ['nullable', 'numeric', 'min:0', 'max:999'];
                $rules['carbs'] = ['nullable', 'numeric', 'min:0', 'max:999'];
                $rules['free_items'] = ['nullable', 'array', 'max:20'];
            } else {
                // 自由記述も複数行をまとめて受け取る（栄養値は任意）
                $rules['free_items'] = ['required', 'array', 'min:1', 'max:20'];
            }

            $rules['free_items.*.food_name_free'] = ['required', 'string', 'max:100'];
            $rules['free_items.*.kcal'] = ['nullable', 'integer', 'min:0', 'max:9999'];
            $rules['free_items.*.protein'] = ['nullable', 'numeric', 'min:0', 'max:999'];
            $rules['free_items.*.fat'] = ['nullable', 'numeric', 'min:0', 'max:999'];
            $rules['free_items.*.carbs'] = ['nullable', 'numeric', 'min:0', 'max:999'];
        } else {
            // クイック記録は名前だけ
            $rules['food_name_free'] = ['required', 'string', 'max:100'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'eaten_on.required' => '日付は必須です。',
            'eaten_on.before_or_equal' => '未来の日付は指定できません。',
            'meal_type.required' => '区分を選択してください。',
            'meal_type.in' => '区分の値が不正です。',
            'food_id.required' => '食品を選択してください。',
            'food_id.exists' => '選択した食品が見つかりません。',
            'amount_g.required' => '量(g)を入力してください。',
            'amount_g.min' => '量は1g以上で入力してください。',
            'amount_g.max' => '量は2000g以内で入力してください。',
            'items.required' => '食品を1つ以上追加してください。',
            'items.max' => '一度に登録できるのは20件までです。',
            'items.*.food_id.required' => '食品を選択してください。',
            'items.*.food_id.exists' => '選択した食品が見つかりません。',
            'items.*.amount_g.required' => '量(g)を入力してください。',
            'items.*.amount_g.min' => '量は1g以上で入力してください。',
            'items.*.amount_g.max' => '量は2000g以内で入力してください。',
            'food_name_free.required' => '食品名を入力してください。',
            'food_name_free.max' => '食品名は100文字以内で入力してください。',
            'kcal.max' => 'kcalは9999以内で入力してください。',
            'free_items.required' => '食品名を1つ以上入力してください。',
            'free_items.max' => '一度に登録できるのは20件までです。',
            'free_items.*.food_name_free.required' => '食品名を入力してください。',
            'free_items.*.food_name_free.max' => '食品名は100文字以内で入力してください。',
            'free_items.*.kcal.max' => 'kcalは9999以内で入力してください。',
        ];
    }
}
