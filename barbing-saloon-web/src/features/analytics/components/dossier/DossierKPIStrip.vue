<template>
  <section class="mb-5">
    <!-- Primary Executive Financial Summary (5 Columns) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
      <!-- 1. Realized Cash-in Revenue -->
      <div class="rounded-lg border border-gray-200 bg-gray-50/80 p-3 relative overflow-hidden">
        <div class="h-1 w-full bg-emerald-500 absolute top-0 left-0"></div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Realized Revenue</p>
        <p class="mt-1 text-base sm:text-lg font-black text-emerald-700 font-mono tracking-tight">
          ₦{{ formatCurrency(businessStats.realized_revenue || businessStats.total_revenue) }}
        </p>
        <p class="mt-0.5 text-[10px] text-gray-500 font-medium">Reconciled settlements</p>
      </div>

      <!-- 2. Completed Appointments -->
      <div class="rounded-lg border border-gray-200 bg-gray-50/80 p-3 relative overflow-hidden">
        <div class="h-1 w-full bg-sky-500 absolute top-0 left-0"></div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Fulfilled Appts</p>
        <p class="mt-1 text-base sm:text-lg font-black text-sky-700 font-mono tracking-tight">
          {{ Number(businessStats.completed_count || businessStats.total_appointments || 0).toLocaleString() }}
        </p>
        <p class="mt-0.5 text-[10px] text-gray-500 font-medium">Completed chair visits</p>
      </div>

      <!-- 3. Average Ticket Size -->
      <div class="rounded-lg border border-gray-200 bg-gray-50/80 p-3 relative overflow-hidden">
        <div class="h-1 w-full bg-amber-500 absolute top-0 left-0"></div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Average Ticket</p>
        <p class="mt-1 text-base sm:text-lg font-black text-amber-700 font-mono tracking-tight">
          ₦{{ formatCurrency(businessStats.average_ticket) }}
        </p>
        <p class="mt-0.5 text-[10px] text-gray-500 font-medium">Mean spend / customer</p>
      </div>

      <!-- 4. Client Base & Retention -->
      <div class="rounded-lg border border-gray-200 bg-gray-50/80 p-3 relative overflow-hidden">
        <div class="h-1 w-full bg-indigo-500 absolute top-0 left-0"></div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Client Scale</p>
        <p class="mt-1 text-base sm:text-lg font-black text-indigo-700 font-mono tracking-tight">
          {{ Number(businessStats.new_customers || 0).toLocaleString() }}
          <span class="text-xs font-normal text-gray-500">/ {{ Number(businessStats.total_customers || 0).toLocaleString() }}</span>
        </p>
        <p class="mt-0.5 text-[10px] text-gray-500 font-medium">New / Total client base</p>
      </div>

      <!-- 5. Staff Capacity & Client Rating -->
      <div class="col-span-2 sm:col-span-1 rounded-lg border border-gray-200 bg-gray-50/80 p-3 relative overflow-hidden">
        <div class="h-1 w-full bg-[#FF6700] absolute top-0 left-0"></div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Chairs & Rating</p>
        <p class="mt-1 text-base sm:text-lg font-black text-orange-600 font-mono tracking-tight flex items-center gap-1">
          <span>{{ platformStats.active_barbers || 0 }}</span>
          <span class="text-xs font-bold text-amber-500">★ {{ Number(platformStats.avg_rating || 5.0).toFixed(1) }}</span>
        </p>
        <p class="mt-0.5 text-[10px] text-gray-500 font-medium">Active master barbers</p>
      </div>
    </div>

    <!-- Secondary Reconciled Balance & Leakage Diagnostics Strip -->
    <div class="mt-2.5 rounded-lg border border-gray-200/80 bg-gray-100/60 p-2.5 flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
      <div class="flex items-center gap-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Gross Volume:</span>
        <span class="font-bold text-gray-800">₦{{ formatCurrency(businessStats.total_revenue) }}</span>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Pending Pipeline:</span>
        <span class="font-bold text-amber-700">₦{{ formatCurrency(businessStats.pending_revenue) }}</span>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Avoidable Leakage:</span>
        <span class="font-bold text-rose-700">₦{{ formatCurrency(businessStats.lost_revenue) }}</span>
        <span class="text-[10px] text-gray-400 font-sans">(Cancelled/No-show)</span>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Fulfillment Efficiency:</span>
        <span class="font-bold text-emerald-700">{{ businessStats.completion_rate || 100 }}%</span>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  businessStats: { type: Object, default: () => ({}) },
  platformStats: { type: Object, default: () => ({}) }
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};
</script>
