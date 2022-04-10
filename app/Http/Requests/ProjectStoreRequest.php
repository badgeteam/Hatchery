<?php

declare(strict_types=1);

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
            'name'        => 'required|unique:projects',
            'description' => 'required|sometimes',
            'git'         => 'required|sometimes',
            'category_id' => 'required|exists:categories,id',
        ];
    }
}
