<template>
  <form class="group/form rounded-2xl border border-theme-border bg-theme-surface/80 p-8 backdrop-blur-sm transition-all duration-500 hover:border-gold/20" @submit.prevent="$emit('submit')">
    <div class="flex items-center gap-3 mb-6">
      <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/10 border border-gold/20">
        <PencilSquareIcon class="h-5 w-5 text-gold" />
      </div>
      <div>
        <h2 class="font-display text-2xl text-theme-text">Write a <span class="text-gold">Review</span></h2>
        <p class="text-xs text-theme-muted mt-0.5">Let your barber know how they did</p>
      </div>
    </div>

    <div class="space-y-5">
      <!-- Barber Select -->
      <div class="space-y-2">
        <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-theme-muted">
          <UserIcon class="h-3.5 w-3.5" />
          Barber
        </label>
        <div class="relative">
          <select v-model="form.barber_id" class="w-full appearance-none rounded-xl border border-theme-border bg-theme-bg px-4 py-3 pr-10 text-theme-text outline-none transition-all focus:border-gold/50 focus:shadow-[0_0_0_3px_rgba(255,153,0,0.08)]">
            <option value="">Select barber</option>
            <option v-for="barber in barbers" :key="barber.id" :value="barber.id">{{ barber.name }}</option>
          </select>
          <ChevronDownIcon class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-theme-muted/50" />
        </div>
      </div>

      <!-- Service Select -->
      <div class="space-y-2">
        <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-theme-muted">
          <ScissorsIcon class="h-3.5 w-3.5" />
          Service
        </label>
        <div class="relative">
          <select v-model="form.service_id" class="w-full appearance-none rounded-xl border border-theme-border bg-theme-bg px-4 py-3 pr-10 text-theme-text outline-none transition-all focus:border-gold/50 focus:shadow-[0_0_0_3px_rgba(255,153,0,0.08)]">
            <option value="">Select service</option>
            <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
          </select>
          <ChevronDownIcon class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-theme-muted/50" />
        </div>
      </div>

      <!-- Star Rating -->
      <div class="space-y-2">
        <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-theme-muted">
          <StarIcon class="h-3.5 w-3.5" />
          Rating
        </label>
        <div class="flex items-center gap-1 p-3 rounded-xl border border-theme-border bg-theme-bg/50 w-fit">
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
            <!-- Glow behind active stars -->
            <div 
              v-if="star <= (hoveredStar || form.rating)" 
              class="absolute inset-0 rounded-full bg-gold/20 blur-md"
            ></div>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="relative h-8 w-8 transition-all duration-200" :class="star <= (hoveredStar || form.rating) ? 'text-gold drop-shadow-[0_0_6px_rgba(255,153,0,0.5)]' : 'text-white/10 group-hover/star:text-white/25'">
              <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
            </svg>
          </button>
          <span class="ml-3 text-sm font-medium" :class="form.rating >= 4 ? 'text-gold' : form.rating >= 3 ? 'text-amber-400' : 'text-theme-muted'">
            {{ ratingLabels[form.rating - 1] }}
          </span>
        </div>
      </div>

      <!-- Review Textarea -->
      <div class="space-y-2">
        <label class="flex items-center justify-between">
          <span class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-theme-muted">
            <ChatBubbleBottomCenterTextIcon class="h-3.5 w-3.5" />
            Review
          </span>
          <span class="text-[10px] tabular-nums" :class="form.comment.length > 400 ? 'text-amber-400' : 'text-theme-muted/50'">
            {{ form.comment.length }} / 500
          </span>
        </label>
        <textarea 
          v-model="form.comment" 
          rows="5" 
          maxlength="500"
          class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-all focus:border-gold/50 focus:shadow-[0_0_0_3px_rgba(255,153,0,0.08)] resize-none leading-relaxed" 
          placeholder="Tell us about your experience..."
        ></textarea>
      </div>
    </div>

    <!-- Submit Button -->
    <button 
      class="mt-8 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-gold to-gold-dark py-4 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(255,153,0,0.2)] transition-all duration-300 hover:shadow-[0_0_40px_rgba(255,153,0,0.35)] hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
      :disabled="submitting"
    >
      <svg v-if="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25" />
        <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="opacity-75" />
      </svg>
      <PaperAirplaneIcon v-else class="h-4 w-4" />
      {{ submitting ? 'Submitting...' : 'Submit review' }}
    </button>
    <p v-if="status" class="mt-4 text-center text-sm font-medium text-emerald-400 bg-emerald-400/10 py-2 rounded-lg border border-emerald-500/20">{{ status }}</p>
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
