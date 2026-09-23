<template>
  <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
    <!-- Revenue -->
    <div class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-admin/30">
      <div class="flex items-center gap-3 mb-4">
        <BanknotesIcon class="h-5 w-5 text-emerald-400" />
        <p class="text-sm text-ivory/60 font-medium">Total Revenue</p>
      </div>
      <p class="font-display text-3xl text-emerald-400 tabular-nums">₦{{ formatCurrency(businessStats.total_revenue) }}</p>
      <p class="mt-2 text-xs text-ivory/40">From completed bookings</p>
    </div>

    <!-- Appointments -->
    <div class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-admin/30">
      <div class="flex items-center gap-3 mb-4">
        <CalendarDaysIcon class="h-5 w-5 text-admin" />
        <p class="text-sm text-ivory/60 font-medium">Appointments</p>
      </div>
      <p class="font-display text-3xl text-admin-light tabular-nums">{{ businessStats.total_appointments || 0 }}</p>
      <p class="mt-2 text-xs text-ivory/40">Successfully completed</p>
    </div>

    <!-- Customers -->
    <div class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-admin/30">
      <div class="flex items-center gap-3 mb-4">
        <UsersIcon class="h-5 w-5 text-blue-400" />
        <p class="text-sm text-ivory/60 font-medium">New Customers</p>
      </div>
      <p class="font-display text-3xl text-blue-400 tabular-nums">{{ businessStats.new_customers || 0 }}</p>
      <p class="mt-2 text-xs text-ivory/40">Registered in period</p>
    </div>

    <!-- Total Customers -->
    <div class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-admin/30">
      <div class="flex items-center gap-3 mb-4">
        <UserGroupIcon class="h-5 w-5 text-sky-400" />
        <p class="text-sm text-ivory/60 font-medium">Total Customers</p>
      </div>
      <p class="font-display text-3xl text-sky-400 tabular-nums">{{ businessStats.total_customers || 0 }}</p>
      <p class="mt-2 text-xs text-ivory/40">Registered all time</p>
    </div>

    <!-- Platform -->
    <div class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-admin/30">
      <div class="flex items-center gap-3 mb-4">
        <ChartBarIcon class="h-5 w-5 text-purple-400" />
        <p class="text-sm text-ivory/60 font-medium">Blog Posts</p>
      </div>
      <p class="font-display text-3xl text-purple-400 tabular-nums">{{ platformStats.total_blog_posts || 0 }}</p>
      <p class="mt-2 text-xs text-ivory/40">Total published</p>
    </div>
  </div>
</template>

<script setup>
import { 
  BanknotesIcon, CalendarDaysIcon, UsersIcon, 
  ChartBarIcon, UserGroupIcon
} from '@heroicons/vue/24/outline';

defineProps({
  businessStats: { type: Object, required: true },
  platformStats: { type: Object, required: true }
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(value || 0));
};
</script>
