<?php

namespace AppBundle\WebHook;

use AppBundle\Membership\UserEvents;
use MyCLabs\Enum\Enum;

/**
 * @method static USER_CREATION()
 * @method static USER_MODIFICATION()
 * @method static USER_DELETION()
 */
class Event extends Enum
{
    public const USER_CREATION = UserEvents::USER_CREATED;
    public const USER_MODIFICATION = UserEvents::USER_UPDATED;
    public const USER_DELETION = UserEvents::USER_DELETED;
}
