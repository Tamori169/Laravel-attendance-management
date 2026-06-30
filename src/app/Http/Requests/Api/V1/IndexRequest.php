<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * リクエストの実行を許可するか判定する。
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを取得。
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'max:100'],
        ];
    }

    /**
     * バリデーションエラーメッセージを取得する。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.integer' => 'ユーザーIDは数値で指定してください。',
            'date.date_format' => '勤怠日は YYYY-MM-DD 形式で指定してください。',
            'month.date_format' => '勤怠日は YYYY-MM 形式で指定してください。',
            'page.integer' => 'ページ数は数値で指定してください。',
            'per_page.integer' => '1ページあたりの件数は数値で指定してください。',
            'per_page.max' => '1ページあたりの件数は100以内で指定してください。'
        ];
    }
}
