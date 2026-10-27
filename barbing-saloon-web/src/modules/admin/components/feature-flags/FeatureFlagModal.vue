<template>
  <Teleport to="body">
    <div 
      v-if="modelValue" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-md animate-fade-in"
      @click.self="$emit('update:modelValue', false)"
    >
      <div 
        class="w-full max-w-lg rounded-[2rem] bg-[#12161C] border border-white/[0.08] p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden"
      >
        <!-- Background Ambient Glow -->
        <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-admin/10 blur-[80px] pointer-events-none"></div>

        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-white/[0.06] relative z-10">
          <div>
            <h3 class="font-display text-xl font-bold text-white tracking-tight">
              {{ editingFlag ? 'Edit Feature Flag' : 'Configure New Feature Flag' }}
            </h3>
            <p class="text-xs text-white/50 mt-0.5">
              {{ editingFlag ? 'Adjust runtime targeting and rollout percentages.' : 'Define unique key, supported clients, and rollout scope.' }}
            </p>
          </div>
          <button 
            @click="$emit('update:modelValue', false)" 
            class="p-2 rounded-xl text-white/40 hover:text-white hover:bg-white/[0.06] transition-colors"
          >
            <XMarkIcon class="h-5 w-5" />
          </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="$emit('save')" class="space-y-5 relative z-10">
          <!-- Flag Key (Identifier) -->
          <div class="space-y-1.5">
            <label class="text-[11px] font-bold uppercase tracking-wider text-white/50">
              Flag Key (Unique Identifier) <span class="text-admin">*</span>
            </label>
            <input 
              v-model="form.key" 
              type="text" 
              required
              :disabled="!!editingFlag"
              placeholder="e.g. instant_booking_checkout"
              class="w-full px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white text-xs font-mono placeholder:text-white/20 focus:outline-none focus:border-admin/50 focus:bg-white/[0.06] transition-all disabled:opacity-50" 
            />
            <p class="text-[10px] text-white/30">Snake_case recommended. Used by web & mobile SDKs to evaluate feature status.</p>
          </div>

          <!-- Display Name -->
          <div class="space-y-1.5">
            <label class="text-[11px] font-bold uppercase tracking-wider text-white/50">
              Display Name <span class="text-admin">*</span>
            </label>
            <input 
              v-model="form.name" 
              type="text" 
              required
              placeholder="e.g. Instant Booking Checkout"
              class="w-full px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white text-xs placeholder:text-white/20 focus:outline-none focus:border-admin/50 focus:bg-white/[0.06] transition-all" 
            />
          </div>

          <!-- Description -->
          <div class="space-y-1.5">
            <label class="text-[11px] font-bold uppercase tracking-wider text-white/50">
              Operational Description
            </label>
            <textarea 
              v-model="form.description" 
              rows="2" 
              placeholder="Explain which user flows or components this flag controls..."
              class="w-full px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white text-xs placeholder:text-white/20 focus:outline-none focus:border-admin/50 focus:bg-white/[0.06] resize-none transition-all"
            ></textarea>
          </div>

          <!-- Platform Targeting -->
          <div class="space-y-2">
            <label class="text-[11px] font-bold uppercase tracking-wider text-white/50">
              Target Platforms <span class="text-admin">*</span>
            </label>
            <div class="flex items-center gap-3">
              <!-- Web Platform Chip -->
              <button
                type="button"
                @click="$emit('togglePlatform', 'web')"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold border transition-all"
                :class="form.enabled_for.includes('web')
                  ? 'bg-cyan-500/15 text-cyan-300 border-cyan-500/40 shadow-[0_0_15px_rgba(6,182,212,0.15)]'
                  : 'bg-white/[0.02] text-white/40 border-white/[0.06] hover:bg-white/[0.05]'"
              >
                <GlobeAltIcon class="h-4 w-4" />
                <span>Web Portal</span>
              </button>

              <!-- App Platform Chip -->
              <button
                type="button"
                @click="$emit('togglePlatform', 'app')"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold border transition-all"
                :class="form.enabled_for.includes('app')
                  ? 'bg-violet-500/15 text-violet-300 border-violet-500/40 shadow-[0_0_15px_rgba(139,92,246,0.15)]'
                  : 'bg-white/[0.02] text-white/40 border-white/[0.06] hover:bg-white/[0.05]'"
              >
                <DevicePhoneMobileIcon class="h-4 w-4" />
                <span>Mobile App</span>
              </button>
            </div>
          </div>

          <!-- Rollout Percentage Slider -->
          <div class="space-y-2 pt-1">
            <div class="flex items-center justify-between">
              <label class="text-[11px] font-bold uppercase tracking-wider text-white/50">
                Rollout Percentage
              </label>
              <span class="font-mono text-xs font-bold text-admin">{{ form.rollout_percentage }}% traffic</span>
            </div>
            <input 
              type="range" 
              v-model.number="form.rollout_percentage" 
              min="0" 
              max="100" 
              step="5"
              class="w-full h-2 rounded-full appearance-none bg-white/[0.08] accent-admin cursor-pointer" 
            />
            <div class="flex items-center justify-between text-[10px] text-white/30 font-mono">
              <button type="button" @click="form.rollout_percentage = 0" class="hover:text-white">0%</button>
              <button type="button" @click="form.rollout_percentage = 25" class="hover:text-white">25%</button>
              <button type="button" @click="form.rollout_percentage = 50" class="hover:text-white">50%</button>
              <button type="button" @click="form.rollout_percentage = 75" class="hover:text-white">75%</button>
              <button type="button" @click="form.rollout_percentage = 100" class="hover:text-white">100%</button>
            </div>
          </div>

          <!-- Active State Checkbox -->
          <div class="flex items-center gap-3 p-3.5 rounded-xl border border-white/[0.08] bg-white/[0.02]">
            <input 
              type="checkbox" 
              id="active_state" 
              v-model="form.is_active" 
              class="h-4 w-4 rounded accent-emerald-500 cursor-pointer" 
            />
            <label for="active_state" class="text-xs text-white/80 cursor-pointer font-medium select-none">
              Flag is active immediately upon saving
            </label>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3 pt-3 border-t border-white/[0.06]">
            <button 
              type="button" 
              @click="$emit('update:modelValue', false)"
              class="px-4 py-2.5 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] text-white/60 hover:text-white text-xs font-semibold transition-all"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="saving || !form.key || !form.name"
              class="flex-1 flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-admin to-admin-light text-obsidian text-xs font-bold shadow-[0_4px_20px_rgba(255,103,0,0.25)] hover:shadow-[0_6px_28px_rgba(255,103,0,0.4)] disabled:opacity-40 transition-all"
            >
              <ArrowPathIcon v-if="saving" class="h-4 w-4 animate-spin" />
              <span>{{ saving ? 'Saving Flag...' : (editingFlag ? 'Update Flag' : 'Create Flag') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { 
  XMarkIcon, 
  ArrowPathIcon, 
  GlobeAltIcon, 
  DevicePhoneMobileIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  editingFlag: {
    type: Object,
    default: null
  },
  form: {
    type: Object,
    required: true
  },
  saving: {
    type: Boolean,
    default: false
  }
});

defineEmits(['update:modelValue', 'togglePlatform', 'save']);
</script>
