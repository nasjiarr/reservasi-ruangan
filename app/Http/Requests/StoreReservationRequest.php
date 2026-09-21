<?php

namespace App\Http\Requests;

use App\Rules\RoomAvailableRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => [
                'required',
                'date',
                'after:start_time',
                new RoomAvailableRule(
                    roomId: (int) $this->input('room_id'),
                    startTime: (string) $this->input('start_time'),
                ),
            ],
        ];
    }
}

