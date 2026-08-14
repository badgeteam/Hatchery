<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class ProjectStoreRequest.
 *
 * @author annejan@badge.team
 */
class ProjectStoreRequest extends FormRequest
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
            'name' => 'required|unique:projects',
            // The description textarea is always submitted, so "sometimes"
            // never made it optional; an empty one just failed "required".
            // An empty description simply means no README.md is created.
            'description' => 'nullable|string',
            // Only the import form posts this, and there it is mandatory.
            'git'         => 'sometimes|required',
            'category_id' => 'required|exists:categories,id',
            // Free text: an SPDX identifier gets a name and a link, anything
            // else is shown as typed.
            'license' => 'nullable|string|max:255',
        ];
    }
}
