<?php

namespace App\Scope;

use MyCLabs\Enum\Enum;

class AppEnum extends Enum
{
    public const DATA_CORNER = 'data_corner';

    public const ALL = [
        self::DATA_CORNER,
    ];
}
