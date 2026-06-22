<?php

namespace Seafile\Client\Type;

use Sdo\Bitmask\AbstractBitmask;

/**
 * Bitmask for share links permissions.
 *
 * @see https://download.seafile.com/published/web-api/v2.1/share-links.md#user-content-Create%20Share%20Link
 */
class SharedLinkPermissions extends AbstractBitmask
{
    public const int CAN_DOWNLOAD = 1;

    public const int CAN_EDIT = 2;
}
