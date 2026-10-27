<template>
  <article 
    class="group relative overflow-hidden rounded-3xl border bg-theme-surface transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl flex flex-col"
    :class="[
      service.approval_status === 'pending' 
        ? 'border-amber-500/30 hover:border-amber-400/50' 
        : 'border-theme-border hover:border-admin/40'
    ]"
  >
    <!-- Glowing Top Accent Line -->
    <div 
      class="absolute top-0 left-0 right-0 h-1 z-30 transition-opacity"
      :class="[
        service.approval_status === 'pending'
          ? 'bg-amber-400 opacity-100'
          : 'bg-gradient-to-r from-admin via-amber-400 to-amber-200 opacity-0 group-hover:opacity-100'
      ]"
    ></div>

    <!-- Media Cover / Image Section -->
    <div class="relative w-full h-44 bg-theme-surface-subtle overflow-hidden shrink-0">
      <img 
        v-if="service.image || service.image_url"
        :src="service.image_url || service.image" 
        :alt="service.name" 
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" 
      />
      <!-- Luxury Placeholder if no image -->
      <div v-else class="w-full h-full flex flex-col items-center justify-center bg-theme-surface-subtle relative">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,103,0,0.06),transparent_70%)]"></div>
        <SparklesIcon class="h-10 w-10 text-theme-text-muted/30 group-hover:text-admin/30 transition-colors" />
        <span class="text-[10px] uppercase tracking-widest text-theme-text-muted/40 mt-1 font-mono">CandyCutz Signature</span>
      </div>

      <!-- Scrim gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-theme-surface via-theme-surface/40 to-transparent z-10 pointer-events-none"></div>

      <!-- Top Badges Overlay -->
      <div class="absolute top-3.5 left-3.5 z-20 flex flex-wrap items-center gap-1.5">
        <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider border border-admin/30 bg-theme-surface/90 text-admin backdrop-blur-md shadow-sm">
          <TagIcon class="h-3 w-3" />
          {{ service.category_name || service.category || 'General' }}
        </span>

        <span 
          v-if="service.is_home_service_eligible" 
          class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[10px] font-bold uppercase tracking-wider border border-sky-500/30 bg-theme-surface/90 text-sky-500 dark:text-sky-400 backdrop-blur-md shadow-sm"
          title="Home service visit allowed"
        >
          <HomeIcon class="h-3 w-3" />
          VIP House Call
        </span>
      </div>

      <!-- Floating Top-Right Action Controls -->
      <div class="absolute top-3.5 right-3.5 z-20 flex items-center gap-1.5 bg-theme-surface/80 backdrop-blur-md rounded-xl p-1 border border-theme-border opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
        <router-link 
          :to="`/admin/services/${service.id}/edit`" 
          class="p-1.5 rounded-lg text-theme-text-muted hover:text-theme-text hover:bg-theme-surface-subtle transition-colors" 
          title="Edit Service Details"
        >
          <PencilSquareIcon class="h-4 w-4" />
        </router-link>
        <button 
          @click="$emit('delete', service)" 
          class="p-1.5 rounded-lg text-theme-text-muted hover:text-red-500 hover:bg-red-500/10 transition-colors" 
          title="Delete Service"
        >
          <TrashIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Card Content Body -->
    <div class="relative z-20 flex flex-col flex-1 p-5 pt-3">
      <!-- Barber Creator Tag -->
      <div v-if="service.barber" class="mb-2 flex items-center gap-2 text-xs">
        <span class="text-theme-text-muted">Created by barber:</span>
        <span class="inline-flex items-center gap-1 font-semibold text-amber-600 dark:text-amber-400/95 bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">
          <UserIcon class="h-3 w-3" />
          {{ service.barber.name }}
        </span>
      </div>

      <!-- Service Title -->
      <h2 class="font-display text-xl text-theme-text group-hover:text-admin transition-colors leading-tight drop-shadow-sm mb-1.5">
        {{ service.name }}
      </h2>

      <!-- Description -->
      <p v-if="service.description" class="text-xs text-theme-text-muted line-clamp-2 mb-4 leading-relaxed">
        {{ service.description }}
      </p>
      <p v-else class="text-xs text-theme-text-muted/50 italic mb-4">
        No description provided.
      </p>

      <!-- Status Indicators & Quick Toggle -->
      <div class="mt-auto mb-4 flex items-center justify-between gap-2 flex-wrap pt-2">
        <div class="flex items-center gap-2 flex-wrap">
          <!-- Approval Status Pill -->
          <span
            class="rounded-full px-2.5 py-0.8 text-[10px] font-bold uppercase tracking-wider border flex items-center gap-1.5"
            :class="{
              'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30': service.approval_status === 'pending',
              'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30': service.approval_status === 'approved' || !service.approval_status,
              'bg-rose-500/15 text-rose-600 dark:text-rose-400 border-rose-500/30': service.approval_status === 'rejected'
            }"
          >
            <span class="h-1.5 w-1.5 rounded-full" :class="{
              'bg-amber-400 animate-pulse': service.approval_status === 'pending',
              'bg-emerald-400': service.approval_status === 'approved' || !service.approval_status,
              'bg-rose-400': service.approval_status === 'rejected'
            }"></span>
            {{ service.approval_status === 'pending' ? 'Pending Review' : (service.approval_status === 'rejected' ? 'Rejected' : 'Approved') }}
          </span>

          <!-- Active Status Pill -->
          <span
            class="rounded-full px-2 py-0.8 text-[10px] font-bold uppercase tracking-wider border"
            :class="service.is_active
              ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
              : 'bg-theme-surface-subtle text-theme-text-muted border-theme-border'"
          >
            {{ service.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <!-- Inline Active/Inactive Toggle Button -->
        <button
          @click="$emit('toggle-active', service)"
          :disabled="togglingId === service.id"
          class="text-[11px] font-semibold px-2.5 py-1 rounded-lg border transition-all flex items-center gap-1"
          :class="service.is_active 
            ? 'text-theme-text-muted border-theme-border hover:bg-theme-surface-subtle hover:text-theme-text' 
            : 'text-emerald-600 dark:text-emerald-400 border-emerald-500/30 bg-emerald-500/10 hover:bg-emerald-500/20'"
          :title="service.is_active ? 'Click to deactivate' : 'Click to activate'"
        >
          <span v-if="togglingId === service.id" class="h-3 w-3 animate-spin rounded-full border-2 border-theme-border border-t-admin"></span>
          <span>{{ service.is_active ? 'Deactivate' : 'Publish' }}</span>
        </button>
      </div>

      <!-- Admin Actions for Pending Submissions -->
      <div v-if="service.approval_status === 'pending'" class="mb-4 pt-3 border-t border-theme-border flex items-center gap-2">
        <button
          @click="$emit('approve', service)"
          :disabled="approvingId === service.id"
          class="flex-1 py-2 px-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-obsidian text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-[0_0_15px_rgba(16,185,129,0.3)] disabled:opacity-50 active:scale-[0.98]"
        >
          <CheckIcon class="h-4 w-4" />
          <span>{{ approvingId === service.id ? 'Approving...' : 'Approve Service' }}</span>
        </button>
        <button
          @click="$emit('reject', service)"
          :disabled="approvingId === service.id"
          class="py-2 px-3 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-500 dark:text-red-400 border border-red-500/30 text-xs font-bold transition-all flex items-center justify-center gap-1 disabled:opacity-50 active:scale-[0.98]"
        >
          <XMarkIcon class="h-4 w-4" />
          <span>Reject</span>
        </button>
      </div>

      <!-- Price & Duration Footer -->
      <div class="pt-3.5 flex items-center justify-between border-t border-theme-border">
        <div class="flex items-baseline gap-1 text-emerald-600 dark:text-emerald-400 font-display">
          <span class="text-xs opacity-60">₦</span>
          <span class="text-2xl font-bold tracking-tight">{{ Number(service.price).toLocaleString() }}</span>
        </div>
        
        <div class="flex items-center gap-2">
          <div class="flex items-center gap-1.5 text-xs font-bold text-theme-text-muted bg-theme-surface-subtle px-2.5 py-1.5 rounded-lg border border-theme-border">
            <ClockIcon class="h-3.5 w-3.5 text-admin" />
            <span>{{ service.duration_minutes }} mins</span>
          </div>

          <router-link 
            :to="`/admin/services/${service.id}/edit`" 
            class="flex items-center justify-center h-8 w-8 rounded-lg bg-theme-surface-subtle border border-theme-border text-theme-text-muted hover:text-theme-text hover:bg-theme-border/20 hover:border-admin/40 transition-all"
            title="Edit"
          >
            <PencilSquareIcon class="h-4 w-4" />
          </router-link>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup>
import { 
  SparklesIcon,
  TagIcon,
  HomeIcon,
  UserIcon,
  ClockIcon,
  CheckIcon,
  XMarkIcon,
  PencilSquareIcon,
  TrashIcon
} from '@heroicons/vue/24/outline';

defineProps({
  service: {
    type: Object,
    required: true
  },
  approvingId: {
    type: [Number, String],
    default: null
  },
  togglingId: {
    type: [Number, String],
    default: null
  }
});

defineEmits(['approve', 'reject', 'toggle-active', 'delete']);
</script>
