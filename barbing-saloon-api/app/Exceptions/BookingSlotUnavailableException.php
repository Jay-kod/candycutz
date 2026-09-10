<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Core\Http\Response\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingSlotUnavailableException extends Exception
{
    protected ?string $slotDetails;
    protected int $statusCode;

    public function __construct(
        string $message = 'The requested appointment slot is no longer available.',
        ?string $slotDetails = null,
        int $statusCode = 409
    ) {
        parent::__construct($message, $statusCode);
        $this->slotDetails = $slotDetails;
        $this->statusCode = $statusCode;
    }

    public function getSlotDetails(): ?string
    {
        return $this->slotDetails;
    }

    public function render(Request $request): JsonResponse
    {
        $details = $this->slotDetails ? ['slot' => [$this->slotDetails]] : [];

        return ApiResponse::error(
            $this->getMessage(),
            $details,
            $this->statusCode,
            'BOOKING_SLOT_UNAVAILABLE'
        );
    }
}
