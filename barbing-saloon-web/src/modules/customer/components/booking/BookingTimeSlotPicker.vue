<template>
  <div class="space-y-4">
    <!-- Header with Step Indicator (Compact) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[var(--color-border)] pb-3">
      <div>
        <h2 class="font-display text-lg md:text-xl text-[var(--color-text-primary)] flex items-center gap-2">
          <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-[11px] font-bold shadow-xs">1</span>
          Select <span class="text-[var(--color-accent)]">Date &amp; Time</span>
        </h2>
        <p class="text-[10px] text-[var(--color-text-secondary)] mt-0.5 ml-8">
          Pick your preferred day and time slot with real-time chair availability
        </p>
      </div>

      <!-- Live Status / Loading Indicator -->
      <div v-if="loadingSlots" class="inline-flex items-center gap-1.5 self-start sm:self-auto rounded-full bg-[var(--color-accent)]/10 px-2 py-0.5 text-[10px] font-semibold text-[var(--color-accent)] border border-[var(--color-accent)]/20 animate-pulse">
        <ArrowPathIcon class="w-2.5 h-2.5 animate-spin" />
        <span>Updating...</span>
      </div>
      <div v-else-if="appointmentDate && slots.length > 0" class="inline-flex items-center gap-1 self-start sm:self-auto rounded-full bg-[var(--color-success)]/10 px-2 py-0.5 text-[10px] font-medium text-[var(--color-success)] border border-[var(--color-success)]/20">
        <CheckCircleIcon class="w-3 h-3" />
        <span>{{ slots.length }} slots</span>
      </div>
    </div>

    <!-- DATE SELECTION SECTION -->
    <div class="space-y-2">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-1.5">
          <CalendarDaysIcon class="w-3 h-3 text-[var(--color-accent)]" />
          <label class="text-[10px] font-bold uppercase tracking-wider text-[var(--color-text-primary)]">
            Choose Appointment Date
          </label>
        </div>

        <!-- Quick Jump Chips (Compact & Clean) -->
        <div class="flex items-center gap-1">
          <button
            type="button"
            @click="selectRelativeDay(0)"
            class="px-2 py-0.5 rounded text-[10px] font-medium transition-all border cursor-pointer"
            :class="isDaySelected(0)
              ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] border-[var(--color-accent)] font-semibold shadow-xs'
              : 'bg-[var(--color-bg-tertiary)]/50 text-[var(--color-text-secondary)] border-[var(--color-border)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-text-primary)]'"
          >
            Today
          </button>
          <button
            type="button"
            @click="selectRelativeDay(1)"
            class="px-2 py-0.5 rounded text-[10px] font-medium transition-all border cursor-pointer"
            :class="isDaySelected(1)
              ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] border-[var(--color-accent)] font-semibold shadow-xs'
              : 'bg-[var(--color-bg-tertiary)]/50 text-[var(--color-text-secondary)] border-[var(--color-border)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-text-primary)]'"
          >
            Tomorrow
          </button>
          <button
            type="button"
            @click="selectNextWeekend"
            class="hidden sm:inline-flex px-2 py-0.5 rounded text-[10px] font-medium transition-all border cursor-pointer"
            :class="isWeekendSelected
              ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] border-[var(--color-accent)] font-semibold shadow-xs'
              : 'bg-[var(--color-bg-tertiary)]/50 text-[var(--color-text-secondary)] border-[var(--color-border)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-text-primary)]'"
          >
            Weekend
          </button>

          <!-- Custom Date Input Trigger -->
          <div class="relative inline-flex items-center">
            <button
              type="button"
              class="relative flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium transition-all border bg-[var(--color-bg-tertiary)]/50 text-[var(--color-text-secondary)] border-[var(--color-border)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-text-primary)] cursor-pointer"
            >
              <span>Calendar</span>
              <input
                :value="appointmentDate"
                type="date"
                :min="todayStr"
                class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                @input="handleCustomDateInput($event.target.value)"
              />
            </button>
          </div>
        </div>
      </div>

      <!-- Horizontal Date Carousel / Strip (Compact Micro Cards) -->
      <div class="relative group/strip">
        <!-- Scroll Left Button -->
        <button
          type="button"
          @click="scrollStrip('left')"
          class="hidden md:flex absolute -left-2.5 top-1/2 -translate-y-1/2 z-20 h-6 w-6 items-center justify-center rounded-full bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-primary)] shadow-sm hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] hover:border-[var(--color-accent)] transition-all opacity-0 group-hover/strip:opacity-100 cursor-pointer"
          aria-label="Scroll dates left"
        >
          <ChevronLeftIcon class="h-3 w-3" />
        </button>

        <!-- Dates Strip Container -->
        <div
          ref="dateStripRef"
          class="flex items-center gap-1.5 overflow-x-auto py-1 px-0.5 scroll-smooth no-scrollbar"
        >
          <button
            v-for="day in daysList"
            :key="day.dateStr"
            type="button"
            @click="selectDate(day.dateStr)"
            class="flex-shrink-0 w-12 sm:w-14 py-1.5 rounded-lg flex flex-col items-center justify-center border transition-all duration-150 cursor-pointer select-none relative"
            :class="[
              appointmentDate === day.dateStr
                ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] border-[var(--color-accent)] shadow-sm shadow-[var(--color-accent)]/20 scale-[1.02] z-10'
                : 'bg-[var(--color-bg-tertiary)]/40 hover:bg-[var(--color-bg-tertiary)] border-[var(--color-border)] text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40',
              day.isSunday ? 'opacity-60' : ''
            ]"
          >
            <!-- Today / Sunday Badge -->
            <span
              v-if="day.isToday"
              class="text-[7px] font-black uppercase tracking-wider px-1 py-0.2 rounded-full mb-0.5 transition-colors leading-none"
              :class="appointmentDate === day.dateStr ? 'bg-[var(--color-accent-foreground)]/20 text-[var(--color-accent-foreground)]' : 'bg-[var(--color-accent)]/15 text-[var(--color-accent)]'"
            >
              Today
            </span>
            <span
              v-else-if="day.isSunday"
              class="text-[7.5px] font-bold uppercase tracking-wider px-0.5 mb-0.5 text-[var(--color-text-muted)] leading-none"
            >
              Sun
            </span>
            <span
              v-else
              class="text-[8px] font-bold uppercase tracking-wider transition-colors mb-0.5 leading-none"
              :class="appointmentDate === day.dateStr ? 'text-[var(--color-accent-foreground)]/80' : 'text-[var(--color-text-muted)]'"
            >
              {{ day.weekdayShort }}
            </span>

            <!-- Day Number (Legible & Compact) -->
            <span
              class="font-display text-sm sm:text-base font-bold leading-tight tracking-tight my-0.2"
              :class="appointmentDate === day.dateStr ? 'text-[var(--color-accent-foreground)]' : 'text-[var(--color-text-primary)]'"
            >
              {{ day.dayNum }}
            </span>

            <!-- Month -->
            <span
              class="text-[8px] font-semibold uppercase tracking-wider transition-colors leading-none"
              :class="appointmentDate === day.dateStr ? 'text-[var(--color-accent-foreground)]/80' : 'text-[var(--color-text-muted)]'"
            >
              {{ day.monthShort }}
            </span>

            <!-- Active Indicator Underline -->
            <div
              v-if="appointmentDate === day.dateStr"
              class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-2.5 h-0.5 rounded-full bg-[var(--color-accent-foreground)]"
            ></div>
          </button>
        </div>

        <!-- Scroll Right Button -->
        <button
          type="button"
          @click="scrollStrip('right')"
          class="hidden md:flex absolute -right-2.5 top-1/2 -translate-y-1/2 z-20 h-6 w-6 items-center justify-center rounded-full bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-primary)] shadow-sm hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] hover:border-[var(--color-accent)] transition-all opacity-0 group-hover/strip:opacity-100 cursor-pointer"
          aria-label="Scroll dates right"
        >
          <ChevronRightIcon class="h-3 w-3" />
        </button>
      </div>

      <!-- Selected Date Confirmation Pill (Compact) -->
      <div v-if="selectedDayMeta" class="flex items-center gap-1.5 text-[10px] text-[var(--color-text-secondary)] bg-[var(--color-bg-tertiary)]/30 rounded-md px-2.5 py-1 border border-[var(--color-border)]/50">
        <CalendarDaysIcon class="w-3 h-3 text-[var(--color-accent)] shrink-0" />
        <span>Selected: <strong class="text-[var(--color-text-primary)] font-semibold">{{ selectedDayMeta.fullFormatted }}</strong></span>
      </div>
    </div>

    <!-- TIME SLOTS SECTION -->
    <div class="space-y-2.5 pt-1">
      <!-- Period Tabs Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
        <div class="flex items-center gap-1.5">
          <ClockIcon class="w-3 h-3 text-[var(--color-accent)]" />
          <label class="text-[10px] font-bold uppercase tracking-wider text-[var(--color-text-primary)]">
            Choose Time Slot
          </label>
        </div>

        <!-- Time Period Filter Tabs (Compact & Sleek) -->
        <div v-if="slots.length > 0" class="inline-flex rounded-md bg-[var(--color-bg-tertiary)]/60 p-0.5 border border-[var(--color-border)] text-[10px]">
          <button
            type="button"
            @click="selectedPeriod = 'all'"
            class="flex items-center gap-1 px-2 py-0.5 rounded transition-all duration-150 cursor-pointer"
            :class="selectedPeriod === 'all'
              ? 'bg-[var(--color-bg-secondary)] text-[var(--color-accent)] shadow-xs font-bold'
              : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] font-medium'"
          >
            <span>All</span>
            <span class="rounded-full px-1 py-0 text-[8px] font-bold leading-none" :class="selectedPeriod === 'all' ? 'bg-[var(--color-accent)]/15 text-[var(--color-accent)]' : 'bg-[var(--color-border)]/50 text-[var(--color-text-muted)]'">{{ slotBuckets.all.length }}</span>
          </button>

          <button
            type="button"
            @click="selectedPeriod = 'morning'"
            class="flex items-center gap-1 px-2 py-0.5 rounded transition-all duration-150 cursor-pointer"
            :class="selectedPeriod === 'morning'
              ? 'bg-[var(--color-bg-secondary)] text-[var(--color-accent)] shadow-xs font-bold'
              : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] font-medium'"
          >
            <SunIcon class="w-2.5 h-2.5 text-amber-500" />
            <span>Morning</span>
            <span class="rounded-full px-1 py-0 text-[8px] font-bold leading-none" :class="selectedPeriod === 'morning' ? 'bg-[var(--color-accent)]/15 text-[var(--color-accent)]' : 'bg-[var(--color-border)]/50 text-[var(--color-text-muted)]'">{{ slotBuckets.morning.length }}</span>
          </button>

          <button
            type="button"
            @click="selectedPeriod = 'afternoon'"
            class="flex items-center gap-1 px-2 py-0.5 rounded transition-all duration-150 cursor-pointer"
            :class="selectedPeriod === 'afternoon'
              ? 'bg-[var(--color-bg-secondary)] text-[var(--color-accent)] shadow-xs font-bold'
              : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] font-medium'"
          >
            <SunIcon class="w-2.5 h-2.5 text-orange-400" />
            <span>Afternoon</span>
            <span class="rounded-full px-1 py-0 text-[8px] font-bold leading-none" :class="selectedPeriod === 'afternoon' ? 'bg-[var(--color-accent)]/15 text-[var(--color-accent)]' : 'bg-[var(--color-border)]/50 text-[var(--color-text-muted)]'">{{ slotBuckets.afternoon.length }}</span>
          </button>

          <button
            type="button"
            @click="selectedPeriod = 'evening'"
            class="flex items-center gap-1 px-2 py-0.5 rounded transition-all duration-150 cursor-pointer"
            :class="selectedPeriod === 'evening'
              ? 'bg-[var(--color-bg-secondary)] text-[var(--color-accent)] shadow-xs font-bold'
              : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] font-medium'"
          >
            <MoonIcon class="w-2.5 h-2.5 text-indigo-400" />
            <span>Evening</span>
            <span class="rounded-full px-1 py-0 text-[8px] font-bold leading-none" :class="selectedPeriod === 'evening' ? 'bg-[var(--color-accent)]/15 text-[var(--color-accent)]' : 'bg-[var(--color-border)]/50 text-[var(--color-text-muted)]'">{{ slotBuckets.evening.length }}</span>
          </button>
        </div>
      </div>

      <!-- Loading Skeletons -->
      <div v-if="loadingSlots" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-1.5 py-1">
        <div
          v-for="i in 10"
          :key="i"
          class="h-7.5 rounded-lg bg-[var(--color-bg-tertiary)]/50 animate-pulse border border-[var(--color-border)]/40 flex items-center justify-center"
        >
          <div class="h-2.5 w-12 bg-[var(--color-border)]/40 rounded"></div>
        </div>
      </div>

      <!-- Slots Grid (Compact, Readable Precision Buttons) -->
      <div v-else-if="filteredSlots.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-1.5">
        <button
          v-for="slot in filteredSlots"
          :key="slot"
          type="button"
          @click="$emit('update:appointmentTime', slot)"
          class="group relative flex items-center justify-between px-2.5 py-1.5 rounded-lg border text-[11px] font-medium transition-all duration-150 cursor-pointer"
          :class="appointmentTime === slot
            ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] border-[var(--color-accent)] shadow-xs ring-1 ring-[var(--color-accent)]/40 font-semibold z-10'
            : 'bg-[var(--color-bg-tertiary)]/35 hover:bg-[var(--color-bg-tertiary)] border-[var(--color-border)] text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/50'"
        >
          <div class="flex items-center gap-1.5">
            <ClockIcon
              class="w-3 h-3 transition-colors shrink-0"
              :class="appointmentTime === slot ? 'text-[var(--color-accent-foreground)]' : 'text-[var(--color-accent)]/70 group-hover:text-[var(--color-accent)]'"
            />
            <span class="tracking-tight">{{ format12Hour(slot) }}</span>
          </div>

          <!-- Selection Check Badge -->
          <div
            v-if="appointmentTime === slot"
            class="h-3.5 w-3.5 rounded-full bg-[var(--color-accent-foreground)]/20 flex items-center justify-center shrink-0 ml-1"
          >
            <CheckIcon class="w-2.5 h-2.5 text-[var(--color-accent-foreground)] stroke-[3]" />
          </div>
        </button>
      </div>

      <!-- Empty State: Filter Tab has 0 slots but other periods do -->
      <div
        v-else-if="slots.length > 0 && filteredSlots.length === 0"
        class="rounded-lg border border-dashed border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/20 p-5 text-center"
      >
        <p class="text-[11px] text-[var(--color-text-secondary)]">
          No <span class="capitalize font-semibold text-[var(--color-text-primary)]">{{ selectedPeriod }}</span> slots available for this day.
        </p>
        <button
          type="button"
          @click="selectedPeriod = 'all'"
          class="mt-2 inline-flex items-center gap-1 px-2.5 py-1 rounded bg-[var(--color-accent)]/10 text-[var(--color-accent)] hover:bg-[var(--color-accent)]/20 text-[10px] font-bold transition-colors cursor-pointer"
        >
          View All Available Slots ({{ slots.length }})
        </button>
      </div>

      <!-- Empty State: Date has 0 slots or Shop Closed -->
      <div
        v-else-if="appointmentDate && slots.length === 0"
        class="rounded-lg border border-dashed border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/20 p-5 text-center space-y-1.5"
      >
        <div class="mx-auto w-8 h-8 rounded-full bg-[var(--color-accent)]/10 flex items-center justify-center text-[var(--color-accent)]">
          <ClockIcon class="w-4 h-4" />
        </div>
        <h4 class="text-xs font-display font-bold text-[var(--color-text-primary)]">
          No Available Slots on This Date
        </h4>
        <p class="text-[10px] text-[var(--color-text-secondary)] max-w-xs mx-auto">
          All appointment times for this day may have been booked, or the barber is off-duty.
        </p>
        <div class="pt-1 flex items-center justify-center">
          <button
            type="button"
            @click="selectRelativeDay(1)"
            class="inline-flex items-center gap-1 rounded bg-[var(--color-accent)] px-2.5 py-1 text-[11px] font-bold text-[var(--color-accent-foreground)] shadow-xs hover:bg-[var(--color-accent-hover)] transition-all cursor-pointer"
          >
            <SparklesIcon class="w-3 h-3" /> Check Tomorrow
          </button>
        </div>
      </div>

      <!-- Empty State: Pick a Date First -->
      <div
        v-else
        class="h-16 rounded-lg border border-dashed border-[var(--color-border)] flex flex-col items-center justify-center text-xs text-[var(--color-text-muted)] bg-[var(--color-bg-tertiary)]/30 gap-1"
      >
        <CalendarDaysIcon class="w-4 h-4 text-[var(--color-accent)]/50" />
        <span class="text-[10px]">Pick an appointment date above to explore available seats</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import {
  CalendarDaysIcon,
  ClockIcon,
  ArrowPathIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  SunIcon,
  MoonIcon,
  CheckCircleIcon,
  SparklesIcon,
} from '@heroicons/vue/24/outline'
import { CheckIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  appointmentDate: { type: String, default: '' },
  appointmentTime: { type: String, default: '' },
  todayStr: { type: String, required: true },
  slots: { type: Array, default: () => [] },
  loadingSlots: { type: Boolean, default: false },
})

const emit = defineEmits(['update:appointmentDate', 'update:appointmentTime', 'dateChange'])

const dateStripRef = ref(null)
const selectedPeriod = ref('all') // 'all' | 'morning' | 'afternoon' | 'evening'

// Scroll helper for horizontal date carousel
function scrollStrip(direction) {
  if (!dateStripRef.value) return
  const offset = direction === 'left' ? -180 : 180
  dateStripRef.value.scrollBy({ left: offset, behavior: 'smooth' })
}

// Generate the next 21 selectable calendar days starting from todayStr
const daysList = computed(() => {
  const [y, m, d] = (props.todayStr || '').split('-').map(Number)
  const base = y && m && d ? new Date(y, m - 1, d) : new Date()
  const list = []

  for (let i = 0; i < 21; i++) {
    const cur = new Date(base.getFullYear(), base.getMonth(), base.getDate() + i)
    const year = cur.getFullYear()
    const month = String(cur.getMonth() + 1).padStart(2, '0')
    const day = String(cur.getDate()).padStart(2, '0')
    const dateStr = `${year}-${month}-${day}`

    list.push({
      dateStr,
      weekdayShort: cur.toLocaleDateString('en-US', { weekday: 'short' }),
      monthShort: cur.toLocaleDateString('en-US', { month: 'short' }),
      dayNum: cur.getDate(),
      isToday: i === 0,
      isTomorrow: i === 1,
      isSunday: cur.getDay() === 0,
      fullFormatted: cur.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }),
    })
  }
  return list
})

// Current selected day metadata
const selectedDayMeta = computed(() => {
  if (!props.appointmentDate) return null
  const found = daysList.value.find((d) => d.dateStr === props.appointmentDate)
  if (found) return found

  const [y, m, d] = props.appointmentDate.split('-').map(Number)
  if (y && m && d) {
    const cur = new Date(y, m - 1, d)
    return {
      dateStr: props.appointmentDate,
      fullFormatted: cur.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }),
    }
  }
  return null
})

// Quick check helpers
function isDaySelected(offsetDays) {
  const target = daysList.value[offsetDays]
  return target && props.appointmentDate === target.dateStr
}

const isWeekendSelected = computed(() => {
  const found = daysList.value.find((d) => d.dateStr === props.appointmentDate)
  return found && (found.isSunday || found.weekdayShort === 'Sat')
})

function selectDate(dateStr) {
  if (props.appointmentDate === dateStr) return
  emit('update:appointmentDate', dateStr)
  emit('dateChange')
}

function selectRelativeDay(offsetDays) {
  const target = daysList.value[offsetDays]
  if (target) {
    selectDate(target.dateStr)
  }
}

function selectNextWeekend() {
  const saturday = daysList.value.find((d) => d.weekdayShort === 'Sat')
  if (saturday) {
    selectDate(saturday.dateStr)
  }
}

function handleCustomDateInput(val) {
  if (val) {
    selectDate(val)
  }
}

// Format "HH:mm" -> "hh:mm AM/PM" with padded hour for uniformity
function format12Hour(timeStr) {
  if (!timeStr) return ''
  const [hStr, mStr] = timeStr.split(':')
  const h = parseInt(hStr, 10)
  if (isNaN(h)) return timeStr
  const m = mStr || '00'
  const ampm = h >= 12 ? 'PM' : 'AM'
  const displayH = h % 12 === 0 ? 12 : h % 12
  return `${String(displayH).padStart(2, '0')}:${m} ${ampm}`
}

function getSlotPeriod(timeStr) {
  const hour = parseInt(timeStr.split(':')[0], 10)
  if (hour < 12) return 'morning'
  if (hour < 17) return 'afternoon'
  return 'evening'
}

// Bucketing slots for filter tabs
const slotBuckets = computed(() => {
  const morning = []
  const afternoon = []
  const evening = []

  props.slots.forEach((slot) => {
    const p = getSlotPeriod(slot)
    if (p === 'morning') morning.push(slot)
    else if (p === 'afternoon') afternoon.push(slot)
    else evening.push(slot)
  })

  return {
    all: props.slots,
    morning,
    afternoon,
    evening,
  }
})

// Current filtered slots based on period tab
const filteredSlots = computed(() => {
  return slotBuckets.value[selectedPeriod.value] || []
})

// Reset to 'all' if active period tab has no slots after date change
watch(
  () => props.slots,
  (newSlots) => {
    if (selectedPeriod.value !== 'all' && slotBuckets.value[selectedPeriod.value]?.length === 0 && newSlots.length > 0) {
      selectedPeriod.value = 'all'
    }
  }
)
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
