<?php

use App\Domain\Booking\AppointmentStateMachine;
use App\Domain\Shared\Enums\AppointmentStatus;

it('includes draft in active statuses and not in terminal statuses', function () {
    expect(AppointmentStatus::activeStatuses())->toContain(AppointmentStatus::draft);
    expect(AppointmentStatus::terminalStatuses())->not->toContain(AppointmentStatus::draft);
    expect(AppointmentStatus::draft->isTerminal())->toBeFalse();
});

it('allows valid transitions from draft state', function () {
    expect(AppointmentStateMachine::isAllowed(AppointmentStatus::draft, AppointmentStatus::confirmed))->toBeTrue();
    expect(AppointmentStateMachine::isAllowed(AppointmentStatus::draft, AppointmentStatus::pending))->toBeTrue();
    expect(AppointmentStateMachine::isAllowed(AppointmentStatus::draft, AppointmentStatus::cancelled))->toBeTrue();
});

it('disallows invalid transitions from draft state', function () {
    expect(AppointmentStateMachine::isAllowed(AppointmentStatus::draft, AppointmentStatus::completed))->toBeFalse();
    expect(AppointmentStateMachine::isAllowed(AppointmentStatus::draft, AppointmentStatus::checked_in))->toBeFalse();
    expect(AppointmentStateMachine::isAllowed(AppointmentStatus::draft, AppointmentStatus::in_progress))->toBeFalse();
    expect(AppointmentStateMachine::isAllowed(AppointmentStatus::draft, AppointmentStatus::no_show))->toBeFalse();
});

it('enforces role permissions for draft transitions', function () {
    expect(AppointmentStateMachine::isRoleAllowed(AppointmentStatus::draft, AppointmentStatus::confirmed, 'customer'))->toBeTrue();
    expect(AppointmentStateMachine::isRoleAllowed(AppointmentStatus::draft, AppointmentStatus::cancelled, 'customer'))->toBeTrue();
    expect(AppointmentStateMachine::isRoleAllowed(AppointmentStatus::draft, AppointmentStatus::pending, 'customer'))->toBeTrue();
    expect(AppointmentStateMachine::isRoleAllowed(AppointmentStatus::draft, AppointmentStatus::cancelled, 'barber'))->toBeTrue();
    expect(AppointmentStateMachine::isRoleAllowed(AppointmentStatus::draft, AppointmentStatus::cancelled, 'admin'))->toBeTrue();
});
