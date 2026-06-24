<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Reports</p>
          <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg">
            Operational <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Reports</span>
          </h1>
          <p class="mt-2 text-sm text-ivory/60">Overview of saloon performance, revenue, and operational metrics.</p>
        </div>
      </div>

      <!-- Revenue Card -->
      <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-admin/20 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-admin/40 hover:bg-black/40 hover:shadow-[0_10px_30px_rgba(255,103,0,0.12)]">
          <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-admin/10 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
              <div class="h-10 w-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                <BanknotesIcon class="h-5 w-5 text-emerald-400" />
              </div>
              <p class="text-sm text-ivory/60 font-medium">Total Revenue</p>
            </div>
            <p class="font-display text-3xl text-emerald-400">₦{{ Number(reports.revenue_estimate || 0).toLocaleString() }}</p>
            <p class="mt-2 text-xs text-ivory/40">Lifetime from completed appointments</p>
          </div>
        </div>

        <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-admin/30 hover:bg-black/40">
          <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
              <div class="h-10 w-10 rounded-xl bg-admin/10 border border-admin/20 flex items-center justify-center">
                <CalendarDaysIcon class="h-5 w-5 text-admin" />
              </div>
              <p class="text-sm text-ivory/60 font-medium">Today's Bookings</p>
            </div>
            <p class="font-display text-3xl text-admin-light">{{ stats.appointments_today || 0 }}</p>
            <p class="mt-2 text-xs text-ivory/40">Scheduled for today</p>
          </div>
        </div>

        <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-admin/30 hover:bg-black/40">
          <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
              <div class="h-10 w-10 rounded-xl bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center">
                <ClockIcon class="h-5 w-5 text-yellow-400" />
              </div>
              <p class="text-sm text-ivory/60 font-medium">Pending</p>
            </div>
            <p class="font-display text-3xl text-yellow-400">{{ stats.pending_appointments || 0 }}</p>
            <p class="mt-2 text-xs text-ivory/40">Awaiting confirmation</p>
          </div>
        </div>

        <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-admin/30 hover:bg-black/40">
          <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
              <div class="h-10 w-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
                <UsersIcon class="h-5 w-5 text-blue-400" />
              </div>
              <p class="text-sm text-ivory/60 font-medium">Active Barbers</p>
            </div>
            <p class="font-display text-3xl text-blue-400">{{ stats.barbers || 0 }}</p>
            <p class="mt-2 text-xs text-ivory/40">On your team</p>
          </div>
        </div>
      </div>

      <!-- Summary Placeholder -->
      <div class="rounded-2xl border border-white/5 bg-black/20 p-12 backdrop-blur-sm flex flex-col items-center justify-center text-center">
        <div class="relative mb-6">
          <div class="absolute inset-0 bg-admin/20 rounded-full blur-2xl"></div>
          <ChartBarIcon class="relative h-16 w-16 text-admin/40 drop-shadow-lg" />
        </div>
        <h2 class="font-display text-2xl text-theme-text mb-2">Advanced Analytics Coming Soon</h2>
        <p class="text-ivory/50 max-w-md">Detailed revenue breakdowns, booking trends, barber performance charts, and monthly reports are in development.</p>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { BanknotesIcon, CalendarDaysIcon, ClockIcon, UsersIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

const reports = ref({});
const stats = ref({});

onMounted(async () => {
  try {
    const response = await adminApi.dashboard();
    const data = response.data.data;
    reports.value = data.reports || {};
    stats.value = data.stats || {};
  } catch (err) {
    console.error('Failed to fetch reports:', err);
  }
});
</script>