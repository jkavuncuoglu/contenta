<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Exceptions;

use DomainException;

class PostNotFoundException extends DomainException
{
    public function __construct(int $postId)
    {
        parent::__construct("Post with ID {$postId} not found");
    }
}
