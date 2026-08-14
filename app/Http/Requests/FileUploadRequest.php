<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class FileUploadRequest.
 *
 * @author annejan@badge.team
 */
class FileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::guard()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|max:' . (File::MAX_UPLOAD_MEGABYTES * 1024),
        ];
    }
}
