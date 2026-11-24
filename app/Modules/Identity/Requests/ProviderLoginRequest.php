<?php

namespace App\Modules\Identity\Requests;

use App\Core\Http\Requests\PublicRequest;

class ProviderLoginRequest extends PublicRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }
}
