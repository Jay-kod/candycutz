<template>
  <component :is="layoutComponent">
    <div class="mx-auto max-w-6xl space-y-4 sm:space-y-5 px-3 py-4 sm:px-6 lg:py-8">
      <!-- Locked Shop Destination Header -->
      <header class="relative overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 sm:p-5 shadow-xs">
        <div
          class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-[var(--color-accent)]/10 blur-2xl"
          aria-hidden="true"
        />
        <div class="relative flex flex-col gap-3.5 sm:flex-row sm:items-center sm:justify-between">
          <div class="min-w-0 flex-1 space-y-1.5">
            <div class="flex flex-wrap items-center gap-2">
              <span
                :class="[
                  'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                  shop?.location_lock && !shop.location_lock.verified
                    ? 'border-rose-500/40 bg-rose-500/10 text-rose-700 dark:text-rose-300'
                    : 'border-[var(--color-accent)]/30 bg-[var(--color-accent)]/10 text-[var(--color-accent)]',
                ]"
              >
                {{
                  shop?.location_lock && !shop.location_lock.verified
                    ? 'Shop Map Issue Detected'
                    : 'Shop Locked • Pin 1 on Map'
                }}
              </span>
              <span v-if="shop?.hours_summary" class="inline-flex items-center rounded-full border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-2.5 py-0.5 text-[11px] font-medium text-[var(--color-text-secondary)]">
                {{ shop.hours_summary }}
              </span>
            </div>
            <h1 class="font-display text-xl sm:text-3xl font-bold tracking-tight text-[var(--color-text-primary)]">
              {{ shop?.name || 'CandyCutz Studio' }}
            </h1>
            <p class="text-xs sm:text-sm text-[var(--color-text-secondary)]">
              Find our shop anchored at Pin 1 on the map, add your address or Pin 2, and follow the live shortest route.
            </p>
          </div>

          <div v-if="shop" class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center shrink-0">
            <a
              v-if="shop.phone"
              :href="`tel:${shop.phone}`"
              class="inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-3.5 py-2 text-xs font-semibold text-[var(--color-text-primary)] transition hover:border-[var(--color-accent)]/50 hover:text-[var(--color-accent)]"
            >
              Call Salon
            </a>
            <a
              v-if="whatsappUrl"
              :href="whatsappUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700"
            >
              WhatsApp Us
            </a>
          </div>
        </div>
      </header>

      <!-- Shop Lock Warning if Admin Coords/Embed Disagree -->
      <div
        v-if="shop?.location_lock && !shop.location_lock.verified"
        role="alert"
        class="rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-xs text-rose-800 dark:text-rose-200"
      >
        <p class="font-bold uppercase tracking-wide text-[11px]">Shop Map Lock Warning</p>
        <p class="mt-0.5">{{ shop.location_lock.message }}</p>
      </div>

      <!-- Restructured 2-Column Cockpit -->
      <div class="grid items-start gap-5 lg:grid-cols-[1.45fr_0.95fr]">
        <!-- Left Stage: Single Unified Map + Telemetry + Turn Guide -->
        <div ref="mapSectionRef" class="space-y-4 min-w-0">
          <LiveDirectionsMap
            :shop="shop"
            :origin="currentOrigin"
            :route="roadRoute"
            :fit-trigger="fitTrigger"
            :tracking-live="trackingLive"
            :pin-mode-active="pinModeActive"
            :origin-locked="originLocked"
            :active-step="activeRouteStep"
            @pin-origin="handleMapPinDrop"
            @toggle-live-gps="trackingLive ? stopLiveTracking() : startLiveTracking()"
            @toggle-origin-lock="handleToggleOriginLock"
            @locked-interaction="notifyLockedOrigin"
          />

          <!-- Quick Current Location + Preferred Pin Lock Strip -->
          <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <button
              type="button"
              :disabled="locatingGps"
              class="flex min-h-[42px] items-center justify-center rounded-xl bg-[var(--color-accent)] px-3.5 py-2.5 text-xs font-bold text-[var(--color-accent-foreground)] shadow-xs transition hover:opacity-90 disabled:opacity-60 cursor-pointer"
              @click="handleUseCurrentLocation"
            >
              {{ locatingGps ? 'Checking Device Location...' : 'Use My Current Location' }}
            </button>

            <button
              v-if="currentOrigin"
              type="button"
              :aria-pressed="originLocked"
              :class="[
                'flex min-h-[42px] items-center justify-center rounded-xl border px-3.5 py-2.5 text-xs font-bold transition cursor-pointer',
                originLocked
                  ? 'border-amber-600/50 bg-amber-500/15 text-amber-800 hover:bg-amber-500/25 dark:text-amber-300'
                  : 'border-emerald-600/50 bg-emerald-600 text-white hover:bg-emerald-700',
              ]"
              @click="handleToggleOriginLock"
            >
              {{ originLocked ? 'Preferred Location Locked • Unlock' : 'Lock Preferred Location' }}
            </button>

            <button
              v-else
              type="button"
              :class="[
                'flex min-h-[42px] items-center justify-center rounded-xl border px-3.5 py-2.5 text-xs font-bold transition cursor-pointer',
                pinModeActive
                  ? 'border-emerald-600 bg-emerald-600 text-white'
                  : 'border-emerald-600/40 bg-emerald-500/10 text-emerald-700 hover:bg-emerald-500/20 dark:text-emerald-300',
              ]"
              @click="activateMapPinMode"
            >
              {{ pinModeActive ? 'Tap Map Above Now' : 'Tap Map to Pin' }}
            </button>
          </div>

          <!-- Inline Device Location Prompt when GPS/Permission is Off or Denied -->
          <div
            v-if="locationPromptCopy"
            role="status"
            class="rounded-2xl border border-amber-500/40 bg-amber-500/10 p-3.5 text-xs space-y-2.5"
          >
            <div class="space-y-1">
              <p class="font-bold text-amber-900 dark:text-amber-200">{{ locationPromptCopy.title }}</p>
              <p class="text-amber-800 dark:text-amber-300">{{ locationPromptCopy.description }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                :disabled="locatingGps"
                class="rounded-lg bg-[var(--color-accent)] px-3 py-1.5 text-[11px] font-bold text-[var(--color-accent-foreground)] hover:opacity-90 disabled:opacity-60 cursor-pointer"
                @click="handleUseCurrentLocation"
              >
                {{ locatingGps ? 'Checking...' : 'Try Current Location Again' }}
              </button>
              <button
                type="button"
                class="rounded-lg border border-amber-600/40 bg-[var(--color-bg-primary)] px-3 py-1.5 text-[11px] font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)] cursor-pointer"
                @click="originLocked ? unlockOrigin() : activateMapPinMode()"
              >
                {{ originLocked ? 'Unlock Pin to Pick on Map' : 'Tap Map to Pin Instead' }}
              </button>
            </div>
          </div>

          <!-- Telemetry Strip -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-px overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-border)] shadow-xs">
            <div class="bg-[var(--color-bg-secondary)] p-3 sm:px-4 sm:py-3.5">
              <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--color-text-muted)]">Distance</p>
              <p class="mt-0.5 text-base sm:text-lg font-bold tabular-nums text-[var(--color-text-primary)]">
                {{ activeDistanceKm !== null ? `${activeDistanceKm} km` : '—' }}
              </p>
            </div>
            <div class="bg-[var(--color-bg-secondary)] p-3 sm:px-4 sm:py-3.5">
              <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--color-text-muted)]">Est. Drive</p>
              <p class="mt-0.5 text-base sm:text-lg font-bold tabular-nums text-[var(--color-accent)]">
                {{ activeDriveMinutes !== null ? `${activeDriveMinutes} min` : '—' }}
              </p>
            </div>
            <div class="bg-[var(--color-bg-secondary)] p-3 sm:px-4 sm:py-3.5">
              <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--color-text-muted)]">Est. Walk</p>
              <p class="mt-0.5 text-base sm:text-lg font-bold tabular-nums text-[var(--color-text-primary)]">
                {{ activeWalkMinutes !== null ? `${activeWalkMinutes} min` : '—' }}
              </p>
            </div>
            <div class="bg-[var(--color-bg-secondary)] p-3 sm:px-4 sm:py-3.5">
              <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--color-text-muted)]">Heading</p>
              <p class="mt-0.5 text-base sm:text-lg font-bold text-[var(--color-text-primary)]">
                {{ activeBearing || '—' }}
              </p>
            </div>
          </div>

          <!-- In-Website Shortest Route & Live Navigation Guide -->
          <section
            v-if="routeSteps.length"
            class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 sm:p-5 shadow-xs space-y-3.5"
          >
            <div class="flex flex-wrap items-center justify-between gap-2">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <h2 class="text-sm font-bold text-[var(--color-text-primary)]">Shortest Live Route Guide</h2>
                  <span class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                    {{ roadRoute?.isFallback ? 'Direct Guide' : roadRoute?.profile === 'walking' ? 'Shortest Path' : 'Shortest Road Route' }}
                  </span>
                </div>
                <p class="mt-0.5 truncate text-xs text-[var(--color-text-secondary)]">
                  {{ currentOrigin?.label }} → {{ shop?.name || 'CandyCutz Studio' }}
                </p>
              </div>

              <div class="flex flex-wrap items-center gap-1.5">
                <div
                  role="group"
                  aria-label="Route travel mode"
                  class="inline-flex items-center rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-primary)] p-0.5"
                >
                  <button
                    type="button"
                    :aria-pressed="travelMode === 'driving'"
                    :class="[
                      'rounded-lg px-2.5 py-1 text-[11px] font-bold transition cursor-pointer',
                      travelMode === 'driving'
                        ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)]'
                        : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]',
                    ]"
                    @click="setTravelMode('driving')"
                  >
                    Drive
                  </button>
                  <button
                    type="button"
                    :aria-pressed="travelMode === 'walking'"
                    :class="[
                      'rounded-lg px-2.5 py-1 text-[11px] font-bold transition cursor-pointer',
                      travelMode === 'walking'
                        ? 'bg-emerald-600 text-white'
                        : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]',
                    ]"
                    @click="setTravelMode('walking')"
                  >
                    Walk
                  </button>
                </div>

                <button
                  v-if="speechSupported"
                  type="button"
                  :aria-pressed="voiceEnabled"
                  :class="[
                    'rounded-xl border px-2.5 py-1.5 text-xs font-semibold transition cursor-pointer',
                    voiceEnabled
                      ? 'border-emerald-600/40 bg-emerald-500/15 text-emerald-700 dark:text-emerald-300'
                      : 'border-[var(--color-border)] bg-[var(--color-bg-primary)] text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]',
                  ]"
                  @click="toggleVoiceGuidance"
                >
                  {{ voiceEnabled ? 'Voice On' : 'Voice Off' }}
                </button>

                <button
                  type="button"
                  class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-2.5 py-1.5 text-xs font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/50 cursor-pointer"
                  @click="showRouteSteps = !showRouteSteps"
                >
                  {{ showRouteSteps ? 'Hide Steps' : `View ${routeSteps.length} Steps` }}
                </button>
              </div>
            </div>

            <!-- Active Turn Callout -->
            <div
              v-if="showRouteSteps && activeRouteStep"
              class="rounded-xl border border-[var(--color-accent)]/40 bg-[var(--color-accent)]/10 p-3.5 space-y-2"
            >
              <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-accent)]">
                  Step {{ activeStepIndex + 1 }} of {{ routeSteps.length }}
                </span>
                <div class="flex items-center gap-1.5">
                  <button
                    type="button"
                    :disabled="activeStepIndex <= 0"
                    class="rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-2.5 py-1 text-[11px] font-semibold text-[var(--color-text-primary)] disabled:opacity-40 cursor-pointer"
                    @click="selectStep(activeStepIndex - 1)"
                  >
                    Prev
                  </button>
                  <button
                    type="button"
                    :disabled="activeStepIndex >= routeSteps.length - 1"
                    class="rounded-lg bg-[var(--color-accent)] px-2.5 py-1 text-[11px] font-bold text-[var(--color-accent-foreground)] disabled:opacity-40 cursor-pointer"
                    @click="selectStep(activeStepIndex + 1)"
                  >
                    Next Step
                  </button>
                </div>
              </div>
              <p class="text-sm font-bold text-[var(--color-text-primary)]">
                {{ activeRouteStep.instruction }}
              </p>
              <p v-if="activeRouteStep.distanceMeters" class="font-mono text-[11px] text-[var(--color-text-secondary)]">
                For {{ formatStepDistance(activeRouteStep.distanceMeters) }}
              </p>
            </div>

            <ol v-if="showRouteSteps" class="max-h-56 space-y-2 overflow-y-auto pr-1">
              <li
                v-for="(step, idx) in routeSteps"
                :key="idx"
                :class="[
                  'flex items-start justify-between gap-3 rounded-xl border px-3 py-2 text-xs transition cursor-pointer',
                  idx === activeStepIndex
                    ? 'border-[var(--color-accent)] bg-[var(--color-accent)]/10'
                    : 'border-[var(--color-border)] bg-[var(--color-bg-primary)] hover:border-[var(--color-accent)]/40',
                ]"
                @click="selectStep(idx)"
              >
                <div class="flex items-start gap-2.5 min-w-0">
                  <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[var(--color-accent)]/15 text-[10px] font-bold text-[var(--color-accent)]">
                    {{ idx + 1 }}
                  </span>
                  <span class="font-medium text-[var(--color-text-primary)]">{{ step.instruction }}</span>
                </div>
                <span class="shrink-0 font-mono text-[11px] text-[var(--color-text-muted)]">
                  {{ formatStepDistance(step.distanceMeters) }}
                </span>
              </li>
            </ol>
          </section>
        </div>

        <!-- Right Panel: Clean Unified Customer Address Editor + Saved Addresses -->
        <div class="space-y-4 min-w-0">
          <!-- Card A: Customer Address & Pin 2 Editor -->
          <section class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 sm:p-5 shadow-xs space-y-3.5">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <div>
                <h2 class="text-sm font-bold text-[var(--color-text-primary)]">
                  {{ draftOrigin.id ? `Your Address (${draftOrigin.title})` : 'Add Your Address & Location' }}
                </h2>
                <p class="text-[11px] text-[var(--color-text-secondary)]">
                  Set where you are starting from, pin your spot on the map, and save.
                </p>
              </div>
              <button
                v-if="draftOrigin.id"
                type="button"
                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-2.5 py-1 text-[11px] font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)] cursor-pointer"
                @click="startNewAddress({ keepCoordinates: true })"
              >
                + New Address
              </button>
            </div>

            <!-- Address Title & City/State -->
            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
              <div>
                <label class="block text-[11px] font-semibold text-[var(--color-text-secondary)]">Address Label</label>
                <input
                  v-model="draftOrigin.title"
                  placeholder="e.g. Home, Office"
                  class="mt-1 w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-3 py-2 text-xs text-[var(--color-text-primary)] outline-none focus:border-[var(--color-accent)]"
                  @input="markDraftEdited"
                />
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-[var(--color-text-secondary)]">Nearby Landmark (Optional)</label>
                <input
                  v-model="draftOrigin.landmark"
                  placeholder="e.g. Near Total Filling Station"
                  class="mt-1 w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-3 py-2 text-xs text-[var(--color-text-primary)] outline-none focus:border-[var(--color-accent)]"
                  @input="markDraftEdited"
                />
              </div>
            </div>

            <!-- Street / Search Place / Paste Google Maps Link -->
            <div>
              <label class="block text-[11px] font-semibold text-[var(--color-text-secondary)]">
                Street, Area, or Google Maps Link
              </label>
              <form class="mt-1 flex gap-2" @submit.prevent="searchPlaces()">
                <input
                  v-model="draftOrigin.street"
                  placeholder="e.g. Angwan Kare, NSUK Gate, or paste Google Maps link"
                  class="min-w-0 flex-1 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-3 py-2 text-xs text-[var(--color-text-primary)] outline-none focus:border-[var(--color-accent)]"
                  @input="markDraftEdited"
                />
                <button
                  type="submit"
                  :disabled="searchingPlaces"
                  class="shrink-0 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-3 py-2 text-xs font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/50 cursor-pointer"
                >
                  {{ searchingPlaces ? '...' : 'Find' }}
                </button>
              </form>

              <div v-if="searchResults.length" class="mt-2 space-y-1.5">
                <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Select matching location (Keffi biased):</p>
                <button
                  v-for="(item, idx) in searchResults"
                  :key="idx"
                  type="button"
                  class="flex w-full items-center justify-between gap-2 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-2.5 py-1.5 text-left text-[11px] text-[var(--color-text-secondary)] hover:border-[var(--color-accent)] cursor-pointer"
                  @click="applySearchResult(item)"
                >
                  <span class="truncate">{{ item.label }}</span>
                  <span v-if="item.distance_km !== null && item.distance_km !== undefined" class="shrink-0 font-mono text-[10px] text-[var(--color-text-muted)]">
                    {{ item.distance_km }} km
                  </span>
                </button>
              </div>
            </div>

            <!-- Two Clear Pin 2 Controls -->
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
              <button
                type="button"
                :disabled="locatingGps"
                class="flex min-h-[42px] items-center justify-center rounded-xl bg-[var(--color-accent)] px-3.5 py-2.5 text-xs font-bold text-[var(--color-accent-foreground)] shadow-xs transition hover:opacity-90 disabled:opacity-60 cursor-pointer"
                @click="handleUseCurrentLocation"
              >
                {{ locatingGps ? 'Checking Location...' : 'Use My Current Location' }}
              </button>
              <button
                type="button"
                :class="[
                  'flex min-h-[42px] items-center justify-center rounded-xl border px-3.5 py-2.5 text-xs font-bold transition cursor-pointer',
                  originLocked
                    ? 'border-amber-500/40 bg-amber-500/10 text-amber-800 dark:text-amber-300'
                    : pinModeActive
                      ? 'border-emerald-600 bg-emerald-600 text-white'
                      : 'border-emerald-600/40 bg-emerald-500/10 text-emerald-700 hover:bg-emerald-500/20 dark:text-emerald-300',
                ]"
                @click="originLocked ? notifyLockedOrigin() : activateMapPinMode()"
              >
                {{
                  originLocked
                    ? 'Pin Locked (Unlock Below)'
                    : pinModeActive
                      ? 'Tap Map Above Now'
                      : 'Tap Map to Pin'
                }}
              </button>
            </div>

            <!-- GPS Errors / Coarse Drift Warnings -->
            <p v-if="geoError && !locationPromptCopy" class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs font-medium text-rose-700 dark:text-rose-300">
              {{ geoError }}
            </p>
            <p v-else-if="geoWarning" class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-800 dark:text-amber-300">
              {{ geoWarning }}
            </p>

            <!-- Exact Pin 2 Coordinate Readout & Preferred Lock Toggle -->
            <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-3 py-2.5 text-xs space-y-2">
              <div class="flex flex-wrap items-center justify-between gap-1.5">
                <span class="font-semibold text-[var(--color-text-primary)]">
                  {{
                    originLocked && currentOrigin
                      ? 'Pin 2 Locked (Preferred Start)'
                      : currentOrigin
                        ? 'Pin 2 Active on Map'
                        : 'No Map Pin Yet'
                  }}
                </span>
                <div class="flex flex-wrap items-center gap-1.5">
                  <span
                    v-if="originLocked"
                    class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-300"
                  >
                    Locked
                  </span>
                  <span
                    v-if="currentOrigin?.accuracy"
                    class="rounded-full bg-emerald-500/15 px-2 py-0.5 font-mono text-[10px] font-semibold text-emerald-700 dark:text-emerald-300"
                  >
                    ±{{ currentOrigin.accuracy }}m GPS
                  </span>
                  <span
                    v-if="draftOrigin.isDirty"
                    class="rounded-full bg-amber-500/15 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300"
                  >
                    Unsaved Edits
                  </span>
                </div>
              </div>
              <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-mono text-[11px] text-[var(--color-text-muted)]">
                  {{ currentOrigin ? `${currentOrigin.latitude}, ${currentOrigin.longitude}` : 'Tap the map or click Use My Current Location' }}
                </p>
                <button
                  v-if="currentOrigin"
                  type="button"
                  :class="[
                    'rounded-lg border px-2.5 py-1 text-[11px] font-bold transition cursor-pointer',
                    originLocked
                      ? 'border-amber-500/50 bg-amber-500/15 text-amber-800 hover:bg-amber-500/25 dark:text-amber-300'
                      : 'border-emerald-600/40 bg-emerald-500/10 text-emerald-700 hover:bg-emerald-500/20 dark:text-emerald-300',
                  ]"
                  @click="handleToggleOriginLock"
                >
                  {{ originLocked ? 'Unlock Location' : 'Lock Location' }}
                </button>
              </div>
            </div>

            <!-- Single Explicit Save + Live Navigation Controls -->
            <div class="space-y-2 pt-1">
              <button
                type="button"
                :disabled="savingAddress"
                class="flex min-h-[44px] w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60 cursor-pointer"
                @click="handleSaveAndRoute"
              >
                {{
                  savingAddress
                    ? 'Saving Address...'
                    : draftOrigin.id
                      ? 'Save Changes & Show Route'
                      : 'Save Address & Show Route'
                }}
              </button>

              <button
                type="button"
                class="flex min-h-[40px] w-full items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-4 py-2 text-xs font-semibold text-[var(--color-text-primary)] transition hover:border-[var(--color-accent)]/50 cursor-pointer"
                @click="handleToggleLiveNavigation"
              >
                {{ trackingLive ? 'Pause Live GPS Location' : 'Start Live GPS Tracking to Shop' }}
              </button>
            </div>
          </section>

          <!-- Card B: Saved Addresses -->
          <AddressBookCard
            :is-authenticated="isAuthenticated"
            :addresses="addresses"
            :active-id="draftOrigin.id"
            @select="handleSelectSavedAddress"
            @remove="removeAddress"
          />
        </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import PublicLayout from '@/core/layouts/PublicLayout.vue'
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue'
import { useAuthStore } from '@/modules/auth/store/auth.store'
import { useShopDirections } from '../composables/useShopDirections'
import LiveDirectionsMap from '../components/LiveDirectionsMap.vue'
import AddressBookCard from '../components/AddressBookCard.vue'

const route = useRoute()
let authStore = null
try {
  authStore = useAuthStore()
} catch {
  authStore = null
}
const isAuthenticated = computed(() => Boolean(authStore?.token || authStore?.user?.id))
const layoutComponent = computed(() => (route.path.startsWith('/customer') ? CustomerLayout : PublicLayout))

const mapSectionRef = ref(null)
const pinModeActive = ref(false)
const showRouteSteps = ref(false)
const activeStepIndex = ref(0)
const voiceEnabled = ref(false)

const speechSupported =
  typeof window !== 'undefined' && 'speechSynthesis' in window && typeof SpeechSynthesisUtterance !== 'undefined'

const {
  shop,
  addresses,
  draftOrigin,
  currentOrigin,
  searchResults,
  locatingGps,
  trackingLive,
  searchingPlaces,
  savingAddress,
  geoError,
  geoWarning,
  locationPromptState,
  originLocked,
  travelMode,
  fitTrigger,
  roadRoute,
  activeDistanceKm,
  activeDriveMinutes,
  activeWalkMinutes,
  activeBearing,
  init,
  setTravelMode,
  setPinOnMap,
  pickCurrentLocation,
  startLiveTracking,
  stopLiveTracking,
  unlockOrigin,
  toggleOriginLock,
  notifyLockedOrigin,
  searchPlaces,
  applySearchResult,
  selectSavedAddress,
  startNewAddress,
  markDraftEdited,
  saveCustomerAddress,
  removeAddress,
} = useShopDirections(isAuthenticated)

const routeSteps = computed(() => (Array.isArray(roadRoute.value?.steps) ? roadRoute.value.steps : []))
const activeRouteStep = computed(() => routeSteps.value[activeStepIndex.value] || null)

const locationPromptCopy = computed(() => {
  const mode = locationPromptState?.value
  if (mode === 'denied') {
    return {
      title: 'Device or Browser Location Is Turned Off',
      description:
        geoError.value ||
        'Allow location access in your browser/device Location settings so we can detect where you are and route to Candycutz.',
    }
  }
  if (mode === 'needs_enable') {
    return {
      title: 'Enable Device Location (GPS)',
      description:
        geoError.value ||
        'Turn on your phone or computer Location services, then tap "Try Current Location Again" to show your live pin and route.',
    }
  }
  if (mode === 'unavailable') {
    return {
      title: 'Current Location Unavailable',
      description:
        geoError.value ||
        'Automatic location is not available in this browser session. Please tap your exact street on the map instead.',
    }
  }
  return null
})

watch(routeSteps, () => {
  activeStepIndex.value = 0
})

function formatStepDistance(meters) {
  const m = Number(meters)
  if (!Number.isFinite(m) || m <= 0) return '—'
  return m >= 1000 ? `${(m / 1000).toFixed(1)} km` : `${Math.round(m)} m`
}

function speakInstruction(text) {
  if (!speechSupported || !voiceEnabled.value || !text) return
  try {
    window.speechSynthesis.cancel()
    const utterance = new SpeechSynthesisUtterance(text)
    utterance.rate = 1
    window.speechSynthesis.speak(utterance)
  } catch {
    // Ignore speech synthesis errors cleanly
  }
}

function stopVoice() {
  if (speechSupported) {
    try {
      window.speechSynthesis.cancel()
    } catch {
      // Ignore cleanup errors
    }
  }
}

function toggleVoiceGuidance() {
  voiceEnabled.value = !voiceEnabled.value
  if (voiceEnabled.value && activeRouteStep.value?.instruction) {
    speakInstruction(activeRouteStep.value.instruction)
  } else {
    stopVoice()
  }
}

function selectStep(idx) {
  if (idx < 0 || idx >= routeSteps.value.length) return
  activeStepIndex.value = idx
  const step = routeSteps.value[idx]
  if (step?.instruction) {
    speakInstruction(step.instruction)
  }
}

function activateMapPinMode() {
  if (originLocked.value) {
    pinModeActive.value = false
    notifyLockedOrigin()
    return
  }
  pinModeActive.value = !pinModeActive.value
  if (pinModeActive.value) {
    mapSectionRef.value?.scrollIntoView?.({ behavior: 'smooth', block: 'start' })
  }
}

async function handleUseCurrentLocation() {
  pinModeActive.value = false
  const ok = await pickCurrentLocation({ auto: false })
  if (ok) {
    if (routeSteps.value.length > 0) {
      showRouteSteps.value = true
    }
    mapSectionRef.value?.scrollIntoView?.({ behavior: 'smooth', block: 'start' })
  }
}

function handleToggleOriginLock() {
  pinModeActive.value = false
  toggleOriginLock()
}

async function handleMapPinDrop({ latitude, longitude }) {
  pinModeActive.value = false
  const ok = await setPinOnMap(latitude, longitude, { recenter: false })
  if (ok && routeSteps.value.length > 0) {
    showRouteSteps.value = true
  }
}

async function handleSaveAndRoute() {
  const ok = await saveCustomerAddress()
  if (ok && routeSteps.value.length > 0) {
    showRouteSteps.value = true
    mapSectionRef.value?.scrollIntoView?.({ behavior: 'smooth', block: 'start' })
  }
}

async function handleSelectSavedAddress(addr) {
  pinModeActive.value = false
  await selectSavedAddress(addr)
  if (routeSteps.value.length > 0) {
    showRouteSteps.value = true
  }
}

function handleToggleLiveNavigation() {
  if (trackingLive.value) {
    stopLiveTracking()
    return
  }
  startLiveTracking()
  showRouteSteps.value = true
  mapSectionRef.value?.scrollIntoView?.({ behavior: 'smooth', block: 'start' })
}

const whatsappUrl = computed(() => {
  const digits = String(shop.value?.whatsapp || shop.value?.phone || '').replace(/[^\d]/g, '')
  return digits ? `https://wa.me/${digits}` : ''
})

onMounted(init)
onUnmounted(stopVoice)
</script>
