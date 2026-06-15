<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
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
            'clock_out' => [
                'after_or_equal:clock_in',
            ],
            'breaks.*.break_in' => [
                'nullable',
                'required_with:breaks.*.break_out',
                'after_or_equal:clock_in',
                'before_or_equal:clock_out',
            ],
            'breaks.*.break_out' => [
                'nullable',
                'required_with:breaks.*.break_in',
                'before_or_equal:clock_out',
            ],
            'comment' => [
                'required',
            ]
        ];
    }

    public function messages()
    {
        return [
            'clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_in.after_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.break_in.before_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.break_out.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'comment.required' => '備考を記入してください',
        ];
    }
}
