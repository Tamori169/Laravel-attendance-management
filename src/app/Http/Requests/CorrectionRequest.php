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
            'requested_clock_in' => ['required', 'date_format:H:i'],
            'requested_clock_out' => ['required', 'date_format:H:i', 'after_or_equal:requested_clock_in'],
            'requested_break_in' => ['date_format:H:i', 'after_or_equal:requested_clock_in', 'before_or_equal:requested_clock_out'],
            'requested_break_out' => ['date_format:H:i', 'before_or_equal:requested_clock_out'],
            'comment' => ['required','max:255']
        ];
    }

    public function messages()
    {
        return [
            'requested_clock_in.required' => '出勤時間を入力してください',
            'requested_clock_in.date_format' => '出勤時間をHH:MM形式で入力してください',
            'requested_clock_out.required' => '退勤時間を入力してください',
            'requested_clock_out.date_format' => '退勤時間をHH:MM形式で入力してください',
            'requested_clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_break_in.date_format' => '休憩開始時間をHH:MM形式で入力してください',
            'requested_break_in.after_or_equal' => '休憩時間が不適切な値です',
            'requested_break_out.date_format' => '休憩終了時間をHH:MM形式で入力してください',
            'requested_break_in.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'comment.required' => '備考を記入してください',
            'comment.max' => '備考は255文字以内で記入してください',
        ];
    }
}
