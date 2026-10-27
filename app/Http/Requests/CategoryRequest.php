<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:60',
                Rule::unique('categories')
                    ->where('user_id', $this->user()->id)
                    ->ignore($this->route('category')),
            ],
            'group' => ['required', Rule::in(array_keys(Category::GROUPS))],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nombre', 'group' => 'grupo'];
    }
}
