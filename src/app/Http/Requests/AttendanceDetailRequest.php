<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceDetailRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'clock_in' => ['required'],
            'clock_out' => ['required'],
            'note' => ['required', 'string'],
            'breaks.*.break_start' => ['nullable'],
            'breaks.*.break_end' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breaks = $this->input('breaks', []);

            // 出勤・退勤チェック
            if ($clockIn && $clockOut) {
                if (strtotime($clockIn) >= strtotime($clockOut)) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            $hasBreakStartError = false;
            $hasBreakEndError = false;
             foreach ($breaks as $break) {
                $breakStart = $break['break_start'] ?? null;
                $breakEnd = $break['break_end'] ?? null;
                if ($breakStart) {
                    if (
                        ($clockIn && strtotime($breakStart) < strtotime($clockIn)) ||
                        ($clockOut && strtotime($breakStart) > strtotime($clockOut))
                    ) {
                        $hasBreakStartError = true;
                    }
                }
                if ($breakEnd) {
                    if ($clockOut && strtotime($breakEnd) > strtotime($clockOut)) {
                        $hasBreakEndError = true;
                    }
                }
            }
            if ($hasBreakStartError) {
                $validator->errors()->add('break_start', '休憩時間が不適切な値です');
            }
            if ($hasBreakEndError) {
                $validator->errors()->add('break_end', '休憩時間もしくは退勤時間が不適切な値です');
            }
        });
    }
}