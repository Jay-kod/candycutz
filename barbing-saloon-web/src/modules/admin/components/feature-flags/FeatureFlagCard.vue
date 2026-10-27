<template>
  <div 
    class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg transition-all duration-300 hover:border-admin/40 hover:-translate-y-0.5 hover:shadow-[0_10px_30px_rgba(0,0,0,0.4)]"
  >
    <!-- Ambient subtle highlight -->
    <div class="absolute inset-0 bg-gradient-to-r from-admin/[0.02] via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-4">
      <!-- Left: Flag Details & Metadata -->
      <div class="flex-1 min-w-0">
        <!-- Title & Badges -->
        <div class="flex flex-wrap items-center gap-2.5 mb-2">
          <h3 class="font-display text-base font-bold text-white group-hover:text-admin-light transition-colors">
            {{ flag.name }}
          </h3>

          <!-- Unique Key Badge with Click-to-Copy -->
          <button
            @click="copyKey"
            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-mono font-medium bg-white/[0.04] text-amber-400 border border-amber-500/20 hover:bg-amber-500/10 transition-colors"
            :title="copied ? 'Copied!' : 'Click to copy key'"
          >
            <span>{{ flag.key }}</span>
            <CheckIcon v-if="copied" class="h-3 w-3 text-emerald-400" />
            <DocumentDuplicateIcon v-else class="h-3 w-3 text-amber-400/60" />
          </button>

          <!-- Status Indicator Badge -->
          <span 
            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
            :class="flag.is_active 
              ? 'bg-emerald-950/80 text-emerald-400 border-emerald-500/30' 
              : 'bg-white/[0.03] text-white/40 border-white/[0.08]'"
          >
            <span class="h-1.5 w-1.5 rounded-full" :class="flag.is_active ? 'bg-emerald-400 animate-pulse' : 'bg-white/30'"></span>
            <span>{{ flag.is_active ? 'Active' : 'Inactive' }}</span>
          </span>
        </div>

        <!-- Description -->
        <p class="text-xs text-white/60 leading-relaxed mb-4 max-w-2xl">
          {{ flag.description || 'No detailed description provided for this runtime feature flag.' }}
        </p>

        <!-- Web Portal & Rollout Badges -->
        <div class="flex flex-wrap items-center gap-4 text-xs">
          <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold border bg-cyan-500/10 text-cyan-300 border-cyan-500/20">
              <GlobeAltIcon class="h-3 w-3" />
              <span>Web Portal</span>
            </span>
          </div>

          <!-- Rollout Percentage Slider Representation -->
          <div class="flex items-center gap-2">
            <span class="text-[11px] text-white/40">Rollout:</span>
            <div class="w-24 h-2 rounded-full bg-white/[0.06] overflow-hidden border border-white/[0.04]">
              <div 
                class="h-full rounded-full bg-gradient-to-r from-admin via-amber-400 to-emerald-400 transition-all duration-500"
                :style="{ width: `${flag.rollout_percentage ?? 100}%` }"
              ></div>
            </div>
            <span class="font-mono text-xs font-semibold text-white/80">{{ flag.rollout_percentage ?? 100 }}%</span>
          </div>

          <!-- Created Date -->
          <span class="text-[11px] text-white/40">
            Added {{ formattedDate }}
          </span>
        </div>
      </div>

      <!-- Right: Action Controls (Toggle, Edit, Delete) -->
      <div class="flex items-center gap-3 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-white/[0.04]">
        <!-- Luxury Toggle Switch -->
        <button 
          @click="$emit('toggle', flag)" 
          :disabled="isToggling"
          class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border transition-all duration-300 focus:outline-none disabled:opacity-50"
          :class="flag.is_active 
            ? 'bg-emerald-500/20 border-emerald-500/40' 
            : 'bg-white/[0.06] border-white/[0.08]'"
          :title="flag.is_active ? 'Click to disable' : 'Click to enable'"
        >
          <span 
            class="pointer-events-none inline-block h-4 w-4 transform rounded-full shadow-md transition-all duration-300 mt-0.5 ml-0.5"
            :class="flag.is_active 
              ? 'translate-x-5 bg-emerald-400' 
              : 'translate-x-0 bg-white/40'"
          ></span>
        </button>

        <!-- Edit Button -->
        <button 
          @click="$emit('edit', flag)"
          class="p-2 rounded-xl text-white/50 hover:text-admin hover:bg-admin/10 hover:border-admin/20 border border-transparent transition-colors"
          title="Edit feature flag"
        >
          <PencilSquareIcon class="h-4 w-4" />
        </button>

        <!-- Delete Button -->
        <button 
          @click="$emit('delete', flag)"
          class="p-2 rounded-xl text-white/50 hover:text-rose-400 hover:bg-rose-500/10 hover:border-rose-500/20 border border-transparent transition-colors"
          title="Delete feature flag"
        >
          <TrashIcon class="h-4 w-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { 
  GlobeAltIcon, 
  PencilSquareIcon, 
  TrashIcon, 
  DocumentDuplicateIcon, 
  CheckIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  flag: {
    type: Object,
    required: true
  },
  isToggling: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle', 'edit', 'delete']);

const copied = ref(false);

const formattedDate = computed(() => {
  if (!props.flag.created_at) return 'Recently';
  try {
    return new Date(props.flag.created_at).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  } catch {
    return 'Recently';
  }
});

const copyKey = async () => {
  try {
    await navigator.clipboard.writeText(props.flag.key);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (err) {
    console.error('Failed to copy flag key:', err);
  }
};
</script>
