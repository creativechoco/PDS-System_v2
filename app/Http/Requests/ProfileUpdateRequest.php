<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $units = config('units.list', []);

        return [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Male,Female'],
            'unit' => ['required', Rule::in($units)],
            'phone' => ['required', 'digits:11'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'type' => ['required', 'in:Permanent Employee,Contract of Service,Job Order'],
            'location_assigned' => ['required', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
