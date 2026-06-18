<?php

namespace App\Http\Requests;

use Carbon\Carbon;
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
            'clock_in' => [
                'bail',
                'date_format:H:i',
                'required',
            ],
            'clock_out' => [
                'bail',
                'date_format:H:i',
                'after_or_equal:clock_in',
                'required',
            ],
            'breaks.*.break_in' => [
                'bail',
                'date_format:H:i',
                'after_or_equal:clock_in',
                'before_or_equal:clock_out',
                'required_with:breaks.*.break_out',
                'nullable',
            ],
            'breaks.*.break_out' => [
                'bail',
                'date_format:H:i',
                'before_or_equal:clock_out',
                'required_with:breaks.*.break_in',
                'nullable',
                'after_or_equal:break_out',
            ],
            'comment' => [
                'required',
                'max:255',
            ]
        ];
    }

    public function messages()
    {
        return [
            'clock_in.date_format' => '出勤時間は「HH:MM」形式で入力してください',
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.date_format' => '退勤時間は「HH:MM」形式で入力してください',
            'clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '退勤時間を入力してください',
            'breaks.*.break_in.date_format' => '休憩時間は「HH:MM」形式で入力してください',
            'breaks.*.break_in.after_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.break_in.before_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.break_in.required_with' => '休憩時間は両方入力してください',
            'breaks.*.break_out.date_format' => '休憩時間は「HH:MM」形式で入力してください',
            'breaks.*.break_out.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_out.after_or_equal' => '休憩開始時間もしくは休憩終了時間が不適切な値です',
            'breaks.*.break_out.required_with' => '休憩時間は両方入力してください',
            'comment.required' => '備考を記入してください',
            'comment.max' => '備考は255文字以内で記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $breaks = $this->input('breaks', []);

            $ranges = [];

            foreach ($breaks as $index => $break) {
                if (empty($break['break_in']) || empty($break['break_out'])) {
                    continue;
                }

                if (
                    !preg_match('/^\d{2}:\d{2}$/', $break['break_in']) ||
                    !preg_match('/^\d{2}:\d{2}$/', $break['break_out'])
                ) {
                    continue;
                }

                $start = Carbon::createFromFormat('H:i', $break['break_in']);
                $end = Carbon::createFromFormat('H:i', $break['break_out']);

                $ranges[] = [
                    'index' => $index,
                    'start' => $start,
                    'end' => $end,
                ];
            }

            foreach ($ranges as $i => $current) {
                foreach ($ranges as $j => $target) {
                    if ($i >= $j) {
                        continue;
                    }

                    if ($current['start']->lt($target['end']) && $target['start']->lt($current['end'])) {
                        $validator->errors()->add(
                            "breaks.{$target['index']}.break_in",
                            '休憩時間が重複しています'
                        );
                    }
                }
            }
        });
    }
}
