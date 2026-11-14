<template>
  <section class="mb-5 page-break-avoid">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Left: Staff Productivity Audit -->
      <div class="rounded-lg border border-gray-200 bg-white p-3.5">
        <div class="flex items-center justify-between mb-2.5">
          <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 flex items-center gap-1.5">
            <span class="h-2 w-2 rounded-full bg-sky-500"></span>
            Staff Productivity & Revenue Share
          </h4>
          <span class="text-[10px] font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">{{ topBarbers.length }} Master Barbers</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs font-mono">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50 text-[10px] font-bold uppercase tracking-wider text-gray-600">
                <th class="py-1.5 px-2">Master Barber</th>
                <th class="py-1.5 px-2 text-center">Appts</th>
                <th class="py-1.5 px-2 text-center">Rating</th>
                <th class="py-1.5 px-2 text-right">Revenue (₦)</th>
                <th class="py-1.5 px-2 text-right">Share</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(b, idx) in topBarbers" :key="b.id || idx" class="hover:bg-gray-50/50">
                <td class="py-1.5 px-2 font-medium text-gray-900 truncate max-w-[130px]">
                  <div class="flex items-center gap-1.5">
                    <span
                      class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[10px] font-bold text-white"
                      :class="idx === 0 ? 'bg-[#FF6700]' : idx === 1 ? 'bg-gray-600' : 'bg-gray-400'"
                    >
                      {{ idx + 1 }}
                    </span>
                    <span class="font-sans font-semibold text-gray-900 truncate text-[11px]">{{ b.name }}</span>
                  </div>
                </td>
                <td class="py-1.5 px-2 text-center font-bold text-gray-800">
                  {{ b.completed_appointments || b.bookings || b.count || 0 }}
                </td>
                <td class="py-1.5 px-2 text-center text-amber-600 text-[11px] font-bold">
                  ★{{ Number(b.rating || 5.0).toFixed(1) }}
                </td>
                <td class="py-1.5 px-2 text-right font-bold text-emerald-700">
                  ₦{{ formatCurrency(b.revenue) }}
                </td>
                <td class="py-1.5 px-2 text-right font-semibold text-gray-600">
                  <div class="inline-flex items-center gap-1 justify-end w-full">
                    <span>{{ computeBarberShare(b.revenue) }}%</span>
                    <div class="w-8 h-1 rounded-full bg-gray-100 overflow-hidden shrink-0">
                      <div class="h-full bg-sky-500" :style="{ width: `${computeBarberShare(b.revenue)}%` }"></div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="topBarbers.length === 0" class="py-3 text-center text-xs text-gray-400 font-sans">
            No barber throughput data for period.
          </div>
        </div>
      </div>

      <!-- Right: Service Catalogue Demand & Yield -->
      <div class="rounded-lg border border-gray-200 bg-white p-3.5">
        <div class="flex items-center justify-between mb-2.5">
          <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 flex items-center gap-1.5">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            Catalogue Demand & Gross Yield
          </h4>
          <span class="text-[10px] font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">{{ topServices.length }} Top Services</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs font-mono">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50 text-[10px] font-bold uppercase tracking-wider text-gray-600">
                <th class="py-1.5 px-2">Service Name</th>
                <th class="py-1.5 px-2 text-center">Category</th>
                <th class="py-1.5 px-2 text-center">Bookings</th>
                <th class="py-1.5 px-2 text-right">Yield (₦)</th>
                <th class="py-1.5 px-2 text-right">Share</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(s, idx) in topServices" :key="s.id || idx" class="hover:bg-gray-50/50">
                <td class="py-1.5 px-2 font-medium text-gray-900 truncate max-w-[130px]">
                  <div class="flex items-center gap-1.5">
                    <span
                      class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-[10px] font-bold text-white"
                      :class="idx === 0 ? 'bg-emerald-600' : idx === 1 ? 'bg-gray-600' : 'bg-gray-400'"
                    >
                      {{ idx + 1 }}
                    </span>
                    <span class="font-sans font-semibold text-gray-900 truncate text-[11px]">{{ s.name }}</span>
                  </div>
                </td>
                <td class="py-1.5 px-2 text-center text-[10px]">
                  <span class="px-1.5 py-0.2 rounded bg-gray-100 text-gray-600 border border-gray-200 font-sans">
                    {{ s.category || 'Standard' }}
                  </span>
                </td>
                <td class="py-1.5 px-2 text-center font-bold text-gray-800">
                  {{ s.count || s.bookings || 0 }}
                </td>
                <td class="py-1.5 px-2 text-right font-bold text-emerald-700">
                  ₦{{ formatCurrency(s.revenue) }}
                </td>
                <td class="py-1.5 px-2 text-right font-semibold text-gray-600">
                  <div class="inline-flex items-center gap-1 justify-end w-full">
                    <span>{{ computeServiceShare(s.revenue) }}%</span>
                    <div class="w-8 h-1 rounded-full bg-gray-100 overflow-hidden shrink-0">
                      <div class="h-full bg-emerald-500" :style="{ width: `${computeServiceShare(s.revenue)}%` }"></div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="topServices.length === 0" class="py-3 text-center text-xs text-gray-400 font-sans">
            No service catalog records for period.
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  topBarbers: { type: Array, default: () => [] },
  topServices: { type: Array, default: () => [] },
  totalRevenue: { type: [Number, String], default: 0 }
});

const totalBarberRevenue = computed(() => {
  return props.topBarbers.reduce((sum, b) => sum + Number(b.revenue || 0), 0);
});

const totalServicesRevenue = computed(() => {
  return props.topServices.reduce((sum, s) => sum + Number(s.revenue || 0), 0);
});

const computeBarberShare = (revenue) => {
  const tot = totalBarberRevenue.value || Number(props.totalRevenue || 0);
  if (!tot) return 0;
  return Math.min(Math.round((Number(revenue || 0) / tot) * 100), 100);
};

const computeServiceShare = (revenue) => {
  const tot = totalServicesRevenue.value || Number(props.totalRevenue || 0);
  if (!tot) return 0;
  return Math.min(Math.round((Number(revenue || 0) / tot) * 100), 100);
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};
</script>
