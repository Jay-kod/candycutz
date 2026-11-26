<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Services\ConsentService;
use App\Domain\Shared\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GetConsentReport
{
    public function __construct(private readonly ConsentService $consentService) {}

    /**
     * @param  array{status?: string, search?: string, per_page?: int}  $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters): array
    {
        $version = $this->consentService->currentTermsVersion();
        $query = $this->baseQuery($filters);

        $stats = [
            'total' => (clone $query)->count(),
            'accepted' => $this->accepted(clone $query, $version)->count(),
            'pending' => $this->pending(clone $query, $version)->count(),
        ];

        $status = $filters['status'] ?? 'all';
        if ($status === 'accepted') {
            $this->accepted($query, $version);
        } elseif ($status === 'pending') {
            $this->pending($query, $version);
        }

        $perPage = min(100, max(1, (int) ($filters['per_page'] ?? 25)));
        $paginator = $query
            ->with(['consents' => fn ($relation) => $relation->where('type', 'terms')->orderByDesc('accepted_at')])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->transform(fn (User $user) => $this->mapUser($user, $version));

        return [
            'terms_version' => $version,
            'stats' => $stats,
            'users' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * @param  array{status?: string, search?: string, per_page?: int}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function export(array $filters): array
    {
        $version = $this->consentService->currentTermsVersion();

        return $this->baseQuery($filters)
            ->with(['consents' => fn ($relation) => $relation->where('type', 'terms')->orderByDesc('accepted_at')])
            ->orderBy('name')
            ->limit(5000)
            ->get()
            ->map(fn (User $user) => $this->mapUser($user, $version))
            ->all();
    }

    /**
     * @param  array{status?: string, search?: string, per_page?: int}  $filters
     * @return Builder<User>
     */
    private function baseQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return User::query()
            ->where('role', UserRole::customer->value)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            });
    }

    /** @param Builder<User> $query
     * @return Builder<User>
     */
    private function accepted(Builder $query, string $version): Builder
    {
        return $query->whereHas('consents', function (Builder $relation) use ($version): void {
            $relation->where('type', 'terms')->where('version', $version);
        });
    }

    /** @param Builder<User> $query
     * @return Builder<User>
     */
    private function pending(Builder $query, string $version): Builder
    {
        return $query->whereDoesntHave('consents', function (Builder $relation) use ($version): void {
            $relation->where('type', 'terms')->where('version', $version);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function mapUser(User $user, string $version): array
    {
        $consent = $user->consents->first();
        $acceptedCurrent = $consent !== null && $consent->version === $version;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'is_active' => (bool) $user->is_active,
            'created_at' => $user->created_at?->toIso8601String(),
            'terms_status' => $acceptedCurrent ? 'accepted' : 'pending',
            'terms_version' => $consent?->version,
            'accepted_at' => $consent?->accepted_at?->toIso8601String(),
            'source' => $consent?->source,
            'ip_address' => $consent?->ip_address,
            'user_agent' => $consent?->user_agent,
        ];
    }
}
