<?php

namespace App\Http\Requests;

use App\Rules\MaxWordsRule;
use App\Rules\FutureDateRule;
use App\Rules\SlugFormatRule;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;

class StorePostRequest extends PostRequest
{
   /**
     * {@inheritdoc}
     */
    public function authorize(): bool
    {

        return parent::authorize();
    }

    /**
     * Get the specific validation rules for storing a new post.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug'  => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('posts', 'slug'),
                new SlugFormatRule(),
            ],
            'body' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'publish_date' => [
                'nullable',
                'date',
                new FutureDateRule(),
                Rule::requiredIf(fn () => $this->boolean('is_published'))
            ],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'tags'             => ['nullable', 'string'],
            'keywords' => [
                'nullable',
                'string',
                new MaxWordsRule(15)
            ]
        ];
    }


    /**
     * Get custom messages specific to storing a post or override base messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'slug.unique' => 'This slug (:input) is already in use. Please choose another one or leave it blank for automatic generation.',
            'publish_date.required_if' => 'Since you marked the article as "published", the publish date must be specified.',
        ]);
    }
    /**
     * {@inheritdoc}
     * attributes() is inherited from PostRequest
     */
    public function attributes(): array
    {
        return parent::attributes();
    }

    /**{@inheritdoc}
     *
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
    }
    /**
     * Handle any tasks that should occur after validation passes for storing.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Log specific to storing
        Log::info(static::class . ': Validation passed for storing post.', [
            'validated_data' => $this->validated()
        ]);
    }

    /**
     * Get the context for logging failed validation during store attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return array<string, mixed>
     */
    protected function getFailedValidationLogContext(Validator $validator): array
    {
        // No specific post ID yet, use base context
        return parent::getFailedValidationLogContext($validator);
    }

    /**
     * Get the user-facing message for failed validation during store attempt.
     *
     * @return string
     */
    protected function getFailedValidationMessage(): string
    {
        return 'The input data for creating the post is invalid or incomplete.';

    }


}
