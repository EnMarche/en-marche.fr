<?php

namespace App\Scope;

use MyCLabs\Enum\Enum;

class FeatureEnum extends Enum
{
    public const DASHBOARD = 'dashboard';
    public const CONTACTS = 'contacts';
    public const MESSAGES = 'messages';
    public const MOBILE_APP = 'mobile_app';
    public const ELECTIONS = 'elections';

    public const ALL = [
        self::DASHBOARD,
        self::CONTACTS,
        self::MESSAGES,
        self::MOBILE_APP,
        self::ELECTIONS,
    ];
}
