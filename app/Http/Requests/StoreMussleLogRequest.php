<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreMussleLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション失敗時にモーダルを開いたままダッシュボードへリダイレクト
     *
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        $date  = $this->input('mussle_date', $this->query('date', today()->format('Y-m-d')));
        $year  = $this->query('year', now()->year);
        $month = $this->query('month', now()->month);

        throw new HttpResponseException(
            redirect()
                ->route('dashboard', ['date' => $date, 'year' => $year, 'month' => $month, 'modal' => 1])
                ->withErrors($validator)
                ->withInput()
        );
    }

    /**
     * バリデーションルール
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'material_id' => [
                'required',
                Rule::exists('materials', 'id')->where('user_id', $this->user()->id),
            ],
            'mussle_date' => ['required', 'date', 'before_or_equal:today'],
            'weight_kg'   => ['nullable', 'numeric', 'min:0', 'max:999'],
            'reps'        => ['required', 'integer', 'min:1', 'max:999'],
            'sets'        => ['required', 'integer', 'min:1', 'max:99'],
            'memo'        => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * バリデーションエラーメッセージ
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'material_id.required'       => '種目を選択してください。',
            'material_id.exists'         => '選択した種目が見つかりません。',
            'mussle_date.required'       => '日付は必須です。',
            'mussle_date.before_or_equal' => '未来の日付は指定できません。',
            'weight_kg.min'              => '重量は0以上で入力してください。',
            'weight_kg.max'              => '重量は999kg以内で入力してください。',
            'reps.required'              => '回数は必須です。',
            'reps.min'                   => '回数は1以上で入力してください。',
            'sets.required'              => 'セット数は必須です。',
            'sets.min'                   => 'セット数は1以上で入力してください。',
            'memo.max'                   => 'メモは1000文字以内で入力してください。',
        ];
    }
}
