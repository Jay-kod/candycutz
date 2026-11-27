<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-admin/15 border border-admin/30 flex items-center justify-center text-admin shadow-[0_0_15px_rgba(255,103,0,0.15)]">
          <WindowIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Homepage Hero & CTA Section</h2>
          <p class="text-xs text-white/50 mt-0.5">Primary visual hook, two-tone colored headline, supporting copy, action buttons, and background photography.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 01 • Hero & CTA
      </span>
    </div>

    <div class="grid lg:grid-cols-2 gap-8">
      <!-- Left: Headline, Copy & Buttons -->
      <div class="space-y-6">
        <!-- 1. Headline & Two-Tone Phrasing -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#0E1318]/90 p-4 space-y-4 shadow-lg backdrop-blur-sm">
          <div class="flex items-center justify-between pb-2 border-b border-white/[0.06]">
            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/90">
              <DocumentTextIcon class="h-3.5 w-3.5 text-admin" />
              Two-Tone Hero Headline
            </label>
            <span class="text-[10px] text-white/40 font-mono">Two Phrases • Dual Color</span>
          </div>

          <!-- Live Colored Headline Banner -->
          <div class="p-3.5 rounded-xl border border-white/[0.08] bg-black/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 shadow-inner">
            <span class="text-[10px] uppercase font-bold tracking-wider text-white/40 shrink-0">Live Headline:</span>
            <div class="text-base sm:text-lg font-display tracking-tight font-bold">
              <span :class="settings.cta_title_color_order === 'first-gold' ? 'text-[#D4AF37]' : 'text-white'">
                {{ settings.cta_title_part_one || 'Fresh Cut.' }}
              </span>
              {{ ' ' }}
              <span :class="settings.cta_title_color_order === 'first-gold' ? 'text-white' : 'text-[#D4AF37]'">
                {{ settings.cta_title_part_two || 'Zero Stress.' }}
              </span>
            </div>
          </div>

          <!-- Two Phrase Inputs -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
              <label class="block text-[11px] font-semibold text-white/70">Headline Phrase One</label>
              <input 
                v-model="settings.cta_title_part_one" 
                type="text" 
                maxlength="60"
                @input="onPhraseInput"
                class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-xs font-display text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-1 focus:ring-admin/20 transition-all outline-none" 
                placeholder="e.g. Fresh Cut."
              />
            </div>
            <div class="space-y-1.5">
              <label class="block text-[11px] font-semibold text-white/70">Headline Phrase Two</label>
              <input 
                v-model="settings.cta_title_part_two" 
                type="text" 
                maxlength="60"
                @input="onPhraseInput"
                class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-xs font-display text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-1 focus:ring-admin/20 transition-all outline-none" 
                placeholder="e.g. Zero Stress."
              />
            </div>
          </div>

          <!-- Headline Color Order -->
          <div class="space-y-1.5 pt-1">
            <label class="block text-[11px] font-semibold text-white/70">Headline Color Styling</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <button
                type="button"
                @click="settings.cta_title_color_order = 'first-white'"
                :class="[
                  'px-3 py-2 rounded-xl border text-xs font-medium text-left transition-all flex items-center justify-between',
                  (settings.cta_title_color_order || 'first-white') === 'first-white'
                    ? 'bg-admin/15 border-admin text-white shadow-sm'
                    : 'bg-white/[0.02] border-white/[0.08] text-white/60 hover:text-white hover:bg-white/[0.05]'
                ]"
              >
                <span>Phrase 1 White, Phrase 2 Gold</span>
                <span class="h-2 w-2 rounded-full" :class="(settings.cta_title_color_order || 'first-white') === 'first-white' ? 'bg-admin' : 'bg-white/20'"></span>
              </button>

              <button
                type="button"
                @click="settings.cta_title_color_order = 'first-gold'"
                :class="[
                  'px-3 py-2 rounded-xl border text-xs font-medium text-left transition-all flex items-center justify-between',
                  settings.cta_title_color_order === 'first-gold'
                    ? 'bg-admin/15 border-admin text-white shadow-sm'
                    : 'bg-white/[0.02] border-white/[0.08] text-white/60 hover:text-white hover:bg-white/[0.05]'
                ]"
              >
                <span>Phrase 1 Gold, Phrase 2 White</span>
                <span class="h-2 w-2 rounded-full" :class="settings.cta_title_color_order === 'first-gold' ? 'bg-admin' : 'bg-white/20'"></span>
              </button>
            </div>
          </div>
        </div>

        <!-- 2. Supporting Copy -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#0E1318]/90 p-4 space-y-4 shadow-lg backdrop-blur-sm">
          <div class="flex items-center justify-between pb-2 border-b border-white/[0.06]">
            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/90">
              <ChatBubbleBottomCenterTextIcon class="h-3.5 w-3.5 text-admin" />
              Supporting Copy Hierarchy
            </label>
            <span class="text-[10px] text-white/40 font-mono">Subtitle & Caption</span>
          </div>

          <!-- Main Supporting Subtitle -->
          <div class="space-y-1.5">
            <label class="block text-[11px] font-semibold text-white/70">Main Supporting Line</label>
            <textarea 
              v-model="settings.cta_subtitle" 
              @input="onSubtitleInput"
              rows="2" 
              maxlength="240"
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-xs text-white/90 placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-1 focus:ring-admin/20 resize-none transition-all outline-none leading-relaxed" 
              placeholder="e.g. Pick your barber, choose your style, lock your time."
            ></textarea>
          </div>

          <!-- Secondary Caption -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label class="block text-[11px] font-semibold text-white/70">Smaller Supporting Caption</label>
              <span class="text-[10px] text-white/40">Appears above booking buttons</span>
            </div>
            <textarea 
              v-model="settings.cta_caption" 
              rows="2" 
              maxlength="240"
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-xs text-white/80 placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-1 focus:ring-admin/20 resize-none transition-all outline-none leading-relaxed" 
              placeholder="e.g. Expert barbers. Fresh fades. A better kind of barbershop experience."
            ></textarea>
          </div>
        </div>

        <!-- 3. Action Buttons -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#0E1318]/90 p-4 space-y-3 shadow-lg backdrop-blur-sm">
          <div class="flex items-center justify-between pb-2 border-b border-white/[0.06]">
            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/90">
              <SparklesIcon class="h-3.5 w-3.5 text-admin" />
              Action Button Labels
            </label>
            <span class="text-[10px] text-white/40 font-mono">Primary & Secondary</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
              <label class="block text-[11px] font-semibold text-white/70">Primary Button (Booking)</label>
              <input 
                v-model="settings.cta_primary_text" 
                @input="onPrimaryBtnInput"
                type="text" 
                maxlength="40"
                class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2 text-xs font-semibold text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-1 focus:ring-admin/20 transition-all outline-none" 
                placeholder="e.g. Book Your Cut"
              />
            </div>
            <div class="space-y-1.5">
              <label class="block text-[11px] font-semibold text-white/70">Secondary Button (About)</label>
              <input 
                v-model="settings.hero_btn2_text" 
                type="text" 
                maxlength="40"
                class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2 text-xs font-semibold text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-1 focus:ring-admin/20 transition-all outline-none" 
                placeholder="e.g. About Us"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Image Upload & Opacity -->
      <div class="space-y-5">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/70">
            <PhotoIcon class="h-3.5 w-3.5 text-admin" />
            Background Showcase Image
          </label>
          <span v-if="(previewUrl || currentHeroImage) && !imageLoadError" class="text-xs text-white/50 font-mono">
            Opacity: <strong class="text-admin font-semibold">{{ settings.hero_image_opacity ?? 22 }}%</strong>
          </span>
        </div>
        
        <div 
          class="relative w-full aspect-video rounded-2xl border-2 border-dashed border-white/[0.12] bg-black/40 hover:border-admin/50 hover:bg-black/60 transition-all duration-300 group overflow-hidden flex flex-col items-center justify-center cursor-pointer shadow-inner"
          @click="$refs.fileInput.click()"
        >
          <!-- Current/Preview Image with dynamic opacity -->
          <img 
            v-if="(previewUrl || currentHeroImage) && !imageLoadError" 
            :src="previewUrl || getStorageUrl(currentHeroImage)" 
            @error="$emit('update:imageLoadError', true)"
            @load="$emit('update:imageLoadError', false)"
            :style="{ opacity: computedPreviewOpacity }"
            alt="Hero background preview"
            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500" 
          />
               
          <!-- Live Overlay Preview -->
          <div
            v-if="(previewUrl || currentHeroImage) && !imageLoadError"
            class="absolute inset-0 pointer-events-none transition-opacity duration-300"
            :style="{
              background: computedOverlayGradient,
              opacity: computedGoldOverlayOpacity
            }"
            aria-hidden="true"
          ></div>

          <!-- Live Headline & Copy Snippet in Preview -->
          <div 
            v-if="(previewUrl || currentHeroImage) && !imageLoadError"
            class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center z-10 pointer-events-none"
          >
            <div class="text-sm sm:text-base font-display font-bold drop-shadow-md">
              <span :class="settings.cta_title_color_order === 'first-gold' ? 'text-[#D4AF37]' : 'text-white'">
                {{ settings.cta_title_part_one || 'Fresh Cut.' }}
              </span>
              {{ ' ' }}
              <span :class="settings.cta_title_color_order === 'first-gold' ? 'text-white' : 'text-[#D4AF37]'">
                {{ settings.cta_title_part_two || 'Zero Stress.' }}
              </span>
            </div>
            <p class="text-[10px] text-white/80 line-clamp-1 max-w-xs mt-1 drop-shadow">
              {{ settings.cta_subtitle || settings.hero_subtitle }}
            </p>
          </div>

          <div 
            v-if="(previewUrl || currentHeroImage) && !imageLoadError" 
            class="absolute bottom-3 left-3 z-10 flex items-center gap-2 rounded-lg bg-[#0B0F13]/90 border border-white/10 px-2.5 py-1 text-[11px] font-mono text-white/80 backdrop-blur-md pointer-events-none"
          >
            <span>Photo: <strong class="text-admin">{{ settings.hero_image_opacity ?? 22 }}%</strong></span>
            <span class="text-white/20">•</span>
            <span class="inline-flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full border border-white/40" :style="{ backgroundColor: settings.hero_overlay_color || '#D4AF37' }"></span>
              <span>Overlay: <strong class="text-admin">{{ settings.hero_overlay_opacity ?? 45 }}%</strong></span>
            </span>
          </div>

          <!-- Upload Overlay -->
          <div 
            class="relative z-10 flex flex-col items-center justify-center p-6 text-center" 
            :class="((previewUrl || currentHeroImage) && !imageLoadError) ? 'opacity-0 group-hover:opacity-100 transition-opacity duration-300' : ''"
          >
            <div class="w-14 h-14 rounded-full bg-admin/15 border border-admin/30 flex items-center justify-center mb-3 text-admin group-hover:scale-110 transition-transform shadow-[0_0_20px_rgba(255,103,0,0.2)]">
              <CloudArrowUpIcon class="w-7 h-7" />
            </div>
            <p class="text-white font-display text-base mb-1">{{ ((previewUrl || currentHeroImage) && !imageLoadError) ? 'Replace Hero Image' : 'Upload Hero Image' }}</p>
            <p class="text-xs text-white/40">Drag & drop or click to browse</p>
            <p class="text-[10px] text-white/30 uppercase tracking-widest mt-3 border border-white/[0.08] bg-[#0B0F13]/80 px-2.5 py-0.5 rounded-full font-mono">1920×1080 • Max 2MB • JPG / PNG</p>
          </div>
          
          <input 
            ref="fileInput"
            type="file" 
            class="hidden"
            @change="onChange" 
            accept="image/jpeg,image/png,image/webp" 
          />
        </div>

        <!-- Hero Image Opacity Controller (Compact Edition) -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#0E1318]/90 p-3.5 space-y-2.5 shadow-lg backdrop-blur-sm">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/80">
              <AdjustmentsHorizontalIcon class="h-3.5 w-3.5 text-admin" />
              Hero Image Opacity
            </label>
            <div class="flex items-center gap-2">
              <!-- Quick Presets -->
              <div class="flex items-center gap-1">
                <button
                  v-for="preset in [10, 22, 35, 50, 75, 100]"
                  :key="preset"
                  type="button"
                  @click="settings.hero_image_opacity = preset"
                  :class="[
                    'px-1.5 py-0.5 rounded text-[10px] font-mono transition-all',
                    (settings.hero_image_opacity ?? 22) === preset
                      ? 'bg-admin text-obsidian font-bold shadow-sm'
                      : 'bg-white/[0.03] border border-white/[0.06] text-white/60 hover:text-white hover:bg-white/[0.08]'
                  ]"
                >
                  {{ preset }}%
                </button>
              </div>

              <!-- Number Input -->
              <div class="relative w-14 shrink-0">
                <input 
                  v-model.number="settings.hero_image_opacity"
                  type="number" 
                  min="0" 
                  max="100" 
                  aria-label="Hero background image opacity percentage"
                  class="w-full rounded-md border border-white/10 bg-black/40 px-1.5 py-0.5 text-center text-xs font-mono font-bold text-white focus:border-admin/60 focus:outline-none focus:ring-1 focus:ring-admin/30"
                />
                <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-[9px] text-white/40 pointer-events-none font-mono">%</span>
              </div>
            </div>
          </div>

          <!-- Slider -->
          <input 
            v-model.number="settings.hero_image_opacity"
            type="range" 
            min="0" 
            max="100" 
            step="1"
            aria-label="Hero background image opacity slider"
            class="w-full h-1.5 rounded-lg bg-white/10 appearance-none cursor-pointer accent-[#FF6700] hover:bg-white/20 transition-colors"
          />
        </div>

        <!-- Hero Overlay Styling Controller (Compact & Professional) -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#0E1318]/90 p-3.5 space-y-3 shadow-lg backdrop-blur-sm">
          <!-- Card Header -->
          <div class="flex items-center justify-between pb-2 border-b border-white/[0.06]">
            <div class="flex items-center gap-2">
              <div class="h-6 w-6 rounded-lg bg-admin/15 border border-admin/30 flex items-center justify-center text-admin">
                <SparklesIcon class="h-3.5 w-3.5" />
              </div>
              <span class="text-xs font-bold uppercase tracking-wider text-white/90">Hero Overlay Styling</span>
            </div>

            <div class="flex items-center gap-2">
              <!-- Compact Status Chip -->
              <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-mono font-bold bg-white/[0.04] text-white/90 border border-white/10 shadow-sm">
                <span class="h-2 w-2 rounded-full border border-white/40 shadow-sm" :style="{ backgroundColor: settings.hero_overlay_color || '#D4AF37' }"></span>
                <span>{{ settings.hero_overlay_color || '#D4AF37' }}</span>
                <span class="text-white/20">•</span>
                <span class="text-admin">{{ settings.hero_overlay_opacity ?? 45 }}%</span>
              </div>

              <!-- Edit Presets Toggle Button -->
              <button
                type="button"
                @click="showPresetsManager = !showPresetsManager"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold border transition-all"
                :class="showPresetsManager
                  ? 'bg-admin text-obsidian border-admin shadow-sm font-bold'
                  : 'bg-white/[0.04] text-admin border-admin/30 hover:bg-admin/10 hover:border-admin/50'"
                :title="showPresetsManager ? 'Close preset editor' : 'Manage preset colors'"
              >
                <PencilSquareIcon class="h-3 w-3" />
                <span>{{ showPresetsManager ? 'Done' : 'Edit Presets' }}</span>
              </button>
            </div>
          </div>

          <!-- Presets Manager Expanded Mode -->
          <OverlayPresetsManager
            v-if="showPresetsManager"
            v-model="currentPresets"
            :active-color="settings.hero_overlay_color"
            @select-color="settings.hero_overlay_color = $event"
            @close="showPresetsManager = false"
          />

          <!-- Compact Normal Mode -->
          <div v-else class="space-y-3">
            <!-- Unified Color Row: Swatches + Active Label + Custom Picker + Hex Input -->
            <div class="flex flex-wrap items-center justify-between gap-2.5">
              <!-- Left: Swatch Palette Circles -->
              <div class="flex items-center gap-1.5 flex-wrap">
                <button
                  v-for="(preset, idx) in currentPresets"
                  :key="idx"
                  type="button"
                  @click="settings.hero_overlay_color = preset.value"
                  :title="`${preset.label} (${preset.value})`"
                  class="group relative h-6 w-6 rounded-full border border-white/20 transition-all hover:scale-110 flex items-center justify-center shrink-0 cursor-pointer"
                  :class="[
                    (settings.hero_overlay_color || '#D4AF37').toLowerCase() === preset.value.toLowerCase()
                      ? 'ring-2 ring-admin ring-offset-2 ring-offset-[#0E1318] scale-105'
                      : 'hover:border-white/50'
                  ]"
                  :style="{ backgroundColor: preset.value }"
                >
                  <span class="sr-only">{{ preset.label }}</span>
                  <span 
                    v-if="(settings.hero_overlay_color || '#D4AF37').toLowerCase() === preset.value.toLowerCase()" 
                    class="h-1.5 w-1.5 rounded-full bg-white shadow-sm"
                  ></span>
                </button>

                <!-- Quick Add Preset Shortcut Dot -->
                <button
                  type="button"
                  @click="showPresetsManager = true"
                  title="Add or manage preset colors"
                  class="h-6 w-6 rounded-full border border-dashed border-white/20 hover:border-admin/60 text-white/40 hover:text-admin flex items-center justify-center transition-colors text-xs shrink-0 cursor-pointer"
                >
                  +
                </button>
              </div>

              <!-- Center/Right: Active Label & Custom Hex Picker -->
              <div class="flex items-center gap-2">
                <span class="text-[11px] font-medium text-white/60 truncate max-w-[120px]" :title="activePresetLabel">
                  {{ activePresetLabel }}
                </span>

                <div class="relative h-6 w-6 rounded-md overflow-hidden border border-white/20 cursor-pointer shadow-sm shrink-0">
                  <div 
                    class="w-full h-full"
                    :style="{ backgroundColor: settings.hero_overlay_color || '#D4AF37' }"
                  ></div>
                  <input
                    v-model="settings.hero_overlay_color"
                    type="color"
                    aria-label="Pick custom overlay color"
                    class="absolute -inset-2 w-10 h-10 cursor-pointer bg-transparent border-0 opacity-0"
                  />
                </div>

                <input
                  v-model="settings.hero_overlay_color"
                  type="text"
                  maxlength="7"
                  placeholder="#D4AF37"
                  aria-label="Hex color value"
                  class="w-20 rounded-md border border-white/10 bg-black/40 px-2 py-0.5 text-center text-xs font-mono font-bold text-white uppercase focus:border-admin/60 focus:outline-none focus:ring-1 focus:ring-admin/30"
                />

                <button
                  v-if="(settings.hero_overlay_color || '#D4AF37').toLowerCase() !== '#d4af37'"
                  type="button"
                  @click="settings.hero_overlay_color = '#D4AF37'"
                  title="Reset to signature gold"
                  class="text-[10px] text-admin hover:text-white font-mono px-1.5 py-0.5 rounded bg-admin/10 border border-admin/20 hover:bg-admin/20 transition-colors"
                >
                  ↺ Gold
                </button>
              </div>
            </div>

            <!-- Compact Opacity Controls -->
            <div class="space-y-1.5 pt-2 border-t border-white/[0.06]">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="text-[10px] uppercase tracking-wider font-semibold text-white/50">Overlay Opacity:</span>
                
                <div class="flex items-center gap-2">
                  <!-- Quick Preset Chips -->
                  <div class="flex items-center gap-1">
                    <button
                      v-for="preset in [0, 15, 30, 45, 60, 80, 100]"
                      :key="preset"
                      type="button"
                      @click="settings.hero_overlay_opacity = preset"
                      :class="[
                        'px-1.5 py-0.5 rounded text-[10px] font-mono transition-all',
                        (settings.hero_overlay_opacity ?? 45) === preset
                          ? 'bg-admin text-obsidian font-bold shadow-sm'
                          : 'bg-white/[0.03] border border-white/[0.06] text-white/60 hover:text-white hover:bg-white/[0.08]'
                      ]"
                    >
                      {{ preset }}%
                    </button>
                  </div>

                  <!-- Number Box -->
                  <div class="relative w-14 shrink-0">
                    <input 
                      v-model.number="settings.hero_overlay_opacity"
                      type="number" 
                      min="0" 
                      max="100" 
                      aria-label="Hero overlay opacity percentage"
                      class="w-full rounded-md border border-white/10 bg-black/40 px-1.5 py-0.5 text-center text-xs font-mono font-bold text-white focus:border-admin/60 focus:outline-none focus:ring-1 focus:ring-admin/30"
                    />
                    <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-[9px] text-white/40 pointer-events-none font-mono">%</span>
                  </div>
                </div>
              </div>

              <!-- Slider Track -->
              <input 
                v-model.number="settings.hero_overlay_opacity"
                type="range" 
                min="0" 
                max="100" 
                step="1"
                aria-label="Hero overlay opacity slider"
                class="w-full h-1.5 rounded-lg bg-white/10 appearance-none cursor-pointer accent-[#FF6700] hover:bg-white/20 transition-colors"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { buildOverlayGradient, parseOverlayPresets } from '@/core/utils/heroOverlay';
import OverlayPresetsManager from './OverlayPresetsManager.vue';
import { 
  WindowIcon,
  DocumentTextIcon, 
  ChatBubbleBottomCenterTextIcon, 
  PhotoIcon, 
  CloudArrowUpIcon,
  AdjustmentsHorizontalIcon,
  SparklesIcon,
  PencilSquareIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  settings: {
    type: Object,
    required: true
  },
  previewUrl: {
    type: String,
    default: null
  },
  currentHeroImage: {
    type: String,
    default: null
  },
  imageLoadError: {
    type: Boolean,
    default: false
  },
  getStorageUrl: {
    type: Function,
    required: true
  }
});

const emit = defineEmits(['image-upload', 'update:imageLoadError']);
const fileInput = ref(null);
const showPresetsManager = ref(false);

const splitHeadline = (title) => {
  const normalizedTitle = String(title || '').replace(/\s*\n\s*/g, ' ').trim();
  const sentenceBreak = normalizedTitle.match(/^(.+?[.!?])\s+(.+)$/);
  if (sentenceBreak) return [sentenceBreak[1], sentenceBreak[2]];

  const words = normalizedTitle.split(/\s+/);
  if (words.length < 2) return [normalizedTitle, ''];
  const midpoint = Math.ceil(words.length / 2);
  return [words.slice(0, midpoint).join(' '), words.slice(midpoint).join(' ')];
};

// Initialize dual-phrase if needed
if (!props.settings.cta_title_part_one && props.settings.hero_title) {
  const parts = splitHeadline(props.settings.hero_title);
  props.settings.cta_title_part_one = parts[0];
  props.settings.cta_title_part_two = parts[1];
}
if (!props.settings.cta_subtitle && props.settings.hero_subtitle) {
  props.settings.cta_subtitle = props.settings.hero_subtitle;
}
if (!props.settings.cta_primary_text && props.settings.hero_btn1_text) {
  props.settings.cta_primary_text = props.settings.hero_btn1_text;
}

const onPhraseInput = () => {
  const p1 = (props.settings.cta_title_part_one || '').trim();
  const p2 = (props.settings.cta_title_part_two || '').trim();
  if (p1 || p2) {
    const full = `${p1} ${p2}`.trim();
    props.settings.cta_title = full;
    props.settings.hero_title = full;
  }
};

const onSubtitleInput = (e) => {
  props.settings.hero_subtitle = e.target.value;
};

const onPrimaryBtnInput = (e) => {
  props.settings.hero_btn1_text = e.target.value;
};

const currentPresets = computed({
  get: () => parseOverlayPresets(props.settings?.hero_overlay_presets),
  set: (val) => {
    if (props.settings) {
      props.settings.hero_overlay_presets = JSON.stringify(val);
    }
  }
});

const activePreset = computed(() => {
  const current = (props.settings?.hero_overlay_color || '#D4AF37').toLowerCase();
  return currentPresets.value.find(p => p.value.toLowerCase() === current) || null;
});

const activePresetLabel = computed(() => {
  if (activePreset.value) return activePreset.value.label;
  return 'Custom Tone';
});

const computedPreviewOpacity = computed(() => {
  const raw = props.settings?.hero_image_opacity;
  if (raw === undefined || raw === null || raw === '') return 0.22;
  const num = Number(raw);
  if (isNaN(num)) return 0.22;
  const clamped = Math.max(0, Math.min(100, num));
  return clamped > 1 ? clamped / 100 : clamped;
});

const computedOverlayGradient = computed(() => buildOverlayGradient(props.settings?.hero_overlay_color));

const computedGoldOverlayOpacity = computed(() => {
  const raw = props.settings?.hero_overlay_opacity;
  if (raw === undefined || raw === null || raw === '') return 0.45;
  const num = Number(raw);
  if (isNaN(num)) return 0.45;
  const clamped = Math.max(0, Math.min(100, num));
  return clamped > 1 ? clamped / 100 : clamped;
});

const onChange = (event) => {
  emit('image-upload', event.target.files[0], fileInput.value);
};
</script>
