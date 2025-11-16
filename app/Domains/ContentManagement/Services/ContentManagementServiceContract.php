<?php

namespace App\Domains\ContentManagement\Services;

interface ContentManagementServiceContract
{
    public function posts();

    public function pages();

    public function tags();

    public function categories();
}
