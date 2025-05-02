<?php

namespace App\Http\Requests;

use App\Rules\MaxWordsRule;
use Illuminate\Support\Str;
use App\Rules\FutureDateRule;
use App\Rules\SlugFormatRule;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Log::info('StorePostRequest: Authorize check passed.');
        return true ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',

            'slug'  => [
                'nullable',
                'string'  ,
                'max:255',
                Rule::unique('posts', 'slug'),
                new SlugFormatRule(),
                'sometimes'
            ],

            'body' => 'required|string',

            'is_published' =>'sometimes|boolean',

            'publish_date' => [
                'nullable',
                'date',
                new FutureDateRule(),
                Rule::requiredIf(fn () => $this->boolean('is_published'))
            ],

            'meta_description' => 'nullable|string|max:160',

            'tags'             => 'nullable|string',

            'keywords' => [
                'nullable',
                'string'  ,
                new MaxWordsRule(15)
        ]

        ];
    }

    public function messages(){
        return[
            'title.required' => 'The title field is required.',
            'title.max'      => 'The title field may not be greater than :max characters.',
            'slug.unique' => 'This slug (:input) is already in use. Please choose another one or leave it blank for automatic generation.',
            'slug.max' => 'The slug field may not be greater than :max characters.',
            'body.required' => 'The article content is required.',
            'is_published.boolean' => 'The is_published field must be true or false.',
            'publish_date.date' => 'The publish date must be a valid date.',
            'meta_description.max' => 'The meta description may not be greater than :max characters.',
            'keywords.string' => 'The keywords field must be a string.',
            'publish_date.required_if' => 'Since you marked the article as "published", the publish date must be specified.',
        ];
    }

    public function attributes(){
        return [
            'title' => 'Title',
            'slug' => 'Slug',
            'body' => 'Content',
            'is_published' => 'Publishing Status',
            'publish_date' => 'Publish Date',
            'meta_description' => 'Meta Description',
            'tags' => 'Tags',
            'keywords' => 'Keywords',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('title') && !$this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($this->title),
            ]);
        }

        if ($this->filled('tags')) {
            $cleanedTags = preg_replace('/\s*,\s*/', ',', trim($this->tags));
            $cleanedTags = preg_replace('/,{2,}/', ',', $cleanedTags);
            $cleanedTags = trim($cleanedTags, ',');
            $this->merge([
                'tags' => $cleanedTags ?: null
            ]);
        }

        if ($this->has('is_published')) {
             $this->merge([
                'is_published' => $this->boolean('is_published')
             ]);
        }

         if ($this->has('is_published') && !$this->boolean('is_published')) {
              $this->merge(['publish_date' => null]);
         }

         if ($this->input('publish_date') === '') {
             $this->merge(['publish_date' => null]);
         }
         if ($this->input('meta_description') === '') {
             $this->merge(['meta_description' => null]);
         }
         if ($this->input('tags') === '') {
             $this->merge(['tags' => null]);
         }
          if ($this->input('keywords') === '') {
             $this->merge(['keywords' => null]);
         }
    }

    protected function passedValidation(): void
    {
        Log::info('StorePostRequest: Validation passed.', [
            'validated_data' => $this->validated()
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::warning('StorePostRequest: Validation failed.', [
            'errors' => $validator->errors()
        ]);
        $response = response()->json([
            'status' => 'error',
            'message' => 'The input data is invalid or incomplete.',
            'errors' => $validator->errors()
        ], 422);

        throw new HttpResponseException($response);
    }
}
