<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\Services\NoShowPolicyService;
use App\Models\NoShowRecord;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetNoShowDashboardData
{
    public function __construct(
        protected NoShowPolicyService $policyService
    ) {}

    /**
     * @return array{
     *   records: LengthAwarePaginator<int, NoShowRecord>,
     *   policy: array<string, mixed>,
     *   stats: array<string, int>,
     *   flagged_customers: array<int, array<string, mixed>>
     * }
     */
    public function execute(?string $filter = null, ?string $search = null, int $perPage = 20): array
    {
        $query = NoShowRecord::query()
            ->with([
                'appointment.service',
                'appointment.barber.user',
                'appointment.payment',
                'customer',
                'markedBy',
                'forfeitedBy',
                'resolvedBy',
            ])
            ->orderByDesc('marked_at');

        if ($filter === 'pending_appeals') {
            $query->where('appealed', true)->where('appeal_outcome', 'pending');
        } elseif ($filter === 'appealed') {
            $query->where('appealed', true);
        } elseif ($filter === 'forfeited') {
            $query->where('deposit_forfeited', true);
        } elseif ($filter === 'refundable') {
            $query->whereHas('appointment.payment', fn ($q) => $q->whereIn('status', ['successful', 'verified']));
        }

        if ($search !== null && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(function ($q) use ($term): void {
                $q->whereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('email', 'like', $term))
                    ->orWhereHas('appointment', fn ($a) => $a->where('booking_reference', 'like', $term)->orWhere('client_name', 'like', $term));
            });
        }

        $flaggedCustomers = User::query()
            ->where('requires_deposit', true)
            ->orderByDesc('last_no_show_at')
            ->limit(25)
            ->get(['id', 'name', 'email', 'phone', 'no_show_count', 'last_no_show_at', 'requires_deposit'])
            ->map(fn (User $u): array => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'no_show_count' => (int) $u->no_show_count,
                'last_no_show_at' => $u->last_no_show_at?->toIso8601String(),
                'requires_deposit' => (bool) $u->requires_deposit,
            ])
            ->all();

        return [
            'records' => $query->paginate(min(100, max(5, $perPage))),
            'policy' => $this->policyService->getConfig(),
            'stats' => [
                'total_no_shows' => NoShowRecord::count(),
                'pending_appeals' => NoShowRecord::where('appealed', true)->where('appeal_outcome', 'pending')->count(),
                'forfeited_count' => NoShowRecord::where('deposit_forfeited', true)->count(),
                'flagged_customers' => User::where('requires_deposit', true)->count(),
            ],
            'flagged_customers' => $flaggedCustomers,
        ];
    }
}
