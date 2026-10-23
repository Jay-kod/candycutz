<?php

declare(strict_types=1);

namespace App\Domain\Booking;

use App\Domain\Shared\Enums\AppointmentStatus;

/**
 * Defines the legal state transitions for appointments and enforces
 * role-based access to each transition.
 *
 * Transition rules:
 *   pending    → confirmed | cancelled
 *   confirmed  → checked_in | no_show | cancelled
 *   checked_in → in_progress | cancelled
 *   in_progress→ completed
 *   completed  → (terminal)
 *   cancelled  → (terminal)
 *   no_show    → (terminal)
 */
class AppointmentStateMachine
{
    /**
     * Map of from → allowed to-statuses.
     *
     * @var array<string, string[]>
     */
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['checked_in', 'no_show', 'cancelled'],
        'checked_in' => ['in_progress', 'cancelled'],
        'in_progress' => ['completed'],
        'completed' => [],
        'cancelled' => [],
        'no_show' => [],
    ];

    /**
     * Which roles may perform each transition.
     * '*' means any authenticated role can do it.
     *
     * @var array<string, string[]>
     */
    private const ROLE_PERMISSIONS = [
        // customer can only cancel their own pending/confirmed bookings
        'pending→cancelled' => ['customer', 'barber', 'admin', 'super_admin'],
        'pending→confirmed' => ['barber', 'admin', 'super_admin'],
        'confirmed→cancelled' => ['customer', 'barber', 'admin', 'super_admin'],
        'confirmed→checked_in' => ['barber', 'admin', 'super_admin'],
        'confirmed→no_show' => ['barber', 'admin', 'super_admin'],
        'checked_in→in_progress' => ['barber', 'admin', 'super_admin'],
        'checked_in→cancelled' => ['barber', 'admin', 'super_admin'],
        'in_progress→completed' => ['barber', 'admin', 'super_admin'],
    ];

    /**
     * Check if a transition from $from to $to is structurally allowed.
     */
    public static function isAllowed(AppointmentStatus|string $from, AppointmentStatus|string $to): bool
    {
        $fromValue = $from instanceof AppointmentStatus ? $from->value : $from;
        $toValue = $to instanceof AppointmentStatus ? $to->value : $to;

        return in_array($toValue, self::TRANSITIONS[$fromValue] ?? [], true);
    }

    /**
     * Check if a given role may perform the transition.
     */
    public static function isRoleAllowed(AppointmentStatus|string $from, AppointmentStatus|string $to, string $role): bool
    {
        if (! self::isAllowed($from, $to)) {
            return false;
        }

        $fromValue = $from instanceof AppointmentStatus ? $from->value : $from;
        $toValue = $to instanceof AppointmentStatus ? $to->value : $to;
        $key = "{$fromValue}→{$toValue}";

        $allowedRoles = self::ROLE_PERMISSIONS[$key] ?? [];

        // If no specific role permission defined, deny by default
        if (empty($allowedRoles)) {
            return false;
        }

        return in_array($role, $allowedRoles, true);
    }

    /**
     * Get all allowed transitions from a given status.
     *
     * @return string[]
     */
    public static function allowedTransitions(AppointmentStatus|string $from): array
    {
        $fromValue = $from instanceof AppointmentStatus ? $from->value : $from;

        return self::TRANSITIONS[$fromValue] ?? [];
    }

    /**
     * Get all allowed transitions for a given status and role.
     *
     * @return string[]
     */
    public static function allowedTransitionsForRole(AppointmentStatus|string $from, string $role): array
    {
        $transitions = self::allowedTransitions($from);

        return array_values(array_filter($transitions, function (string $to) use ($from, $role) {
            return self::isRoleAllowed($from, $to, $role);
        }));
    }
}
