<template>
  <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div>
        <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Your Saved Addresses</h2>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">Save Home or Office once to route to the shop in one tap.</p>
      </div>
      <button
        v-if="isAuthenticated"
        type="button"
        class="rounded-lg border border-amber-500/40 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300"
        @click="showForm = !showForm"
      >
        {{ showForm ? 'Close Form' : '+ Add Address' }}
      </button>
    </div>

    <div v-if="!isAuthenticated" class="mt-3 rounded-xl bg-zinc-50 p-3.5 text-xs text-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-300">
      <RouterLink to="/customer/login" class="font-bold text-amber-600 underline hover:text-amber-700">Sign in as a customer</RouterLink>
      to save multiple personal addresses for future visits, or use Live GPS / manual search above.
    </div>

    <form v-if="isAuthenticated && showForm" class="mt-4 space-y-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50" @submit.prevent="submitAddress">
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Label</label>
          <input v-model="form.title" required placeholder="e.g. Home, Office" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Landmark (Optional)</label>
          <input v-model="form.landmark" placeholder="e.g. Opposite Total Station" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Street Address</label>
        <input v-model="form.street" required placeholder="e.g. 45 Unity Road" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">City</label>
          <input v-model="form.city" placeholder="Keffi" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">State</label>
          <input v-model="form.state" placeholder="Nasarawa" class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
        </div>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
        <button type="button" :disabled="locatingGps" class="text-xs font-semibold text-emerald-600 underline hover:text-emerald-700 disabled:opacity-60" @click="captureCurrentGps">
          {{ locatingGps ? 'Locating GPS...' : gpsCaptured ? '✓ GPS Coordinates Attached' : 'Pin Current GPS Location (Optional)' }}
        </button>
        <button type="submit" :disabled="saving" class="rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-700 disabled:opacity-60">
          {{ saving ? 'Saving...' : 'Save Address' }}
        </button>
      </div>
      <p v-if="gpsError" class="text-xs text-rose-600">{{ gpsError }}</p>
    </form>

    <div v-if="isAuthenticated && addresses.length" class="mt-4 space-y-2">
      <div
        v-for="addr in addresses"
        :key="addr.id"
        :class="selectedId === addr.id ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/20' : 'border-zinc-200 dark:border-zinc-800'"
        class="flex flex-wrap items-center justify-between gap-2 rounded-xl border p-3 text-xs transition"
      >
        <button type="button" class="min-w-0 flex-1 text-left" @click="$emit('select', addr)">
          <p class="font-bold text-zinc-900 dark:text-white">
            {{ addr.title }}
            <span v-if="addr.is_default" class="ml-1.5 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
              Default
            </span>
          </p>
          <p class="truncate text-zinc-500 dark:text-zinc-400">{{ addr.formatted }}</p>
        </button>
        <button type="button" class="text-[11px] font-semibold text-rose-600 hover:underline" @click="$emit('remove', addr.id)">
          Remove
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'

defineProps({
  isAuthenticated: { type: Boolean, default: false },
  addresses: { type: Array, default: () => [] },
  selectedId: { type: Number, default: null },
  saving: { type: Boolean, default: false },
})

const emit = defineEmits(['save', 'select', 'remove'])

const showForm = ref(false)
const locatingGps = ref(false)
const gpsError = ref('')
const form = reactive({
  title: 'Home',
  street: '',
  landmark: '',
  city: 'Keffi',
  state: 'Nasarawa',
  latitude: null,
  longitude: null,
  is_default: true,
})

const gpsCaptured = computed(() => form.latitude !== null && form.longitude !== null)

function captureCurrentGps() {
  gpsError.value = ''
  if (typeof navigator === 'undefined' || !navigator.geolocation) {
    gpsError.value = 'GPS services are unavailable in this browser.'
    return
  }
  locatingGps.value = true
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      form.latitude = Number(pos.coords.latitude.toFixed(6))
      form.longitude = Number(pos.coords.longitude.toFixed(6))
      locatingGps.value = false
    },
    (err) => {
      gpsError.value = err?.message || 'Unable to read current GPS coordinates.'
      locatingGps.value = false
    },
    { enableHighAccuracy: true, timeout: 10000 }
  )
}

async function submitAddress() {
  if (form.street.trim().length < 3) {
    gpsError.value = 'Please enter a valid street address.'
    return
  }
  emit('save', { ...form, street: form.street.trim(), title: (form.title || 'Home').trim() })
  form.street = ''
  form.landmark = ''
  form.latitude = null
  form.longitude = null
  gpsError.value = ''
  showForm.value = false
}
</script>
