<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] shadow-2xl">
    <!-- Preview Toolbar -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.08] bg-[#12161C]/90 backdrop-blur-md">
      <div class="flex items-center gap-3">
        <span class="flex h-3 w-3 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-xs font-mono font-bold uppercase tracking-wider text-white/80">Interactive Account Deletion Simulation</span>
        <span class="hidden sm:inline-flex text-[10px] text-white/40 font-mono">(Live Public Rendering)</span>
      </div>
      <button 
        @click="$emit('exit')" 
        class="text-xs font-bold px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/10 text-white/70 hover:text-white hover:bg-white/10 transition-all"
      >
        Exit Preview Mode
      </button>
    </div>

    <div class="p-4 sm:p-8 space-y-10">
      <!-- 1. Simulated Page Hero -->
      <div class="relative overflow-hidden rounded-3xl border border-white/10 p-8 sm:p-12 text-center bg-gradient-to-b from-[#181D24] to-[#0B0F13] shadow-inner">
        <div 
          v-if="previewHeroImage"
          class="absolute inset-0 bg-cover bg-center"
          :style="{ 
            backgroundImage: `url(${previewHeroImage})`,
            opacity: (100 - heroOpacity) / 100
          }"
        ></div>
        <div class="relative z-10 space-y-3">
          <p class="text-xs uppercase tracking-[0.3em] text-amber-400 font-bold">Account Management</p>
          <h1 class="font-display text-3xl sm:text-5xl text-white font-bold tracking-tight">
            {{ content.account_deletion_title || 'Account Deactivation & Erasure' }}
          </h1>
          <p class="text-xs sm:text-sm text-white/60 max-w-xl mx-auto leading-relaxed">
            {{ content.account_deletion_subtitle || 'Instructions for requesting deletion or deactivation of your CandyCutz account and associated personal data.' }}
          </p>
        </div>
      </div>

      <!-- 2. Public Layout Content Sections -->
      <div class="max-w-3xl mx-auto space-y-8">
        <!-- Step 1: In-App Deactivation -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 sm:p-8 space-y-4 shadow-xl">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/15 text-amber-400 font-bold text-base border border-amber-500/30">
              1
            </span>
            <h2 class="font-display text-xl font-bold text-amber-400">Instant In-App Deactivation</h2>
          </div>
          <p class="text-xs sm:text-sm text-white/70 leading-relaxed">
            You can immediately deactivate your account at any time using the CandyCutz mobile application:
          </p>
          <ol class="list-decimal list-inside space-y-1.5 text-xs sm:text-sm text-white/90 pl-2">
            <li>Open the <strong class="text-amber-400">CandyCutz</strong> app on your iOS or Android device.</li>
            <li>Navigate to your <strong class="text-amber-400">Profile</strong> tab in the bottom navigation.</li>
            <li>Tap on <strong class="text-amber-400">Security & Privacy</strong>.</li>
            <li>Tap <strong class="text-rose-400">Deactivate Account</strong> and confirm your password.</li>
          </ol>
          <p class="text-[11px] text-white/40 pt-2 border-t border-white/[0.04]">
            * This immediately revokes all active device sessions, terminates scheduled notifications, and removes your profile from discovery.
          </p>
        </div>

        <!-- Step 2: Web Deletion Request Form Mockup -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 sm:p-8 space-y-6 shadow-xl">
          <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/15 text-amber-400 font-bold text-base border border-amber-500/30">
              2
            </span>
            <h2 class="font-display text-xl font-bold text-amber-400">Submit Deletion Request Online</h2>
          </div>
          <p class="text-xs sm:text-sm text-white/70 leading-relaxed">
            If you do not have access to the mobile application, complete the form below to initiate an official data erasure request.
          </p>

          <div class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-white/60 mb-1.5">Registered Email Address</label>
              <input
                type="email"
                disabled
                :placeholder="content.account_deletion_email_placeholder || 'you@example.com'"
                class="w-full rounded-xl border border-white/[0.08] bg-black/40 px-4 py-2.5 text-xs text-white placeholder-white/30 cursor-not-allowed opacity-80"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-white/60 mb-1.5">Account Username or Full Name</label>
              <input
                type="text"
                disabled
                :placeholder="content.account_deletion_identifier_placeholder || '@username or Full Name'"
                class="w-full rounded-xl border border-white/[0.08] bg-black/40 px-4 py-2.5 text-xs text-white placeholder-white/30 cursor-not-allowed opacity-80"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-white/60 mb-1.5">Reason for Deactivation (Optional)</label>
              <textarea
                rows="3"
                disabled
                :placeholder="content.account_deletion_reason_placeholder || 'Help us understand how we can improve...'"
                class="w-full rounded-xl border border-white/[0.08] bg-black/40 px-4 py-2.5 text-xs text-white placeholder-white/30 cursor-not-allowed opacity-80 resize-none"
              ></textarea>
            </div>

            <button
              type="button"
              disabled
              class="w-full rounded-xl bg-gradient-to-r from-admin to-admin-light py-3 font-bold text-obsidian text-xs opacity-80 cursor-not-allowed"
            >
              Submit Account Deletion Request
            </button>
          </div>
        </div>

        <!-- Statutory Retention Notice -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#0B0F13] p-6 text-xs text-white/60 space-y-2">
          <h3 class="font-bold text-white text-xs uppercase tracking-wider">Statutory Data Retention Notice</h3>
          <p class="leading-relaxed whitespace-pre-wrap font-sans">
            {{ content.account_deletion_body || 'Per statutory financial reporting requirements and commercial audit standards, completed transaction receipts and tax records are retained for a minimum period. All marketing subscriptions, identity markers, personal device tokens, and contact preferences are wiped immediately upon processing.' }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  content: {
    type: Object,
    required: true
  },
  previewHeroImage: {
    type: String,
    default: ''
  },
  heroOpacity: {
    type: Number,
    default: 76
  }
});

defineEmits(['exit']);
</script>

