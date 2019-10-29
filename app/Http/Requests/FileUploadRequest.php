<?php

namespace App\Http\Requests;

use App\Models\File;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class FileUploadRequest.
 *
 * @package App\Http\Requests
 */
class FileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::guard()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required|file', //|mimes:'.implode(',', File::$extensions),
        ];
    }
}
