<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'employee'
            && $this->user()->employee()->exists();
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'Latitude wajib dikirim.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus berada antara -90 dan 90.',
            'longitude.required' => 'Longitude wajib dikirim.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus berada antara -180 dan 180.',
        ];
    }
}
