<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return ($this->path() === 'customers');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'searchId' => ['nullable', 'integer', 'min:1'],
            'searchName' => ['nullable', 'string', 'max:255'],
            'searchKana' => ['nullable', 'string', 'max:255'],
            'searchTel' => ['nullable', 'string', 'max:20'],
            'searchEmail' => ['nullable', 'string', 'max:255'],
        ];
    }

    // public function attributes()
    // {
    //     return __('messages.seminar.search.attributes');
    // }
}
