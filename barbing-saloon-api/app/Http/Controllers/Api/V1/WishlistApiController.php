<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishlistApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = DB::table('wishlists')
            ->where('customer_id', $user->id)
            ->get();

        return ApiResponse::success($items, 'Wishlist loaded');
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $itemType = $request->input('item_type', 'service');
        $itemId = $request->input('item_id');

        DB::table('wishlists')->updateOrInsert(
            ['customer_id' => $user->id, 'item_type' => $itemType, 'item_id' => $itemId],
            ['created_at' => now()]
        );

        return ApiResponse::success(null, 'Item added to wishlist');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        DB::table('wishlists')
            ->where('customer_id', $user->id)
            ->where('id', $id)
            ->delete();

        return ApiResponse::success(null, 'Item removed from wishlist');
    }

    public function destroyByType(Request $request): JsonResponse
    {
        $user = $request->user();
        $itemType = $request->query('type');
        $itemId = $request->query('id');

        DB::table('wishlists')
            ->where('customer_id', $user->id)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->delete();

        return ApiResponse::success(null, 'Item removed from wishlist');
    }
}
