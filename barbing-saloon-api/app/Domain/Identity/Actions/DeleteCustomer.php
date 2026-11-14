<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Shared\Enums\UserRole;
use App\Domain\Shared\Traits\HasAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeleteCustomer
{
    use HasAuditLog;

    public function execute(int $id): void
    {
        Gate::authorize('manage-customers');

        DB::transaction(function () use ($id) {
            /** @var User $customer */
            $customer = User::where('role', UserRole::customer)->findOrFail($id);

            $this->logAction('customer.deleted', $customer, [
                'name' => $customer->name,
                'email' => $customer->email,
            ], []);

            // Revoke active sessions / API tokens
            $customer->tokens()->delete();

            // Soft delete customer record (preserves financial/appointment history via soft deletes)
            $customer->delete();
        });
    }
}

