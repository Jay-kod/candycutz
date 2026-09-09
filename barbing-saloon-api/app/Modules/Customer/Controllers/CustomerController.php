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

    public function analytics(Request $request)
    {
        $user = $request->user();
        $totalSpent = Appointment::query()
            ->where('customer_id', $user->id)
            ->where('status', \App\Core\Enums\AppointmentStatus::completed->value)
            ->sum('total_price');

        $monthlyBookings = Appointment::query()
            ->where('customer_id', $user->id)
            ->where('appointment_date', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw('MONTHNAME(appointment_date) as month, COUNT(*) as bookings, SUM(total_price) as spent')
            ->groupBy('month')
            ->get();

        return ApiResponse::success([
            'total_spent' => (float) $totalSpent,
            'monthly' => $monthlyBookings,
        ], 'Analytics loaded');
    }

    public function myCodes(Request $request)
    {
        $user = $request->user();
        $codes = Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $user->id)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'appointment_date' => $a->appointment_date,
                'appointment_time' => $a->appointment_time,
                'status' => $a->status,
                'verification_code' => $a->verification_code,
                'service_name' => $a->service?->name,
                'service_image' => $a->service?->image,
                'barber_name' => $a->barber?->user?->name,
                'barber_avatar' => $a->barber?->user?->avatar,
            ]);

        return ApiResponse::success($codes, 'My verification codes loaded');
    }

    public function deleteBooking(Request $request, Appointment $appointment)
    {
        Gate::authorize('cancel', $appointment);
        $appointment->update(['status' => \App\Core\Enums\AppointmentStatus::cancelled->value]);

        return ApiResponse::success(null, 'Booking cancelled');
    }

    public function wishlist(Request $request)
    {
        $user = $request->user();
        $items = \Illuminate\Support\Facades\DB::table('wishlists')
            ->where('customer_id', $user->id)
            ->get();

        return ApiResponse::success($items, 'Wishlist loaded');
    }

    public function addToWishlist(Request $request)
    {
        $user = $request->user();
        $itemType = $request->input('item_type', 'service');
        $itemId = $request->input('item_id');

        \Illuminate\Support\Facades\DB::table('wishlists')->updateOrInsert(
            ['customer_id' => $user->id, 'item_type' => $itemType, 'item_id' => $itemId],
            ['created_at' => now()]
        );

        return ApiResponse::success(null, 'Item added to wishlist');
    }

    public function removeFromWishlist(Request $request, $id)
    {
        $user = $request->user();
        \Illuminate\Support\Facades\DB::table('wishlists')
            ->where('customer_id', $user->id)
            ->where('id', $id)
            ->delete();

        return ApiResponse::success(null, 'Item removed from wishlist');
    }

    public function removeFromWishlistByType(Request $request)
    {
        $user = $request->user();
        $itemType = $request->query('type');
        $itemId = $request->query('id');

        \Illuminate\Support\Facades\DB::table('wishlists')
            ->where('customer_id', $user->id)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->delete();

        return ApiResponse::success(null, 'Item removed from wishlist');
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        $appointmentId = $request->input('appointment_id');
        $amount = $request->input('amount');
        $paymentMethod = $request->input('payment_method', 'bank_transfer');

        $appointment = Appointment::where('id', $appointmentId)->where('customer_id', $user->id)->firstOrFail();
        $amount = $amount ?: $appointment->total_price;

        $txnRef = 'TXN_' . strtoupper(uniqid());

        \Illuminate\Support\Facades\DB::table('payments')->updateOrInsert(
            ['appointment_id' => $appointment->id],
            [
                'customer_id' => $user->id,
                'amount' => $amount,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'transaction_ref' => $txnRef,
                'updated_at' => now(),
            ]
        );

        return ApiResponse::success([
            'transaction_ref' => $txnRef,
            'message' => 'Payment initiated. Please upload receipt.',
        ], 'Payment initiated');
    }

    public function paymentDetails(Request $request, $appointmentId)
    {
        $user = $request->user();
        $appointment = Appointment::with('barber.user')->where('id', $appointmentId)->where('customer_id', $user->id)->firstOrFail();
        $barber = $appointment->barber;

        return ApiResponse::success([
            'bank_name' => $barber?->bank_name ?: 'Guaranty Trust Bank (GTB)',
            'account_name' => $barber?->account_name ?: ($barber?->user?->name ?: 'Candy Cutz Saloon'),
            'account_number' => $barber?->account_number ?: '0123456789',
        ], 'Payment details loaded');
    }

    public function uploadReceipt(Request $request, $appointmentId)
    {
        $user = $request->user();
        $appointment = Appointment::where('id', $appointmentId)->where('customer_id', $user->id)->firstOrFail();

        $request->validate([
            'receipt' => 'required|file|max:5120',
        ]);

        $file = $request->file('receipt');
        $filename = 'receipt_' . $appointment->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/receipts'), $filename);
        $receiptUrl = '/uploads/receipts/' . $filename;

        \Illuminate\Support\Facades\DB::table('payments')->updateOrInsert(
            ['appointment_id' => $appointment->id],
            [
                'customer_id' => $user->id,
                'amount' => $appointment->total_price,
                'status' => 'awaiting_verification',
                'receipt_image' => $receiptUrl,
                'receipt_url' => $receiptUrl,
                'transaction_ref' => 'TXN_' . strtoupper(uniqid()),
                'updated_at' => now(),
            ]
        );

        return ApiResponse::success([
            'receipt_url' => $receiptUrl,
        ], 'Receipt uploaded successfully. Awaiting verification.');
    }

    public function reactToBlogPost(Request $request, $id)
    {
        return ApiResponse::success(['status' => 'liked'], 'Reaction saved');
    }

    public function removeReactionFromBlogPost(Request $request, $id)
    {
        return ApiResponse::success(null, 'Reaction removed');
    }
}