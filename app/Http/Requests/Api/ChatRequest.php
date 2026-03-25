<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:2', 'max:2000'],
            'language' => ['nullable', 'string', 'in:ar,en'],
            'conversation_id' => ['nullable', 'string', 'max:128'],
        ];
    }
}

