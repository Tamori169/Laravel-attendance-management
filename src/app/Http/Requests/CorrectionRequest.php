<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'requested_clock_out' => [
                'after_or_equal:requested_clock_in',
            ],
            'requested_breaks.*.break_in' => [
                'nullable',
                'required_with:requested_breaks.*.break_out',
                'after_or_equal:requested_clock_in',
                'before_or_equal:requested_clock_out',
            ],
            'requested_breaks.*.break_out' => [
                'nullable',
                'required_with:requested_breaks.*.break_in',
                'before_or_equal:requested_clock_out',
            ],
            'comment' => [
                'required',
            ]
        ];
    }

    public function messages()
    {
        return [
            'requested_clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_breaks.*.break_in.after_or_equal' => '休憩時間が不適切な値です',
            'requested_breaks.*.break_in.before_or_equal' => '休憩時間が不適切な値です',
            'requested_breaks.*.break_out.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'comment.required' => '備考を記入してください',
        ];
    }
}
