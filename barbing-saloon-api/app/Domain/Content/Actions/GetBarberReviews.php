<?php

declare(strict_types=1);

namespace App\Domain\Content\Actions;

use App\Models\Barber;
use App\Models\BarberService;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class GetBarberReviews
{
    /**
     * @return Collection<int, Testimonial>
     */
    public function execute(Barber $barber, ?Request $request = null): Collection
    {
        $serviceIds = Service::query()
            ->where('barber_id', $barber->id)
            ->orWhereIn('id', BarberService::where('barber_id', $barber->id)->select('service_id'))
            ->pluck('id');

        $query = Testimonial::query()
            ->with(['customer', 'service', 'barber.user'])
            ->where(function ($q) use ($barber, $serviceIds) {
                $q->where('barber_id', $barber->id);
                if ($serviceIds->isNotEmpty()) {
                    $q->orWhereIn('service_id', $serviceIds);
                }
            });

        if ($request) {
            if ($request->has('service_id') && is_numeric($request->service_id)) {
                $query->where('service_id', (int) $request->service_id);
            }

            if ($request->has('rating') && is_numeric($request->rating)) {
                $query->where('rating', (int) $request->rating);
            }

            if ($request->has('is_approved') && $request->is_approved !== 'all') {
                $query->where('is_approved', filter_var($request->is_approved, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->filled('search')) {
                $search = '%'.$request->input('search').'%';
                $query->where(function ($q) use ($search) {
                    $q->where('review', 'like', $search)
                        ->orWhere('client_name', 'like', $search);
                });
            }

            $sort = $request->input('sort', 'newest');
            match ($sort) {
                'oldest' => $query->orderBy('created_at'),
                'highest_rating' => $query->orderByDesc('rating')->orderByDesc('created_at'),
                'lowest_rating' => $query->orderBy('rating')->orderByDesc('created_at'),
                default => $query->orderByDesc('created_at'),
            };
        } else {
            $query->orderByDesc('created_at');
        }

        return $query->get();
    }
}

