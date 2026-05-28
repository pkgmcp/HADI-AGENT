<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentExecuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task' => 'required|string|max:50000',
            'context' => 'nullable|array',
            'context.session_id' => 'nullable|string',
            'agent' => 'nullable|string|in:' . implode(',', array_keys(config('agents.agents', []))),
        ];
    }
}
