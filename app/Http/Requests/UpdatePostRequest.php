<?php


namespace App\Http\Requests;

use App\Rules\MaxWordsRule;
// Remove imports now handled by PostRequest if not directly used here
use App\Rules\FutureDateRule;
use App\Rules\SlugFormatRule;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\Post; // Keep model import for route model binding check
use Illuminate\Contracts\Validation\Validator; // Keep for type hinting

class UpdatePostRequest extends PostRequest // Changed inheritance
{

  /**
     * {@inheritdoc}
     * authorize() is inherited from PostRequest.
     */
    public function authorize(): bool
    {
        return parent::authorize();
    }
    /**
     * Get the specific validation rules for updating an existing post.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $postId = $this->getPostId();
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug'  => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')->ignore($postId), // تجاهل المنشور الحالي
                new SlugFormatRule(),
            ],
            'body' => ['sometimes', 'required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'publish_date' => [
                'nullable',
                'date',
                new FutureDateRule(),

                Rule::requiredIf(fn () => $this->has('is_published') && $this->boolean('is_published'))
            ],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:160'],
            'tags'             => ['sometimes', 'nullable', 'string'],
            'keywords' => [
                'sometimes',
                'nullable',
                'string',
                new MaxWordsRule(15)
            ]
        ];
    }
     /**
      * Get custom messages specific to updating a post or override base messages.
      *
      * @return array<string, string>
      */
     public function messages(): array
    {

        return array_merge(parent::messages(), [

            'slug.unique' => 'This slug (:input) is already taken by another post. Please choose a different one.',

            'title.required' => 'The title field is required when provided for update.',
            'slug.required' => 'The slug field is required when provided for update.',
            'body.required' => 'The content field is required when provided for update.',

        ]);
    }

    /**
     * {@inheritdoc}
     *attributes() is inherited from PostRequest
     */
    public function attributes(): array
    {
        return parent::attributes();
    }

    /**
     * {@inheritdoc}
     *
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
    }
    /**
     * Handle any tasks that should occur after validation passes for updating.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        Log::info(static::class . ': Validation passed for updating post.', [
            'post_id' => $this->getPostId(),
            'validated_data' => $this->validated()
        ]);
    }

    /**
     * Get the context for logging failed validation during update attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return array<string, mixed>
     */
    protected function getFailedValidationLogContext(Validator $validator): array
    {
        $context = parent::getFailedValidationLogContext($validator);
        $context['post_id'] = $this->getPostId();
        return $context;
    }

    /**
     * Get the user-facing message for failed validation during update attempt.
     *
     * @return string
     */
    protected function getFailedValidationMessage(): string
    {
        return 'The input data for updating the post is invalid or incomplete.';

    }

    // failedValidation() method is inherited from PostRequest

    /**
     * Helper method to consistently get the post ID from the route.
     * Handles both route model binding and raw ID parameter.
     *
     * @return int|string|null
     */
    protected function getPostId(): int|string|null
    {
        $post = $this->route('post');

        if ($post instanceof Post) {
            return $post->id;
        }
        if (is_numeric($post) || is_string($post)) {
             return $post;
        }

        return null; 
    }
}
