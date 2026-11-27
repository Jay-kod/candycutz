<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Enums\UserRole;
use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ResetCustomerPassword
{
    use HasAuditLog;

    public function __construct(
        protected SetUserPassword $setUserPassword
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id, string $newPassword): array
    {
        Gate::authorize('manage-customers');

        return DB::transaction(function () use ($id, $newPassword) {
            /** @var User $customer */
            $customer = User::where('role', UserRole::customer)->lockForUpdate()->findOrFail($id);

            $this->setUserPassword->execute($customer, $newPassword);

            $this->logAction('customer.password_reset', $customer, [], [
                'email' => $customer->email,
                'admin_override' => true,
                'is_email_verified' => $customer->hasVerifiedEmail(),
            ]);

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'is_email_verified' => $customer->hasVerifiedEmail(),
            ];
        });
    }
}
