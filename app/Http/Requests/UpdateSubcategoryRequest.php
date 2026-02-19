<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubcategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'farmercategory_id' => 'required|integer|exists:farmercategories,id',
            'subcategory_name' => 'required|string|max:50|unique:farmersubcategories,subcategory_name,' . $this->farmersubcategory->id,
            'status' => 'required|in:Active,Inactive',
            'image' => 'nullable',
        ];
    }
}
