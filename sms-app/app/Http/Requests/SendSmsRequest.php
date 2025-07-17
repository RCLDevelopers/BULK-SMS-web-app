<?php

namespace App\Http\Requests;

use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendSmsRequest extends FormRequest
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
            'to' => [
                'sometimes',
                'required_without:recipients',
                'string',
                new ValidPhoneNumber(),
            ],
            'recipients' => [
                'sometimes',
                'required_without:to',
                'array',
                'min:1',
            ],
            'recipients.*' => [
                'required',
                'string',
                new ValidPhoneNumber(),
            ],
            'message' => [
                'required',
                'string',
                'min:1',
                'max:1600', // Max 10 SMS messages (160 chars * 10)
            ],
            'subject' => [
                'nullable',
                'string',
                'max:50',
            ],
            'service' => [
                'nullable',
                'string',
                Rule::in(['twilio', 'textsms']),
            ],
            'schedule_at' => [
                'nullable',
                'date',
                'after_or_equal:now',
            ],
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated();

        // If 'to' is provided, convert it to an array of recipients
        if (isset($validated['to']) && !isset($validated['recipients'])) {
            $validated['recipients'] = [$validated['to']];
            unset($validated['to']);
        }

        // Remove empty values from recipients array
        if (isset($validated['recipients'])) {
            $validated['recipients'] = array_filter($validated['recipients']);
        }

        return $validated;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'to.required_without' => 'The recipient phone number is required if recipients list is not provided.',
            'recipients.required_without' => 'At least one recipient is required if single recipient is not provided.',
            'recipients.array' => 'Recipients must be an array of phone numbers.',
            'recipients.min' => 'At least one recipient is required.',
            'message.required' => 'The message field is required.',
            'message.max' => 'The message is too long. Maximum 10 SMS messages (1600 characters) allowed.',
            'service.in' => 'The selected SMS service is invalid.',
            'schedule_at.after_or_equal' => 'The scheduled time must be in the future.',
        ];
    }
}
