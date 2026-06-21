<?php

namespace App\Modules\Barber\Controllers;

use App\Core\Http\Response\ApiResponse;
use App\Models\Appointment;
use App\Modules\Barber\Requests\UpdateBarberProfileRequest;
use App\Modules\Barber\Resources\BarberAppointmentResource;
use App\Modules\Barber\Resources\BarberProfileResource;
use App\Modules\Barber\Services\BarberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BarberController
{
    public function __construct(protected BarberService $barberService)
    {
    }

    public function dashboard(Request $request)
    {
        return ApiResponse::success($this->barberService->dashboard($request->user()), 'Barber dashboard loaded');
    }

    public function schedule(Request $request)
    {
        return ApiResponse::success($this->barberService->schedule($request->user()), 'Schedule loaded');
    }

    public function appointments(Request $request)
    {
        return ApiResponse::paginated($this->barberService->appointments($request->user()), 'Appointments loaded');
    }

    public function complete(Request $request, Appointment $appointment)
    {
        Gate::authorize('complete', $appointment);

        return ApiResponse::success(new BarberAppointmentResource($this->barberService->complete($appointment)), 'Appointment completed');
    }

    public function noShow(Request $request, Appointment $appointment)
    {
        Gate::authorize('markNoShow', $appointment);

        return ApiResponse::success(new BarberAppointmentResource($this->barberService->noShow($appointment)), 'Appointment marked as no-show');
    }

    public function profile(Request $request)
    {
        return ApiResponse::success(new BarberProfileResource($this->barberService->profile($request->user())), 'Barber profile loaded');
    }

    public function updateProfile(UpdateBarberProfileRequest $request)
    {
        return ApiResponse::success(new BarberProfileResource($this->barberService->updateProfile($request->user(), $request->validated())), 'Barber profile updated');
    }
}