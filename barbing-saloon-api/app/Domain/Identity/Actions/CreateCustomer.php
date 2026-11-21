<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Services\UsernameIdentityService;
use App\Domain\Shared\Enums\UserRole;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateCustomer
{
    use HasAuditLog;

    public function __construct(
        protected UsernameIdentityService $usernameService,
        protected UserMediaUrl $mediaUrl
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(array $data): array
    {
        Gate::authorize('manage-customers');

        $name = trim((string) $data['name']);
        $username = $this->usernameService->generateUniqueUsername($name);
        $password = ! empty($data['password']) ? (string) $data['password'] : Str::random(12);

        $customer = new User([
            'name' => $name,
            'real_name' => $name,
            'username' => $username,
            'email' => strtolower(trim((string) $data['email'])),
            'phone' => ! empty($data['phone']) ? trim((string) $data['phone']) : null,
            'password' => Hash::make($password),
            'last_username_change_at' => now(),
        ]);
        $customer->role = UserRole::customer;
        $customer->is_active = true;
        $customer->status = 'active';
        $customer->save();

        $this->logAction('customer.created', $customer, [], [
            'email' => $customer->email,
            'username' => $customer->username,
            'role' => 'customer',
        ]);

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone ?? 'N/A',
            'is_active' => true,
            'status' => 'active',
            'auth_provider' => 'local',
            'registered_via' => 'Admin portal',
            'avatar' => $this->mediaUrl->for($customer, $customer->avatar),
            'created_at' => $customer->created_at?->toIso8601String(),
            'total_bookings' => 0,
            'total_spent' => 0.0,
            'last_booking_date' => null,
        ];
    }
}

