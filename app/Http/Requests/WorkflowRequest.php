<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'steps' => 'required|array|min:1',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.type' => 'required|string|in:prompt,function,condition,parallel,agent',
            'steps.*.prompt' => 'required_if:steps.*.type,prompt|string',
            'steps.*.agent' => 'required_if:steps.*.type,agent|string',
            'steps.*.function' => 'required_if:steps.*.type,function|string',
            'steps.*.condition' => 'required_if:steps.*.type,condition|string',
            'context' => 'nullable|array',
        ];
    }
}
