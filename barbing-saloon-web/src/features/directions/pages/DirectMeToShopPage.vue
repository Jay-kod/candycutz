<template>
  <component :is="layoutComponent">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:py-8">
      <div class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="space-y-1">
          <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
            Destination Locked • Verified Shop Pin
          </span>
          <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ shop?.name || 'CandyCutz Studio' }}</h1>
          <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ shop?.address || 'Loading shop address...' }}</p>
          <p v-if="shop?.hours_summary" class="text-xs text-zinc-500 dark:text-zinc-400">Open: {{ shop.hours_summary }}</p>
        </div>

        <div v-if="shop" class="flex flex-wrap gap-2">
          <a v-if="shop.phone" :href="`tel:${shop.phone}`" class="rounded-lg border border-zinc-300 px-3.5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
            Call Salon
          </a>
          <a v-if="whatsappUrl" :href="whatsappUrl" target="_blank" rel="noopener noreferrer" class="rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
            WhatsApp Us
          </a>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-[1.35fr_0.95fr]">
        <div class="space-y-4">
          <LiveDirectionsMap :shop="shop" :origin="currentOrigin" :road-route="roadRoute" />

          <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-3.5 dark:border-zinc-800 dark:bg-zinc-900">
              <p class="text-[11px] uppercase text-zinc-500">Distance</p>
              <p class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ activeDistanceKm !== null ? `${activeDistanceKm} km` : '—' }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-3.5 dark:border-zinc-800 dark:bg-zinc-900">
              <p class="text-[11px] uppercase text-zinc-500">Est. Drive</p>
              <p class="mt-1 text-lg font-bold text-amber-600">{{ activeDriveMinutes !== null ? `${activeDriveMinutes} min` : '—' }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-3.5 dark:border-zinc-800 dark:bg-zinc-900">
              <p class="text-[11px] uppercase text-zinc-500">Est. Walk</p>
              <p class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ activeWalkMinutes !== null ? `${activeWalkMinutes} min` : '—' }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-3.5 dark:border-zinc-800 dark:bg-zinc-900">
              <p class="text-[11px] uppercase text-zinc-500">Heading</p>
              <p class="mt-1 text-lg font-bold text-zinc-900 dark:text-white">{{ activeBearing || '—' }}</p>
            </div>
          </div>

          <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Turn-by-Turn Voice Navigation</h2>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Open your preferred maps app with both your origin and our locked shop destination pre-loaded.</p>
            <div class="mt-3 grid grid-cols-2 gap-2.5 sm:grid-cols-4">
              <a :href="links.google_maps" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-amber-600 px-3 py-2.5 text-center text-xs font-bold text-white hover:bg-amber-700">
                Google Maps
              </a>
              <a :href="links.apple_maps" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-zinc-300 px-3 py-2.5 text-center text-xs font-semibold text-zinc-800 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                Apple Maps
              </a>
              <a :href="links.waze" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-zinc-300 px-3 py-2.5 text-center text-xs font-semibold text-zinc-800 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                Waze
              </a>
              <a :href="links.openstreetmap" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-zinc-300 px-3 py-2.5 text-center text-xs font-semibold text-zinc-800 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                OpenStreetMap
              </a>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Choose Your Starting Point</h2>

            <button
              type="button"
              :class="trackingLive ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-zinc-900 text-white hover:bg-zinc-800 dark:bg-amber-600 dark:hover:bg-amber-700'"
              class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition"
              @click="trackingLive ? stopLiveTracking() : startLiveTracking()"
            >
              <span>{{ trackingLive ? 'Live GPS Tracking Active (Tap to Pause)' : 'Use My Live GPS Location' }}</span>
            </button>

            <p v-if="geoError" class="mt-2 text-xs text-rose-600">{{ geoError }}</p>

            <form class="mt-4 space-y-2 border-t border-zinc-100 pt-4 dark:border-zinc-800" @submit.prevent="searchManualAddress">
              <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Or Enter Street / Area</label>
              <div class="flex gap-2">
                <input
                  v-model="manualAddressText"
                  placeholder="e.g. NSUK Gate, Keffi or Total Filling Station"
                  class="min-w-0 flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                />
                <button
                  type="submit"
                  :disabled="resolvingRoute"
                  class="shrink-0 rounded-lg border border-zinc-300 px-3.5 py-2 text-xs font-semibold text-zinc-800 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                  {{ resolvingRoute ? '...' : 'Locate' }}
                </button>
              </div>
            </form>

            <div v-if="geocodeResults.length" class="mt-3 space-y-1.5">
              <p class="text-[11px] font-medium text-zinc-500">Matching places:</p>
              <button
                v-for="(item, idx) in geocodeResults"
                :key="idx"
                type="button"
                class="block w-full truncate rounded-lg border border-zinc-200 px-2.5 py-1.5 text-left text-[11px] text-zinc-700 hover:border-amber-500 dark:border-zinc-800 dark:text-zinc-300"
                @click="selectOrigin(item.latitude, item.longitude, item.label, 'manual')"
              >
                {{ item.label }}
              </button>
            </div>
          </div>

          <AddressBookCard
            :is-authenticated="isAuthenticated"
            :addresses="addresses"
            :selected-id="selectedAddressId"
            :saving="savingAddress"
            @save="saveNewAddress"
            @select="chooseSavedAddress"
            @remove="removeAddress"
          />
        </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { getActivePinia } from 'pinia'
import PublicLayout from '@/core/layouts/PublicLayout.vue'
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue'
import { useAuthStore } from '@/modules/auth/store/auth.store'
import { useShopDirections } from '../composables/useShopDirections'
import LiveDirectionsMap from '../components/LiveDirectionsMap.vue'
import AddressBookCard from '../components/AddressBookCard.vue'

const route = useRoute()
const authStore = getActivePinia() ? useAuthStore() : null
const isAuthenticated = computed(() => Boolean(authStore?.token || authStore?.user?.id))
const layoutComponent = computed(() => (route.path.startsWith('/customer') ? CustomerLayout : PublicLayout))

const {
  shop,
  addresses,
  trackingLive,
  resolvingRoute,
  savingAddress,
  geoError,
  selectedAddressId,
  manualAddressText,
  currentOrigin,
  geocodeResults,
  links,
  roadRoute,
  activeDistanceKm,
  activeDriveMinutes,
  activeWalkMinutes,
  activeBearing,
  init,
  startLiveTracking,
  stopLiveTracking,
  searchManualAddress,
  selectOrigin,
  chooseSavedAddress,
  saveNewAddress,
  removeAddress,
} = useShopDirections(isAuthenticated.value)

const whatsappUrl = computed(() => {
  const digits = String(shop.value?.whatsapp || shop.value?.phone || '').replace(/[^\d]/g, '')
  return digits ? `https://wa.me/${digits}` : ''
})

onMounted(init)
</script>
