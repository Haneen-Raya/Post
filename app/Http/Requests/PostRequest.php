<?php

namespace App\Http\Requests;

use App\Rules\MaxWordsRule;
use Illuminate\Support\Str;
use App\Rules\FutureDateRule;
use App\Rules\SlugFormatRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Allows all users by default. Child classes can override if needed.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        Log::info(static::class . ': Authorize check passed.');
        return true;
    }

    /**
     * Get common custom error messages.
     * Child classes can merge or override these messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.max' => 'The title field may not be greater than :max characters.',
            'slug.max' => 'The slug field may not be greater than :max characters.',
            'body.required' => 'The article content is required.',
            'is_published.boolean' => 'The is_published field must be true or false.',
            'publish_date.date' => 'The publish date must be a valid date.',
            'publish_date.required_if' => 'The publish date is required when the post is marked as published.',
            'meta_description.max' => 'The meta description may not be greater than :max characters.',
            'keywords.string' => 'The keywords field must be a string.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     * These are common for both creating and updating posts.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
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

    /**
     * Prepare the data for validation.
     * Applies common data transformations.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        Log::debug(static::class . ': Preparing data for validation.', $this->all());

        $this->prepareSlug();
        $this->prepareTags();
        $this->preparePublishingStatus();
        $this->prepareNullableFields(['publish_date', 'meta_description', 'tags', 'keywords']);

        Log::debug(static::class . ': Data after preparation.', $this->all());
    }

    /**
     * Auto-generate slug from title if slug is not provided.
     */
    protected function prepareSlug(): void
    {
        if ($this->filled('title') && !$this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->title)]);
            Log::debug(static::class . ': Auto-generated slug.', ['slug' => $this->input('slug')]);
        }
         // Ensure slug is trimmed and lowercase if provided
        if ($this->filled('slug')) {
             $this->merge(['slug' => Str::lower(trim($this->slug))]);
        }
    }

    /**
     * Clean the tags input string.
     */
    protected function prepareTags(): void
    {
        if ($this->has('tags')) {
            if ($this->filled('tags')) {
                $cleanedTags = preg_replace('/\s*,\s*/', ',', trim($this->tags));
                $cleanedTags = preg_replace('/,{2,}/', ',', $cleanedTags);
                $cleanedTags = trim($cleanedTags, ',');
                $this->merge([
                    'tags' => $cleanedTags ?: null
                ]);
            } else {

                 $this->merge(['tags' => null]);
            }
        }
    }

    /**
     * Handle boolean casting for 'is_published' and dependent 'publish_date'.
     */
    protected function preparePublishingStatus(): void
    {
        if ($this->has('is_published')) {
            $isPublished = $this->boolean('is_published');
            $this->merge(['is_published' => $isPublished]);
        }
    }


    /**
     * Convert empty string inputs for specified nullable fields to null.
     *
     * @param array<string> $fields
     */
    protected function prepareNullableFields(array $fields): void
    {
        foreach ($fields as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                 $this->merge([$field => null]);
            }
        }
    }

    /**
     * Handle a failed validation attempt.
     * Returns a standardized JSON response.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return never
     */
    protected function failedValidation(Validator $validator): void
    {
        Log::warning(static::class . ': فشل التحقق من الصحة.', $this->getFailedValidationLogContext($validator));

        $responsePayload = [
            'status' => 'error',
            'message' => $this->getFailedValidationMessage(),
            'errors' => $validator->errors()
        ];
        throw new HttpResponseException(
            response()->json($responsePayload, 422) // 422 Unprocessable Entity
        );
    }


    /**
     * Get the context for logging failed validation.
     * Child classes should override this to add specific context like post ID.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return array<string, mixed>
     */
    protected function getFailedValidationLogContext(Validator $validator): array
    {
        return [
            'request_uri' => $this->getRequestUri(),
            'request_data' => $this->except(['password', 'password_confirmation']), // Avoid logging sensitive data
            'errors' => $validator->errors()
        ];
    }


     /**
      * Get the user-facing message for failed validation.
      * Child classes can override this.
      *
      * @return string
      */
     protected function getFailedValidationMessage(): string
     {

        return 'The given data was invalid.';

     }
}
