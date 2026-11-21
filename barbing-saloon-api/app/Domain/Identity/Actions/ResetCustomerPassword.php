<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Enums\UserRole;
use App\Domain\Shared\Traits\HasAuditLog;
use App\Events\PasswordChanged;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class ResetCustomerPassword
{
    use HasAuditLog;

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id, string $newPassword): array
    {
        Gate::authorize('manage-customers');

        /** @var User $customer */
        $customer = User::where('role', UserRole::customer)->findOrFail($id);

        $customer->update([
            'password' => Hash::make($newPassword),
        ]);

        $customer->tokens()->delete();
        PasswordChanged::dispatch($customer);

        $this->logAction('customer.password_reset', $customer, [], [
            'email' => $customer->email,
        ]);

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
        ];
    }
}

