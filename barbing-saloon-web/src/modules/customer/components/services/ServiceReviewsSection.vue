<template>
  <div class="rounded-3xl border border-theme-border bg-theme-surface/60 p-8 backdrop-blur-sm mt-8">
    <div class="flex items-center justify-between mb-8">
      <h2 class="text-2xl font-display text-theme-text">Customer Reviews</h2>
      <div v-if="reviews.length > 0" class="flex items-center gap-2">
        <div class="flex items-center text-gold">
          <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
          </svg>
        </div>
        <span class="text-theme-text font-bold text-lg">{{ averageRating }}</span>
        <span class="text-theme-muted text-sm">({{ reviews.length }} Reviews)</span>
      </div>
    </div>

    <div v-if="reviewsLoading" class="space-y-4 py-4">
      <div v-for="n in 2" :key="'review-skel-' + n" class="flex gap-3 rounded-2xl border border-theme-border bg-theme-surface/60 p-4">
        <div class="skeleton-block h-10 w-10 rounded-full shrink-0"></div>
        <div class="flex-1 space-y-2">
          <div class="skeleton-block h-4 w-32"></div>
          <div class="skeleton-block h-4 w-full"></div>
          <div class="skeleton-block h-4 w-2/3"></div>
        </div>
      </div>
    </div>

    <div v-else-if="reviews.length > 0" class="space-y-6">
      <!-- Review Item -->
      <div v-for="review in reviews" :key="review.id" class="border-b border-theme-border/50 pb-6 last:border-0 last:pb-0">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-gold to-amber-600 flex items-center justify-center text-obsidian font-bold text-lg">
              {{ review.customer_name ? review.customer_name.charAt(0).toUpperCase() : 'C' }}
            </div>
            <div>
              <p class="text-theme-text font-semibold text-sm">{{ review.customer_name || 'Anonymous Customer' }}</p>
              <p class="text-theme-muted text-xs">{{ new Date(review.created_at).toLocaleDateString() }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span v-if="review.barber_name" class="text-xs font-semibold text-theme-muted bg-theme-bg px-2 py-1 rounded-md border border-theme-border">
              Barber: <span class="text-gold">{{ review.barber_name }}</span>
            </span>
            <div class="flex items-center text-gold">
              <svg v-for="i in Number(review.rating)" :key="'fill-'+i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
              </svg>
              <svg v-for="i in (5 - Number(review.rating))" :key="'empty-'+i" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-theme-muted/30">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.536a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
              </svg>
            </div>
          </div>
        </div>
        <p class="text-theme-muted text-sm leading-relaxed">
          "{{ review.comment }}"
        </p>
      </div>
    </div>

    <div v-else class="text-center py-6">
      <p class="text-theme-muted text-sm italic">There are no reviews for this service yet. Be the first to book and review!</p>
    </div>

    <!-- See More Button -->
    <button v-if="reviews.length > 5" class="mt-6 w-full rounded-xl border border-gold/30 bg-gold/5 py-3 text-sm font-semibold text-gold transition-colors hover:bg-gold/10">
      Read All {{ reviews.length }} Reviews
    </button>
  </div>
</template>

<script setup>
defineProps({
  reviews: { type: Array, default: () => [] },
  reviewsLoading: { type: Boolean, default: false },
  averageRating: { type: [String, Number], default: '0.0' }
})
</script>
