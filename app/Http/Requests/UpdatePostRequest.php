<?php

namespace App\Http\Requests;

use App\Models\Post;
use App\Rules\MaxWordsRule;
use Illuminate\Support\Str;
use App\Rules\FutureDateRule;
use App\Rules\SlugFormatRule;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Log::info('UpdatePostRequest: Authorize check passed.', ['post_id' => $this->route('post')?->id]);
        return true ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $postId = $this->route('post') instanceof Post ? $this->route('post')->id : null ;
        $postId = $this->route('post') instanceof Post ? $this->route('post')->id : null;
        if (!$postId && $this->route('post')) {
             $postId = $this->route('post');
        }
        return [
            'title' => 'sometimes|required|string|max:255' ,
            'slug'  => [
                'sometimes' ,
                'required'  ,
                'string '   ,
                'max:255',

                Rule::unique('posts','slug')->ignore($postId),
                new SlugFormatRule()
            ],

            'body'          => 'sometimes|required|string',

            'is_published'  => 'sometimes|boolean',

            'publish_date'  => [
                'nullable',
                'date_format:d-m-Y',
                new FutureDateRule(),
                Rule::requiredIf(function () {
                    return $this->has('is_published') && $this->boolean('is_published');
                })
            ],
            'meta_description' => 'nullable|string|max:160',
            'tags'             => 'nullable|string'        ,
            'keywords'         => [
                'nullable'  ,
                'string'    ,
                new MaxWordsRule(15)]
        ];
    }
     public function messages(): array
    {
        return [

            'title.required' => 'The title field is required.',
            'title.max'      => 'The title field must not be greater than :max characters.',
            'slug.unique' => 'This slug (:input) has already been taken. Please choose another one.',
            'slug.required' => 'The slug field is required.',
            'slug.max' => 'The slug must not be greater than :max characters.',
            'body.required' => 'The body field is required.',
            'is_published.boolean' => 'The is_published field must be true or false.',
            'publish_date.date' => 'The publish date must be a valid date.',
            'publish_date.required_if' => 'The publish date is required when the post is marked as published.',
            'meta_description.max' => 'The meta description must not be greater than :max characters.',
            'keywords.string' => 'The keywords field must be a string.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     * This defines user-friendly names for fields used in error messages,
     * replacing the default ':attribute' placeholder.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'Title',
            'slug' => 'Slug',
            'body' => 'Body',
            'is_published' => 'Publishing Status',
            'publish_date' => 'Publish Date',
            'meta_description' => 'Meta Description',
            'tags' => 'Tags',
            'keywords' => 'Keywords',
        ];
    }

    protected function prepareForValidation(): void
    {
        Log::debug('UpdatePostRequest: Preparing data for validation.', $this->all());

        // تنظيف الوسوم
        if ($this->filled('tags')) {
            $cleanedTags = preg_replace('/\s*,\s*/', ',', trim($this->tags));
            $cleanedTags = preg_replace('/,{2,}/', ',', $cleanedTags);
            $cleanedTags = trim($cleanedTags, ',');
            $this->merge([
                'tags' => $cleanedTags ?: null
            ]);
        } else if ($this->has('tags')) { 
             $this->merge(['tags' => null]);
        }



        if ($this->has('is_published')) {
            $this->merge([
                'is_published' => $this->boolean('is_published')
            ]);


            if (!$this->boolean('is_published')) {
                 Log::debug('UpdatePostRequest: is_published is false, merging publish_date as null.');
                 $this->merge(['publish_date' => null]);
            }
        }
         $nullableFields = ['publish_date', 'meta_description', 'keywords'];
         foreach ($nullableFields as $field) {
             if ($this->input($field) === '') {
                 $this->merge([$field => null]);
             }
         }
        if ($this->filled('title') && !$this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($this->input('title'))
            ]);
             Log::debug('UpdatePostRequest: Auto-generated slug.', ['slug' => $this->input('slug')]);
        }

        Log::debug('UpdatePostRequest: Data after preparation.', $this->all());
    }
    protected function passedValidation(): void
    {
        Log::info('Validation passed for updating post.', [

            'post_id' => $this->route('post') instanceof Post ? $this->route('post')->id : $this->route('post'),
            'validated_data' => $this->validated()
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::warning('UpdatePostRequest: Validation failed.', [
            'post_id' => $this->route('post')?->id,
            'errors' => $validator->errors()
        ]);

        $response = response()->json([
            'success' => false,
            'message' => 'بيانات الإدخال غير صالحة أو غير مكتملة.',
            'errors' => $validator->errors()
        ], 422);

        throw new HttpResponseException($response);
    }
}
