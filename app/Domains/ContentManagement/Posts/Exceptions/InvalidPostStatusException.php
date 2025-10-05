<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Exceptions;

use DomainException;

class InvalidPostStatusException extends DomainException
{
    public function __construct(string $message = 'Invalid post status')
    {
        parent::__construct($message);
    }
}
