<?php

namespace App\Domains\Security\UserManagement\Constants;

class UserDefaults
{
    const DEFAULT_AVATAR = 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png';
    const DEFAULT_LANGUAGE = 'en';
    const DEFAULT_TIMEZONE = 'UTC';

    const ALL = [
        'avatar' => self::DEFAULT_AVATAR,
        'language' => self::DEFAULT_LANGUAGE,
        'timezone' => self::DEFAULT_TIMEZONE,
    ];
}
