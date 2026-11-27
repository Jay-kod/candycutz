<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Api\V1\NotificationApiController;

/**
 * Backward compatibility wrapper forwarding to Api/V1/NotificationApiController.
 */
class NotificationController extends NotificationApiController
{
}
