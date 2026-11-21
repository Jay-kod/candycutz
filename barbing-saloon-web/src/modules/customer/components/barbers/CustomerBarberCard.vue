<template>
  <article
    class="group relative rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 shadow-xl transition-all duration-300 hover:border-gold/40 hover:-translate-y-1 hover:shadow-xl cursor-pointer flex flex-col justify-between"
    data-test="barber-card"
    @click="$emit('click', barber.id)"
  >
    <div>
      <!-- Top Section: Avatar & Status & Experience -->
      <div class="flex items-start gap-4 mb-4">
        <!-- Avatar Portrait -->
        <div class="relative h-20 w-20 rounded-2xl overflow-hidden border border-gold/30 bg-amber-50 dark:bg-[#161B22] shrink-0 shadow-lg group-hover:scale-105 transition-transform duration-300">
          <img
            v-if="barber.avatar || barber.avatar_url"
            :src="resolveImageUrl(barber.avatar || barber.avatar_url)"
            :alt="barber.name"
            class="h-full w-full object-cover"
          />
          <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-gold/20 to-amber-900/30 text-gold text-xl font-display font-bold">
            {{ getInitials(barber.name) }}
          </div>

          <!-- Status Dot Badge overlay -->
          <span
            class="absolute bottom-1 right-1 h-3.5 w-3.5 rounded-full border-2 border-white dark:border-[#12161C]"
            :class="barber.is_available && (barber.chair_status === 'free' || !barber.chair_status)
              ? 'bg-emerald-400'
              : 'bg-amber-400'"
            :title="barber.is_available ? 'Available' : 'Busy'"
          ></span>
        </div>

        <!-- Name & Title & Rating -->
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-1.5 mb-0.5">
            <span
              class="inline-flex items-center gap-1 text-[9px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-md"
              :class="barber.is_available && (barber.chair_status === 'free' || !barber.chair_status)
                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'
                : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'"
            >
              <span class="h-1.5 w-1.5 rounded-full" :class="barber.is_available ? 'bg-emerald-500 dark:bg-emerald-400 animate-pulse' : 'bg-amber-500 dark:bg-amber-400'"></span>
              <span>{{ barber.is_available ? 'Available' : 'At Chair' }}</span>
            </span>
            
            <span v-if="barber.experience_years" class="text-[9px] uppercase tracking-wider text-slate-600 dark:text-slate-400 font-bold bg-slate-100 dark:bg-white/[0.04] px-1.5 py-0.5 rounded border border-slate-200 dark:border-white/[0.06]">
              {{ barber.experience_years }}+ Yrs
            </span>
          </div>

          <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white truncate group-hover:text-gold transition-colors">
            {{ barber.name }}
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 truncate font-medium">Master Stylist</p>

          <!-- Rating -->
          <div class="flex items-center gap-1.5 mt-1 text-xs text-amber-500 dark:text-amber-400">
            <div class="flex items-center">
              <StarIcon class="h-3.5 w-3.5 fill-current text-amber-400" />
            </div>
            <span class="font-bold text-slate-900 dark:text-white text-[11px] font-mono">{{ Number(barber.rating || 5.0).toFixed(1) }}</span>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">({{ barber.total_reviews || 12 }} cuts)</span>
          </div>
        </div>
      </div>

      <!-- Bio Excerpt -->
      <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed mb-4">
        {{ barber.bio || 'Specializing in signature scissor fades, luxury beard sculpts, and bespoke grooming rituals.' }}
      </p>

      <!-- Specialties Chips -->
      <div v-if="barber.specialties && barber.specialties.length" class="flex flex-wrap gap-1.5 mb-6">
        <span
          v-for="spec in barber.specialties.slice(0, 3)"
          :key="spec"
          class="rounded-lg border border-slate-200 dark:border-white/[0.06] bg-slate-100 dark:bg-white/[0.03] px-2.5 py-1 text-[10px] font-semibold text-slate-700 dark:text-slate-300"
        >
          {{ spec }}
        </span>
        <span
          v-if="barber.specialties.length > 3"
          class="rounded-lg border border-slate-200 dark:border-white/[0.06] bg-slate-100 dark:bg-white/[0.03] px-2 py-1 text-[10px] font-mono text-slate-600 dark:text-slate-400 font-bold"
        >
          +{{ barber.specialties.length - 3 }}
        </span>
      </div>
    </div>

    <!-- Bottom Actions -->
    <div class="pt-4 border-t border-slate-100 dark:border-white/[0.06] flex items-center gap-3" @click.stop>
      <RouterLink
        :to="`/customer/dashboard/barber/${barber.id}`"
        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-gold/30 bg-gold/10 py-2.5 text-xs font-bold text-gold hover:bg-gold hover:text-obsidian transition-all shadow-sm"
        data-test="view-profile-btn"
      >
        <EyeIcon class="h-3.5 w-3.5" />
        <span>View Profile</span>
      </RouterLink>

      <RouterLink
        :to="{ path: '/customer/dashboard/services', query: { barber_id: barber.id } }"
        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-gold to-amber-500 py-2.5 text-xs font-bold text-obsidian shadow-md shadow-gold/20 hover:brightness-110 active:scale-95 transition-all"
        data-test="book-chair-btn"
      >
        <ScissorsIcon class="h-3.5 w-3.5" />
        <span>Book Chair</span>
      </RouterLink>
    </div>
  </article>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { getStorageUrl } from '@/core/utils/url';
import { StarIcon, EyeIcon, ScissorsIcon } from '@heroicons/vue/24/outline';

defineProps({
  barber: {
    type: Object,
    required: true,
  },
});

defineEmits(['click']);

const resolveImageUrl = (path) => getStorageUrl(path);

const getInitials = (name) => {
  if (!name) return 'CC';
  return name
    .split(' ')
    .filter(Boolean)
    .map((w) => w[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
};
</script>

