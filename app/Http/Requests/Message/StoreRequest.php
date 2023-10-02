<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'chat_id' => 'required|integer|exists:chats,id',
            'body' => 'required|string',
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|integer|exists:users,id',
        ];
    }
}
