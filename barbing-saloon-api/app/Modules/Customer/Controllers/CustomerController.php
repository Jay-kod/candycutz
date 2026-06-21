<?php

namespace App\Modules\Customer\Controllers;

use App\Core\Http\Response\ApiResponse;
use App\Models\Appointment;
use App\Modules\Customer\Requests\StoreBookingRequest;
use App\Modules\Customer\Requests\StoreReviewRequest;
use App\Modules\Customer\Requests\UpdateProfileRequest;
use App\Modules\Customer\Resources\AppointmentResource;
use App\Modules\Customer\Resources\TestimonialResource;
use App\Modules\Customer\Resources\UserProfileResource;
use App\Modules\Customer\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerController
{
    public function __construct(protected CustomerService $customerService)
    {
    }

    public function dashboard(Request $request)
    {
        return ApiResponse::success($this->customerService->dashboard($request->user()), 'Customer dashboard loaded');
    }

    public function bookings(Request $request)
    {
        return ApiResponse::paginated($this->customerService->bookings($request->user()), 'Bookings loaded');
    }

    public function storeBooking(StoreBookingRequest $request)
    {
        $appointment = $this->customerService->createBooking($request->user(), $request->validated());

        return ApiResponse::success(new AppointmentResource($appointment->load(['service', 'barber.user'])), 'Booking created', 201);
    }

    public function showBooking(Request $request, Appointment $appointment)
    {
        Gate::authorize('view', $appointment);

        return ApiResponse::success(new AppointmentResource($appointment->load(['service', 'barber.user'])), 'Booking loaded');
    }

    public function cancelBooking(Request $request, Appointment $appointment)
    {
        Gate::authorize('cancel', $appointment);

        $cancelled = $this->customerService->cancelBooking($appointment);

        return ApiResponse::success(new AppointmentResource($cancelled), 'Booking cancelled');
    }

    public function profile(Request $request)
    {
        return ApiResponse::success([
            'profile' => new UserProfileResource($request->user()->loadCount(['appointments', 'testimonials'])),
            'history' => $this->customerService->profile($request->user()),
        ], 'Profile loaded');
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $this->customerService->updateProfile($request->user(), $request->validated(), $request->file('avatar'));

        return ApiResponse::success(new UserProfileResource($user->loadCount(['appointments', 'testimonials'])), 'Profile updated');
    }

    public function reviews(Request $request)
    {
        return ApiResponse::paginated($this->customerService->reviews($request->user()), 'Reviews loaded');
    }

    public function storeReview(StoreReviewRequest $request)
    {
        $testimonial = $this->customerService->createReview($request->user(), $request->validated());

        return ApiResponse::success(new TestimonialResource($testimonial->load(['service', 'barber.user'])), 'Review submitted', 201);
    }
}