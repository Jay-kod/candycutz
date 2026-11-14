<template>
  <form class="group/form rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-xl transition-all duration-300 hover:border-amber-400/40 dark:hover:border-white/20" @submit.prevent="$emit('submit')">
    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-white/[0.06]">
      <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)]">
        <PencilSquareIcon class="h-5 w-5" />
      </div>
      <div>
        <h2 class="font-display text-xl text-slate-900 dark:text-white">Write a <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-200">Review</span></h2>
        <p class="text-xs text-[var(--color-text-secondary)] mt-0.5">Let your barber know how they did</p>
      </div>
    </div>

    <div class="space-y-4">
      <!-- Barber Select -->
      <div class="space-y-1.5">
        <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">
          <UserIcon class="h-3.5 w-3.5 text-[var(--color-accent)]" />
          Barber
        </label>
        <div class="relative">
          <select v-model="form.barber_id" class="w-full appearance-none rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-3 pr-10 text-slate-900 dark:text-white text-sm outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40">
            <option value="" class="bg-white dark:bg-[#12161C] text-slate-500 dark:text-white/50">Select barber</option>
            <option v-for="barber in barbers" :key="barber.id" :value="barber.id" class="bg-white dark:bg-[#12161C] text-slate-900 dark:text-white">{{ barber.name }}</option>
          </select>
          <ChevronDownIcon class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-white/40" />
        </div>
      </div>

      <!-- Service Select -->
      <div class="space-y-1.5">
        <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">
          <ScissorsIcon class="h-3.5 w-3.5 text-[var(--color-accent)]" />
          Service
        </label>
        <div class="relative">
          <select v-model="form.service_id" class="w-full appearance-none rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-3 pr-10 text-slate-900 dark:text-white text-sm outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40">
            <option value="" class="bg-white dark:bg-[#12161C] text-slate-500 dark:text-white/50">Select service</option>
            <option v-for="service in services" :key="service.id" :value="service.id" class="bg-white dark:bg-[#12161C] text-slate-900 dark:text-white">{{ service.name }}</option>
          </select>
          <ChevronDownIcon class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-white/40" />
        </div>
      </div>

      <!-- Star Rating -->
      <div class="space-y-1.5">
        <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">
          <StarIcon class="h-3.5 w-3.5 text-[var(--color-accent)]" />
          Rating
        </label>
        <div class="flex items-center gap-1 p-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.03] w-fit">
          <button 
            v-for="star in 5" 
            :key="star" 
            type="button" 
            @click="form.rating = star"
            @mouseenter="hoveredStar = star"
            @mouseleave="hoveredStar = 0"
            class="group/star relative p-1 transition-transform duration-200"
            :class="{ 'scale-125': hoveredStar === star }"
          >
            <div 
              v-if="star <= (hoveredStar || form.rating)" 
              class="absolute inset-0 rounded-full bg-[var(--color-accent)]/20 blur-md"
            ></div>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="relative h-7 w-7 transition-all duration-200" :class="star <= (hoveredStar || form.rating) ? 'text-[var(--color-accent)] drop-shadow-[0_0_6px_rgba(255,103,0,0.5)]' : 'text-slate-300 dark:text-white/20 group-hover/star:text-amber-400'">
              <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
            </svg>
          </button>
          <span class="ml-3 text-xs font-bold" :class="form.rating >= 4 ? 'text-[var(--color-accent)]' : form.rating >= 3 ? 'text-amber-500 dark:text-amber-400' : 'text-slate-400 dark:text-white/40'">
            {{ ratingLabels[form.rating - 1] }}
          </span>
        </div>
      </div>

      <!-- Review Textarea -->
      <div class="space-y-1.5">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">
            <ChatBubbleBottomCenterTextIcon class="h-3.5 w-3.5 text-[var(--color-accent)]" />
            Review Comments
          </label>
          <span class="text-[10px] tabular-nums" :class="form.comment.length > 400 ? 'text-amber-500 dark:text-amber-400' : 'text-slate-400 dark:text-white/40'">
            {{ form.comment.length }} / 500
          </span>
        </div>
        <textarea 
          v-model="form.comment" 
          rows="4" 
          maxlength="500"
          class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-3 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 text-sm outline-none transition-all focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 resize-none leading-relaxed" 
          placeholder="Describe your appointment, the cut, and overall experience..."
        ></textarea>
      </div>
    </div>

    <!-- Submit Button -->
    <button 
      class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--color-accent)] py-3 text-xs font-bold text-white shadow-lg shadow-[var(--color-accent)]/20 transition-all duration-300 hover:brightness-110 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
      :disabled="submitting"
    >
      <svg v-if="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25" />
        <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="opacity-75" />
      </svg>
      <PaperAirplaneIcon v-else class="h-4 w-4" />
      {{ submitting ? 'Submitting...' : 'Submit Review' }}
    </button>
    <p v-if="status" class="mt-4 text-center text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 py-2 rounded-lg border border-emerald-500/20">{{ status }}</p>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { 
  StarIcon, 
  ChatBubbleBottomCenterTextIcon,
  PencilSquareIcon,
  UserIcon,
  ChevronDownIcon,
  PaperAirplaneIcon
} from '@heroicons/vue/24/outline'
import { ScissorsIcon } from '@heroicons/vue/24/solid'

defineProps({
  barbers: { type: Array, default: () => [] },
  services: { type: Array, default: () => [] },
  form: { type: Object, required: true },
  submitting: { type: Boolean, default: false },
  status: { type: String, default: '' },
  ratingLabels: { type: Array, default: () => ['Poor', 'Fair', 'Good', 'Great', 'Excellent'] }
})

defineEmits(['submit'])

const hoveredStar = ref(0)
</script>
