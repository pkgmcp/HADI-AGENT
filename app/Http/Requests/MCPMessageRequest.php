<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MCPMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => 'required|string|in:user,assistant,system',
            'content' => 'required|string|max:100000',
            'metadata' => 'nullable|array',
            'metadata.provider' => 'nullable|string',
            'metadata.model' => 'nullable|string',
            'metadata.session_id' => 'nullable|string',
            'metadata.temperature' => 'nullable|numeric|min:0|max:2',
            'metadata.max_tokens' => 'nullable|integer|min:1|max:32768',
        ];
    }
}
