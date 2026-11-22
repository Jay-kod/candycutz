<template>
  <div class="space-y-6">
    <!-- Main Studio Split View Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- LEFT COLUMN: Settings, Template Selection & Customization Controls (5 cols) -->
      <div class="lg:col-span-5 space-y-6">
        <!-- Card 1: Template Switcher -->
        <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-6 shadow-2xl space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-white/60 flex items-center gap-2">
              <PaintBrushIcon class="h-4 w-4 text-[var(--color-accent)]" />
              <span>Select Template To Edit</span>
            </h2>
            <span class="text-[11px] text-slate-400 dark:text-white/40">5 Core Templates</span>
          </div>

          <div class="grid grid-cols-2 gap-2.5">
            <button
              v-for="tpl in templateList"
              :key="tpl.id"
              type="button"
              @click="activeTemplateId = tpl.id"
              :class="[
                'text-left p-3 rounded-2xl border transition-all text-xs font-medium cursor-pointer',
                activeTemplateId === tpl.id
                  ? 'bg-[var(--color-accent)]/15 border-[var(--color-accent)] text-slate-900 dark:text-white shadow-sm'
                  : 'bg-slate-50 dark:bg-white/[0.02] border-slate-200 dark:border-white/[0.08] text-slate-600 dark:text-slate-300 hover:border-slate-300 dark:hover:border-white/20',
                tpl.id === 'otp_verification' ? 'col-span-2' : ''
              ]"
            >
              <div class="font-bold flex items-center justify-between">
                <span>{{ tpl.name }}</span>
                <span v-if="activeTemplateId === tpl.id" class="text-[10px] text-[var(--color-accent)] font-bold">✓ Active</span>
              </div>
              <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">{{ tpl.desc }}</div>
            </button>
          </div>
        </div>

        <!-- Card 2: Email Structure & Copy Customizer -->
        <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-6 shadow-2xl space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-white/60 flex items-center gap-2">
              <SwatchIcon class="h-4 w-4 text-[var(--color-accent)]" />
              <span>Header &amp; Subject Customizer</span>
            </h2>
            <button
              type="button"
              @click="handleSave"
              :disabled="saving"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[var(--color-accent)]/15 text-[var(--color-accent)] hover:bg-[var(--color-accent)]/25 border border-[var(--color-accent)]/30 text-xs font-bold transition-all cursor-pointer disabled:opacity-50"
            >
              <ArrowPathIcon v-if="saving" class="h-3.5 w-3.5 animate-spin" />
              <CheckCircleIcon v-else class="h-3.5 w-3.5" />
              <span>Save Template Copy</span>
            </button>
          </div>

          <div class="space-y-3.5 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subject Line</label>
              <input
                v-model="activeTemplate.subject"
                type="text"
                @input="syncToForm"
                class="w-full bg-slate-50 dark:bg-[#0B0E14] border border-slate-200 dark:border-white/[0.1] rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white placeholder-slate-400 focus:border-[var(--color-accent)] outline-none font-medium text-xs transition-all"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Sender Name</label>
                <input
                  v-model="form.mail_from_name"
                  type="text"
                  placeholder="CandyCutz"
                  class="w-full bg-slate-50 dark:bg-[#0B0E14] border border-slate-200 dark:border-white/[0.1] rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:border-[var(--color-accent)] outline-none text-xs transition-all"
                />
              </div>
              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Sender Address</label>
                <input
                  v-model="form.mail_from"
                  type="email"
                  placeholder="concierge@candycutz.com"
                  class="w-full bg-slate-50 dark:bg-[#0B0E14] border border-slate-200 dark:border-white/[0.1] rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:border-[var(--color-accent)] outline-none text-xs transition-all"
                />
              </div>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Headline Greeting</label>
              <input
                v-model="activeTemplate.headline"
                type="text"
                class="w-full bg-slate-50 dark:bg-[#0B0E14] border border-slate-200 dark:border-white/[0.1] rounded-xl px-3.5 py-2.5 text-slate-900 dark:text-white focus:border-[var(--color-accent)] outline-none text-xs transition-all"
              />
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Custom Arrival / Instructions Note</label>
              <textarea
                v-model="activeTemplate.note"
                rows="2"
                @input="syncToForm"
                class="w-full bg-slate-50 dark:bg-[#0B0E14] border border-slate-200 dark:border-white/[0.1] rounded-xl px-3.5 py-2 text-slate-900 dark:text-white focus:border-[var(--color-accent)] outline-none text-xs transition-all"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Card 3: Layout & Feature Toggles (Context aware) -->
        <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-6 shadow-2xl space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-white/60 flex items-center gap-2">
            <SparklesIcon class="h-4 w-4 text-[var(--color-accent)]" />
            <span>{{ activeTemplate.type === 'otp' ? 'OTP Security & Delivery Specs' : 'Interactive Content Modules' }}</span>
          </h2>

          <!-- If OTP Template is selected -->
          <div v-if="activeTemplate.type === 'otp'" class="space-y-2.5 text-xs">
            <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-200/90 space-y-1">
              <div class="font-bold flex items-center gap-1.5 text-amber-300">
                <ClockIcon class="w-4 h-4 text-amber-400" />
                <span>Code Expiration: 15 Minutes (900s)</span>
              </div>
              <p class="text-[11px] text-slate-300">OTPs are purged from Redis memory automatically after 15 minutes.</p>
            </div>

            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-200/90 space-y-1">
              <div class="font-bold flex items-center gap-1.5 text-emerald-300">
                <ShieldCheckIcon class="w-4 h-4 text-emerald-400" />
                <span>Anti-Brute Force Throttling Active</span>
              </div>
              <p class="text-[11px] text-slate-300">Strict 60-second cooldown per notification; max 3 requests/hr per IP.</p>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.06] text-slate-300 space-y-1">
              <div class="font-bold flex items-center gap-1.5 text-white">
                <CheckBadgeIcon class="w-4 h-4 text-[var(--color-accent)]" />
                <span>Dual Verification Supported</span>
              </div>
              <p class="text-[11px] text-slate-400">Users can manually enter the 6 digits or click the direct signed link.</p>
            </div>
          </div>

          <!-- If Transactional Template is selected -->
          <div v-else class="space-y-2.5 text-xs">
            <label class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.06] cursor-pointer hover:bg-slate-100 dark:hover:bg-white/[0.04] transition-colors">
              <span class="text-slate-700 dark:text-slate-200 font-medium">Show Google Maps Saloon Directions</span>
              <input type="checkbox" v-model="showMaps" @change="syncToForm" class="w-4 h-4 accent-[var(--color-accent)] rounded" />
            </label>

            <label class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.06] cursor-pointer hover:bg-slate-100 dark:hover:bg-white/[0.04] transition-colors">
              <span class="text-slate-700 dark:text-slate-200 font-medium">Show "Add to Calendar (.ics)" Button</span>
              <input type="checkbox" v-model="showCalendar" @change="syncToForm" class="w-4 h-4 accent-[var(--color-accent)] rounded" />
            </label>

            <label class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.06] cursor-pointer hover:bg-slate-100 dark:hover:bg-white/[0.04] transition-colors">
              <span class="text-slate-700 dark:text-slate-200 font-medium">Show WhatsApp Concierge Assistance Link</span>
              <input type="checkbox" v-model="showWhatsApp" @change="syncToForm" class="w-4 h-4 accent-[var(--color-accent)] rounded" />
            </label>

            <label class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.06] cursor-pointer hover:bg-slate-100 dark:hover:bg-white/[0.04] transition-colors">
              <span class="text-slate-700 dark:text-slate-200 font-medium">Include Fast Check-In QR Snippet</span>
              <input type="checkbox" v-model="showQr" @change="syncToForm" class="w-4 h-4 accent-[var(--color-accent)] rounded" />
            </label>
          </div>
        </div>

        <!-- Card 4: Immediate Test Dispatcher -->
        <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161F] p-6 shadow-2xl space-y-3">
          <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-white/60 flex items-center gap-2">
            <PaperAirplaneIcon class="h-4 w-4 text-emerald-400" />
            <span>Send Real Test Email</span>
          </h2>
          <div class="flex gap-2">
            <input
              v-model="testEmail"
              type="email"
              placeholder="admin@candycutz.com"
              class="flex-1 bg-slate-50 dark:bg-[#0B0E14] border border-slate-200 dark:border-white/[0.1] rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:border-[var(--color-accent)] outline-none"
            />
            <button
              type="button"
              @click="handleDispatchTest"
              :disabled="testing || !testEmail || !testEmail.includes('@')"
              class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold text-xs hover:brightness-110 active:scale-95 transition-all disabled:opacity-40 flex items-center gap-1.5 shrink-0 cursor-pointer shadow-md shadow-emerald-500/20"
            >
              <ArrowPathIcon v-if="testing" class="h-3.5 w-3.5 animate-spin" />
              <PaperAirplaneIcon v-else class="h-3.5 w-3.5" />
              <span>{{ testing ? 'Sending...' : 'Send Test' }}</span>
            </button>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-white/40">
            Dispatches the currently previewed template with simulated booking data to verify live delivery in your inbox.
          </p>
        </div>
      </div>

      <!-- RIGHT COLUMN: Real-Time Interactive Live Email Preview & Architecture (7 cols) -->
      <div class="lg:col-span-7 space-y-4">
        <!-- Top Mode & Viewport Switcher Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-2xl bg-white dark:bg-[#12161F] border border-slate-200 dark:border-white/[0.08] shadow-lg">
          <!-- Mode Switcher -->
          <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-100 dark:bg-[#0B0E14] border border-slate-200 dark:border-white/[0.08]">
            <button
              type="button"
              @click="displayMode = 'preview'"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer',
                displayMode === 'preview'
                  ? 'bg-white dark:bg-white/10 text-slate-900 dark:text-white shadow-sm'
                  : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              ]"
            >
              <EnvelopeIcon class="w-3.5 h-3.5 text-[var(--color-accent)]" />
              <span>Live Customer View</span>
              <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-[var(--color-accent)]/20 text-[var(--color-accent)]">
                {{ activeTemplate.tag }}
              </span>
            </button>
            <button
              type="button"
              @click="displayMode = 'pipeline'"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer',
                displayMode === 'pipeline'
                  ? 'bg-white dark:bg-white/10 text-slate-900 dark:text-white shadow-sm'
                  : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              ]"
            >
              <QueueListIcon class="w-3.5 h-3.5 text-emerald-400" />
              <span>Pictorial Pipeline</span>
              <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-400">
                Architecture
              </span>
            </button>
          </div>

          <!-- Viewport Toggle (Only in Email Mockup mode) -->
          <div v-if="displayMode === 'preview'" class="flex items-center gap-1 bg-slate-100 dark:bg-[#0B0E14] p-1 rounded-xl border border-slate-200 dark:border-white/[0.08] self-end sm:self-auto">
            <button
              type="button"
              @click="viewMode = 'desktop'"
              :class="[
                'px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer',
                viewMode === 'desktop'
                  ? 'bg-white dark:bg-white/10 text-slate-900 dark:text-white shadow-sm'
                  : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              ]"
            >
              <ComputerDesktopIcon class="w-3.5 h-3.5" />
              <span>Desktop (600px)</span>
            </button>
            <button
              type="button"
              @click="viewMode = 'mobile'"
              :class="[
                'px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer',
                viewMode === 'mobile'
                  ? 'bg-white dark:bg-white/10 text-slate-900 dark:text-white shadow-sm'
                  : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              ]"
            >
              <DevicePhoneMobileIcon class="w-3.5 h-3.5" />
              <span>Mobile (380px)</span>
            </button>
          </div>
        </div>

        <!-- Render Pipeline Architecture Diagram if in pipeline mode -->
        <EmailPipelineDiagram v-if="displayMode === 'pipeline'" />

        <!-- Simulated Email Client Shell (Preview mode) -->
        <div
          v-else
          class="transition-all duration-300 mx-auto w-full"
          :class="viewMode === 'mobile' ? 'max-w-[380px]' : 'max-w-[620px]'"
        >
          <div class="rounded-3xl border border-white/[0.12] bg-[#0A0A0C] shadow-2xl overflow-hidden text-slate-100">
            <!-- Simulated Mail Client Window Chrome -->
            <div class="px-5 py-3.5 bg-[#14141A] border-b border-white/[0.08] flex items-center justify-between text-xs text-slate-400">
              <div class="flex items-center gap-2 truncate">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80 shrink-0"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80 shrink-0"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80 shrink-0"></span>
                <span class="ml-2 font-mono text-[11px] text-slate-400 truncate">
                  From: {{ form.mail_from_name || 'CandyCutz' }} &lt;{{ form.mail_from || 'concierge@candycutz.com' }}&gt;
                </span>
              </div>
              <span class="text-[11px] text-slate-500 font-mono shrink-0 ml-2">Today, 2:30 PM</span>
            </div>

            <!-- Subject Banner inside Client -->
            <div class="px-6 py-3 bg-[#111116] border-b border-white/[0.05] flex items-center justify-between gap-3">
              <div class="truncate">
                <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block">Subject</span>
                <div class="text-xs font-bold text-white tracking-wide truncate">
                  {{ activeTemplate.subject }}
                </div>
              </div>
              <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono shrink-0">
                DMARC Passed
              </span>
            </div>

            <!-- ACTUAL CANDYCUTZ LUXURY EMAIL BODY -->
            <div class="p-6 sm:p-8 space-y-6 text-slate-200 text-sm leading-relaxed" style="background: linear-gradient(180deg, #121218 0%, #0A0A0C 100%);">
              <!-- Email Brand Header -->
              <div class="text-center pb-5 border-b border-white/[0.08]">
                <div class="inline-block tracking-[0.25em] text-xl font-extrabold text-[#D4AF37] uppercase drop-shadow-[0_2px_10px_rgba(212,175,55,0.3)]">
                  C A N D Y C U T Z
                </div>
                <div class="text-[10px] tracking-[0.2em] uppercase text-slate-400 mt-1 font-medium">
                  Luxury Grooming &amp; Barbing Saloon
                </div>
              </div>

              <!-- Status Badge & Greeting -->
              <div class="space-y-3">
                <div
                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider border"
                  :class="activeTemplate.pillClass"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="activeTemplate.dotClass"></span>
                  <span>{{ activeTemplate.pill }}</span>
                </div>

                <h2 class="text-lg font-bold text-white tracking-tight">
                  Hello Alexander!
                  <span class="text-slate-300 font-normal block text-sm mt-0.5">
                    {{ activeTemplate.headline }}
                  </span>
                </h2>

                <p v-if="activeTemplate.note" class="text-xs text-slate-300 leading-relaxed bg-white/[0.02] p-3 rounded-xl border border-white/[0.05]">
                  {{ activeTemplate.note }}
                </p>
              </div>

              <!-- Luxury OTP Code & Verification Card (for OTP Template) -->
              <div
                v-if="activeTemplate.type === 'otp'"
                class="rounded-2xl border-l-4 bg-[#16161F] p-5 sm:p-6 shadow-xl border-y border-r border-white/[0.06] space-y-4"
                :class="activeTemplate.ticketBorderClass"
              >
                <div class="text-center space-y-1.5 pb-2 border-b border-white/[0.06]">
                  <span class="text-[10px] uppercase font-bold tracking-[0.2em] text-[#D4AF37]">
                    Official Verification Token
                  </span>
                  <div class="text-xs text-slate-300">
                    Enter this single-use passcode to verify your account or complete secure authentication.
                  </div>
                </div>

                <!-- 6-Digit OTP Big Display Box -->
                <div class="text-center py-4 px-2 bg-black/40 rounded-xl border border-white/[0.08] shadow-inner space-y-2.5">
                  <span class="text-[10px] uppercase font-bold tracking-widest text-slate-400">
                    6-Digit Verification Code
                  </span>
                  <div class="flex items-center justify-center gap-2 sm:gap-3">
                    <span
                      v-for="(digit, idx) in (activeTemplate.otpCode || '849201').split('')"
                      :key="idx"
                      class="w-9 h-12 sm:w-11 sm:h-14 rounded-xl bg-white/[0.05] border border-amber-500/40 text-amber-400 font-mono font-extrabold text-2xl sm:text-3xl flex items-center justify-center shadow-[0_0_15px_rgba(251,191,36,0.18)]"
                    >
                      {{ digit }}
                    </span>
                  </div>

                  <!-- Copy OTP Button -->
                  <div class="flex items-center justify-center pt-1.5 pb-0.5">
                    <button
                      type="button"
                      @click="copyOtpCode"
                      class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer border shadow-sm active:scale-95"
                      :class="copiedOtp
                        ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40 shadow-[0_0_12px_rgba(16,185,129,0.2)]'
                        : 'bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border-amber-500/35 hover:border-amber-400/50 shadow-[0_0_10px_rgba(251,191,36,0.1)]'"
                      title="Copy 6-digit code to clipboard"
                    >
                      <CheckIcon v-if="copiedOtp" class="w-4 h-4 text-emerald-400" />
                      <ClipboardDocumentIcon v-else class="w-4 h-4 text-amber-400" />
                      <span>{{ copiedOtp ? 'Copied 6-Digit Code!' : 'Copy Code' }}</span>
                    </button>
                  </div>
                  <div class="flex items-center justify-center gap-1.5 text-[11px] text-slate-400 pt-1">
                    <ClockIcon class="w-3.5 h-3.5 text-amber-400" />
                    <span>Expires in <strong>15 minutes</strong> &bull; Single use</span>
                  </div>
                </div>

                <!-- Instant 1-Click Verification Action -->
                <div class="text-center pt-2 space-y-2">
                  <p class="text-xs text-slate-300">
                    Alternatively, verify your account instantly with one click:
                  </p>
                  <a
                    href="javascript:void(0)"
                    class="block w-full py-3 px-4 rounded-xl text-center font-bold text-xs bg-gradient-to-r from-[#D4AF37] to-amber-500 text-slate-950 shadow-md hover:brightness-110 transition-all cursor-pointer"
                  >
                    Verify Email Address Instantly
                  </a>
                </div>

                <!-- Security Advisory Notice -->
                <div class="rounded-xl bg-amber-500/10 border border-amber-500/20 p-3 text-[11px] text-amber-200/90 leading-relaxed flex items-start gap-2.5">
                  <ShieldCheckIcon class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" />
                  <div>
                    <strong>Security Advisory:</strong> CandyCutz staff will never request this verification code via phone call, SMS, or DM. If you did not initiate this request, you can safely ignore this email.
                  </div>
                </div>
              </div>

              <!-- Luxury Appointment Card / Ticket (for Transactional Templates) -->
              <div
                v-else
                class="rounded-2xl border-l-4 bg-[#16161F] p-5 shadow-xl border-y border-r border-white/[0.06] space-y-3.5"
                :class="activeTemplate.ticketBorderClass"
              >
                <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
                  <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Booking Reference</span>
                    <div class="text-sm font-mono font-bold text-[#D4AF37]">{{ activeTemplate.ref }}</div>
                  </div>
                  <div class="text-right">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Status</span>
                    <div class="text-xs font-bold flex items-center justify-end gap-1" :class="activeTemplate.payColor">
                      <span class="w-1.5 h-1.5 rounded-full" :class="activeTemplate.payDotClass"></span>
                      <span>{{ activeTemplate.payStatus }}</span>
                    </div>
                  </div>
                </div>

                <!-- Ticket Details Rows -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                  <div>
                    <span class="text-slate-400 text-[11px] block">Service Chosen</span>
                    <span class="font-bold text-white">{{ activeTemplate.service }}</span>
                  </div>
                  <div>
                    <span class="text-slate-400 text-[11px] block">Master Barber</span>
                    <span class="font-bold text-white">{{ activeTemplate.barber }}</span>
                  </div>
                  <div>
                    <span class="text-slate-400 text-[11px] block">Date &amp; Time</span>
                    <span class="font-bold text-white">{{ activeTemplate.dateTime }}</span>
                  </div>
                  <div>
                    <span class="text-slate-400 text-[11px] block">Duration</span>
                    <span class="font-bold text-white">45 Minutes</span>
                  </div>
                </div>

                <!-- QR Check-in snippet -->
                <div v-if="showQr" class="pt-3 border-t border-white/[0.06] flex items-center justify-between">
                  <div class="text-[11px] text-slate-400">
                    <span class="text-white font-semibold">Fast Check-In:</span> Show this reference at reception.
                  </div>
                  <div class="px-2.5 py-1 bg-white text-slate-950 font-mono text-[10px] font-bold rounded tracking-widest border border-slate-300">
                    [QR CODE]
                  </div>
                </div>
              </div>

              <!-- Location & Map Guide (Transactional only) -->
              <div v-if="activeTemplate.type !== 'otp' && showMaps" class="rounded-xl bg-[#14141B] p-4 border border-white/[0.06] space-y-1.5 text-xs">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-[#D4AF37] flex items-center gap-1.5">
                    <MapPinIcon class="w-4 h-4 text-[#D4AF37]" />
                    Physical Saloon Location
                  </span>
                  <span class="text-[#D4AF37] hover:underline font-semibold text-[11px] cursor-pointer">Open Maps &rarr;</span>
                </div>
                <p class="text-slate-400 text-[11px] leading-relaxed">
                  Angwan Kare, BCG Road, Beside Angwan Kare BCG Gas Station, Keffi 961101, Nasarawa State, Nigeria. Parking available right in front of the saloon.
                </p>
              </div>

              <!-- Action Buttons (Transactional only) -->
              <div v-if="activeTemplate.type !== 'otp'" class="space-y-2.5 pt-2">
                <div class="flex flex-col sm:flex-row gap-2.5">
                  <span class="flex-1 py-3 px-4 rounded-xl text-center font-bold text-xs bg-gradient-to-r from-[#D4AF37] to-amber-500 text-slate-950 shadow-md cursor-pointer hover:brightness-110 transition-all">
                    Manage Booking in Portal
                  </span>
                  <span
                    v-if="showCalendar"
                    class="py-3 px-4 rounded-xl text-center font-bold text-xs bg-white/10 text-white hover:bg-white/15 border border-white/10 transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                  >
                    <CalendarDaysIcon class="w-3.5 h-3.5 text-[#D4AF37]" />
                    <span>Add to Calendar (.ics)</span>
                  </span>
                </div>

                <div v-if="showWhatsApp" class="text-center pt-1">
                  <span class="text-[11px] text-emerald-400 hover:underline inline-flex items-center gap-1 font-medium cursor-pointer">
                    <span>Need to reschedule or speak to your barber? Chat on WhatsApp</span>
                  </span>
                </div>
              </div>

              <!-- Footer -->
              <div class="pt-6 border-t border-white/[0.08] text-center space-y-1.5 text-[11px] text-slate-500">
                <p>&copy; 2026 CandyCutz Barbing Saloon Nigeria. All rights reserved.</p>
                <p>Angwan Kare, BCG, Keffi 961101, Nasarawa State, Nigeria</p>
                <div class="flex items-center justify-center gap-4 pt-1 text-slate-400">
                  <span class="hover:text-[#D4AF37] cursor-pointer">Privacy Policy</span>
                  <span>·</span>
                  <span class="hover:text-[#D4AF37] cursor-pointer">Cancellation Policy</span>
                  <span>·</span>
                  <span class="hover:text-[#D4AF37] cursor-pointer">Contact Support</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import EmailPipelineDiagram from './EmailPipelineDiagram.vue';
import {
  PaintBrushIcon,
  SwatchIcon,
  SparklesIcon,
  PaperAirplaneIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  CheckIcon,
  ClipboardDocumentIcon,
  ComputerDesktopIcon,
  DevicePhoneMobileIcon,
  MapPinIcon,
  CalendarDaysIcon,
  EnvelopeIcon,
  KeyIcon,
  ClockIcon,
  ShieldCheckIcon,
  QueueListIcon,
  CheckBadgeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  form: {
    type: Object,
    required: true
  },
  isConfigured: {
    type: Boolean,
    default: false
  },
  saving: {
    type: Boolean,
    default: false
  },
  testing: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['test-dispatch', 'save']);

const displayMode = ref('preview');
const viewMode = ref('desktop');
const activeTemplateId = ref('booking_confirmed');
const testEmail = ref('');

// Content Toggles
const showMaps = ref(true);
const showCalendar = ref(true);
const showWhatsApp = ref(true);
const showQr = ref(true);

const templateList = [
  { id: 'booking_confirmed', name: 'Confirmation', desc: 'Instant post-booking email' },
  { id: 'booking_reminder', name: '24h Reminder', desc: 'Sent day before service' },
  { id: 'payment_receipt', name: 'Payment Receipt', desc: 'Bank transfer / Paystack' },
  { id: 'cancellation', name: 'Cancellation', desc: 'Refund & reschedule note' },
  { id: 'otp_verification', name: 'OTP & Verification', desc: '6-digit code & secure login' }
];

const templates = reactive({
  booking_confirmed: {
    tag: 'BOOKING CONFIRMATION',
    subject: 'Booking Confirmed — CandyCutz Luxury Barbing',
    pill: 'Appointment Confirmed',
    pillClass: 'bg-[#D4AF37]/15 text-[#D4AF37] border-[#D4AF37]/30',
    dotClass: 'bg-[#D4AF37]',
    ticketBorderClass: 'border-l-[#D4AF37]',
    headline: 'Your VIP Chair is Secured',
    note: 'Please arrive 5 minutes early for complimentary espresso or drink and consultation with your master barber.',
    ref: '#CC-84920',
    payStatus: 'PAID (₦7,500)',
    payColor: 'text-emerald-400',
    payDotClass: 'bg-emerald-400',
    service: 'Royal Signature Haircut & Beard Sculpt',
    barber: 'Emmanuel (Master Stylist)',
    dateTime: 'Saturday, Nov 28 · 3:00 PM'
  },
  booking_reminder: {
    tag: '24-HOUR REMINDER',
    subject: 'Reminder: Your Haircut Appointment Tomorrow at CandyCutz',
    pill: 'Upcoming Tomorrow',
    pillClass: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
    dotClass: 'bg-amber-400',
    ticketBorderClass: 'border-l-amber-500',
    headline: 'Looking forward to seeing you tomorrow!',
    note: 'Your master barber is set for tomorrow at 3:00 PM. If you need to make changes, please reschedule at least 2 hours in advance.',
    ref: '#CC-84920',
    payStatus: 'RESERVED',
    payColor: 'text-amber-400',
    payDotClass: 'bg-amber-400',
    service: 'Royal Signature Haircut & Beard Sculpt',
    barber: 'Emmanuel (Master Stylist)',
    dateTime: 'Tomorrow · 3:00 PM'
  },
  payment_receipt: {
    tag: 'PAYMENT RECEIPT',
    subject: 'Payment Receipt: ₦7,500 for Appointment #CC-84920',
    pill: 'Payment Verified',
    pillClass: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
    dotClass: 'bg-emerald-400',
    ticketBorderClass: 'border-l-emerald-500',
    headline: 'Thank you for your payment',
    note: 'Your bank transfer / gateway receipt has been verified by the cashier. Here is your official service receipt.',
    ref: '#CC-84920',
    payStatus: 'SUCCESSFUL (₦7,500)',
    payColor: 'text-emerald-400',
    payDotClass: 'bg-emerald-400',
    service: 'Royal Signature Haircut & Beard Sculpt',
    barber: 'Emmanuel (Master Stylist)',
    dateTime: 'Saturday, Nov 28 · 3:00 PM'
  },
  cancellation: {
    tag: 'BOOKING CANCELLATION',
    subject: 'Appointment Cancelled — CandyCutz Barbing Saloon',
    pill: 'Appointment Cancelled',
    pillClass: 'bg-rose-500/15 text-rose-400 border-rose-500/30',
    dotClass: 'bg-rose-400',
    ticketBorderClass: 'border-l-rose-500',
    headline: 'Your Appointment has been Cancelled',
    note: 'As requested, your slot has been released. Any pre-paid fees will be credited back or credited to your store wallet.',
    ref: '#CC-84920',
    payStatus: 'CANCELLED',
    payColor: 'text-rose-400',
    payDotClass: 'bg-rose-400',
    service: 'Royal Signature Haircut & Beard Sculpt',
    barber: 'Emmanuel (Master Stylist)',
    dateTime: 'Saturday, Nov 28 · 3:00 PM'
  },
  otp_verification: {
    type: 'otp',
    tag: 'OTP & VERIFICATION',
    subject: 'CandyCutz — Verify Your Email Address (849201)',
    pill: 'Security Code',
    pillClass: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
    dotClass: 'bg-amber-400',
    ticketBorderClass: 'border-l-amber-500',
    headline: 'Verify Your Email Address',
    note: 'Please verify your email address to secure your CandyCutz account. Once verified, this email is permanently bound to your account, protected against unauthorized access, and will receive all official appointment confirmations and receipts.',
    otpCode: '849201',
    expiresIn: 'Valid for 15 minutes',
    verifyUrl: 'https://candycutz.com/verify-email?token=cc_demo_849201'
  }
});

const activeTemplate = computed(() => {
  return templates[activeTemplateId.value];
});

function syncToForm() {
  if (!props.form) return;
  props.form.mail_confirmed_subject = templates.booking_confirmed.subject;
  props.form.mail_reminder_subject = templates.booking_reminder.subject;
  props.form.mail_receipt_subject = templates.payment_receipt.subject;
  props.form.mail_cancellation_subject = templates.cancellation.subject;
  props.form.mail_otp_subject = templates.otp_verification.subject;
  props.form.mail_custom_note = templates.booking_confirmed.note;
  props.form.mail_show_maps = showMaps.value;
  props.form.mail_show_calendar = showCalendar.value;
  props.form.mail_show_whatsapp = showWhatsApp.value;
  props.form.mail_show_qr = showQr.value;
}

onMounted(() => {
  if (props.form) {
    if (props.form.mail_confirmed_subject) {
      templates.booking_confirmed.subject = props.form.mail_confirmed_subject;
    }
    if (props.form.mail_reminder_subject) {
      templates.booking_reminder.subject = props.form.mail_reminder_subject;
    }
    if (props.form.mail_receipt_subject) {
      templates.payment_receipt.subject = props.form.mail_receipt_subject;
    }
    if (props.form.mail_cancellation_subject) {
      templates.cancellation.subject = props.form.mail_cancellation_subject;
    }
    if (props.form.mail_otp_subject) {
      templates.otp_verification.subject = props.form.mail_otp_subject;
    }
    if (props.form.mail_custom_note) {
      templates.booking_confirmed.note = props.form.mail_custom_note;
    }
    if (props.form.mail_show_maps !== undefined) {
      showMaps.value = Boolean(props.form.mail_show_maps);
    }
    if (props.form.mail_show_calendar !== undefined) {
      showCalendar.value = Boolean(props.form.mail_show_calendar);
    }
    if (props.form.mail_show_whatsapp !== undefined) {
      showWhatsApp.value = Boolean(props.form.mail_show_whatsapp);
    }
    if (props.form.mail_show_qr !== undefined) {
      showQr.value = Boolean(props.form.mail_show_qr);
    }
    syncToForm();
  }
});

function handleSave() {
  syncToForm();
  emit('save');
}

function handleDispatchTest() {
  if (!testEmail.value) return;
  emit('test-dispatch', testEmail.value);
}

const copiedOtp = ref(false);
let copyTimeout = null;

async function copyOtpCode() {
  const code = activeTemplate.value?.otpCode || '849201';
  try {
    if (typeof navigator !== 'undefined' && navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
      await navigator.clipboard.writeText(code);
    } else if (typeof document !== 'undefined' && typeof document.execCommand === 'function') {
      const textArea = document.createElement('textarea');
      textArea.value = code;
      textArea.style.position = 'fixed';
      textArea.style.opacity = '0';
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
    }
  } catch (err) {
    // Gracefully handle clipboard write restrictions
  }
  copiedOtp.value = true;
  if (copyTimeout) clearTimeout(copyTimeout);
  copyTimeout = setTimeout(() => {
    copiedOtp.value = false;
  }, 2200);
}

onUnmounted(() => {
  if (copyTimeout) clearTimeout(copyTimeout);
});
</script>
