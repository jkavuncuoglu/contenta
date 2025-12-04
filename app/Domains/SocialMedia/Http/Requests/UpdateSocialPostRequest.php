<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Http\Requests;

use App\Domains\SocialMedia\Constants\PostStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controller middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['sometimes', 'string', 'max:10000'],
            'media_urls' => ['nullable', 'array'],
            'media_urls.*' => ['url'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'status' => ['sometimes', 'string', 'in:'.PostStatus::DRAFT.','.PostStatus::SCHEDULED],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.max' => 'Post content is too long.',
            'scheduled_at.after' => 'Scheduled time must be in the future.',
        ];
    }
}
