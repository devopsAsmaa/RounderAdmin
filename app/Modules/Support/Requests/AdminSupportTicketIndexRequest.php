<?php

namespace App\Modules\Support\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSupportTicketIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof \App\Modules\Identity\Models\Admin;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:all,open,in_progress,resolved,closed'],
            'category' => ['nullable', 'string', 'max:50'],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }
}

