<template>
  <div class="rounded-3xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-xl overflow-hidden h-fit shadow-xl">
    <div class="flex items-center gap-3 border-b border-white/[0.04] px-8 py-6 bg-black/20">
      <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 border border-purple-500/20 shadow-inner">
        <SparklesIcon class="h-5 w-5 text-purple-400" />
      </div>
      <div>
        <h2 class="font-display text-xl text-theme-text">Specialties</h2>
        <p class="text-[10px] uppercase tracking-widest text-ivory/40 font-bold mt-0.5">Your signature styles</p>
      </div>
    </div>

    <div class="p-8">
      <!-- All Available Styles Grid -->
      <p class="text-[11px] uppercase tracking-widest text-ivory/50 font-bold ml-1 mb-4">Tap to select / deselect</p>
      <div class="flex flex-wrap gap-2.5 mb-6">
        <button
          v-for="style in allStyles"
          :key="style"
          type="button"
          @click="$emit('toggle', style)"
          :class="[
            'inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition-all duration-200 border cursor-pointer',
            specialties.includes(style)
              ? 'bg-gold/15 border-gold/50 text-gold shadow-[0_0_12px_rgba(212,175,55,0.2)] ring-1 ring-gold/20'
              : 'bg-[#1a1a1a] border-white/8 text-ivory/40 hover:border-white/20 hover:text-ivory/60'
          ]"
        >
          <CheckIcon v-if="specialties.includes(style)" class="h-3.5 w-3.5" />
          <span>{{ style }}</span>
        </button>
      </div>

      <!-- Custom Add Input -->
      <div class="pt-4 border-t border-white/5">
        <p class="text-[10px] uppercase tracking-widest text-ivory/40 font-bold ml-1 mb-3">Add custom style</p>
        <div class="flex gap-2">
          <input
            v-model="newSpecialty"
            @keydown.enter.prevent="handleAdd"
            class="flex-1 rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-theme-text placeholder:text-ivory/30 outline-none transition-all focus:border-purple-500/50 focus:bg-black/40 focus:ring-1 focus:ring-purple-500/30"
            placeholder="Type a custom style name..."
          />
          <button
            type="button"
            @click="handleAdd"
            class="rounded-xl bg-purple-500/20 hover:bg-purple-500 text-purple-400 hover:text-white px-5 py-3 text-sm font-bold transition-all border border-purple-500/30 hover:border-purple-500 hover:shadow-[0_0_15px_rgba(168,85,247,0.4)]"
          >
            Add
          </button>
        </div>
      </div>

      <!-- Selected count -->
      <div class="mt-5 flex items-center gap-2 text-xs text-ivory/40">
        <SparklesIcon class="h-4 w-4 text-gold/60" />
        <span><strong class="text-gold">{{ specialties.length }}</strong> specialties selected</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { SparklesIcon, CheckIcon } from '@heroicons/vue/24/outline'

defineProps({
  specialties: { type: Array, default: () => [] },
  allStyles: { type: Array, default: () => [] }
})

const emit = defineEmits(['toggle', 'add'])

const newSpecialty = ref('')

function handleAdd() {
  const val = newSpecialty.value.trim()
  if (val) {
    emit('add', val)
    newSpecialty.value = ''
  }
}
</script>
