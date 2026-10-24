<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] shadow-2xl">
    <!-- Preview Toolbar -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.08] bg-[#12161C]/90 backdrop-blur-md">
      <div class="flex items-center gap-3">
        <span class="flex h-3 w-3 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-xs font-mono font-bold uppercase tracking-wider text-white/80">Interactive Contact Page Mockup Preview</span>
        <span class="hidden sm:inline-flex text-[10px] text-white/40 font-mono">(Live Simulation)</span>
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
          v-if="previewImage"
          class="absolute inset-0 bg-cover bg-center"
          :style="{ 
            backgroundImage: `url(${previewImage})`,
            opacity: (100 - heroOpacity) / 100
          }"
        ></div>
        <div class="relative z-10 space-y-3">
          <p class="text-xs uppercase tracking-[0.3em] text-amber-400 font-bold">Contact Studio</p>
          <h1 class="font-display text-3xl sm:text-5xl text-white font-bold tracking-tight">
            {{ content.contact_title || 'Talk to CandyCutz' }}
          </h1>
          <p class="text-xs sm:text-sm text-white/60 max-w-xl mx-auto leading-relaxed">
            {{ content.contact_subtitle || 'Use the form to ask a question, request availability, or start a booking conversation.' }}
          </p>
        </div>
      </div>

      <!-- 2. Two-Column Layout (Business Info & Socials vs. Contact Message Form) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-6xl mx-auto">
        <!-- Left: Business Info Cards & Social Connect -->
        <div class="space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Location -->
            <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 space-y-2">
              <span class="text-[10px] uppercase font-bold tracking-widest text-amber-400">Location</span>
              <p class="text-xs text-white/80 whitespace-pre-wrap leading-relaxed">
                {{ content.contact_address || '123 Grooming Street\nLagos, Nigeria' }}
              </p>
            </div>

            <!-- Hours -->
            <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 space-y-2">
              <span class="text-[10px] uppercase font-bold tracking-widest text-amber-400">Hours</span>
              <p class="text-xs text-white/80 whitespace-pre-wrap leading-relaxed">
                {{ content.contact_hours || 'Mon - Sat: 9:00 AM - 8:00 PM\nSun: Closed' }}
              </p>
            </div>

            <!-- Phone -->
            <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 space-y-2">
              <span class="text-[10px] uppercase font-bold tracking-widest text-amber-400">Phone</span>
              <p class="text-xs text-white/80 font-mono">
                {{ content.contact_phone || '+234 800 000 0000' }}
              </p>
            </div>

            <!-- Email -->
            <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 space-y-2">
              <span class="text-[10px] uppercase font-bold tracking-widest text-amber-400">Email</span>
              <p class="text-xs text-white/80 break-all">
                {{ content.contact_email || 'info@candycutz.com' }}
              </p>
            </div>
          </div>

          <!-- Social Constellation Preview -->
          <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Connect With Us</span>
              <span class="text-[10px] font-mono text-white/40">{{ activeContactPreviewCount }} Channels Active</span>
            </div>
            <div v-if="activeContactPreviewCount > 0" class="flex flex-wrap gap-2.5">
              <div
                v-for="platform in activeContactPreviewList"
                :key="platform.id"
                class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/[0.03] border border-white/[0.08] text-xs font-medium text-white/80"
              >
                <SocialIcon :name="platform.id" class="h-3.5 w-3.5 text-amber-400" />
                <span>{{ platform.name }}</span>
              </div>
            </div>
            <div v-else class="text-xs text-white/40 italic">
              (No social profiles toggled for public contact page)
            </div>
          </div>
        </div>

        <!-- Right: Simulated Contact Form with Configured Placeholders -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 sm:p-8 space-y-5 shadow-xl">
          <div class="space-y-1">
            <h3 class="font-display text-lg text-white font-bold">Send a Message</h3>
            <p class="text-xs text-white/50">Our team will respond to your inquiry promptly.</p>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-white/60 mb-1.5">Your Full Name</label>
              <input 
                type="text" 
                disabled 
                :placeholder="content.contact_form_name_placeholder || 'John Doe'" 
                class="w-full rounded-xl border border-white/[0.08] bg-black/40 px-4 py-2.5 text-xs text-white placeholder-white/30 cursor-not-allowed opacity-80" 
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-white/60 mb-1.5">Email Address</label>
                <input 
                  type="email" 
                  disabled 
                  :placeholder="content.contact_form_email_placeholder || 'john@example.com'" 
                  class="w-full rounded-xl border border-white/[0.08] bg-black/40 px-4 py-2.5 text-xs text-white placeholder-white/30 cursor-not-allowed opacity-80" 
                />
              </div>
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-white/60 mb-1.5">Phone Number</label>
                <input 
                  type="text" 
                  disabled 
                  :placeholder="content.contact_form_phone_placeholder || '+234...'" 
                  class="w-full rounded-xl border border-white/[0.08] bg-black/40 px-4 py-2.5 text-xs text-white placeholder-white/30 cursor-not-allowed opacity-80" 
                />
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-white/60 mb-1.5">Your Message</label>
              <textarea 
                rows="4" 
                disabled 
                :placeholder="content.contact_form_message_placeholder || 'How can we help?'" 
                class="w-full rounded-xl border border-white/[0.08] bg-black/40 px-4 py-2.5 text-xs text-white placeholder-white/30 cursor-not-allowed opacity-80 resize-none" 
              ></textarea>
            </div>

            <button 
              type="button" 
              disabled 
              class="w-full rounded-xl bg-gradient-to-r from-admin to-admin-light py-3 text-xs font-bold text-obsidian shadow-md opacity-80 cursor-not-allowed"
            >
              Send Message
            </button>
          </div>
        </div>
      </div>

      <!-- 3. Google Maps Simulation -->
      <div v-if="extractedMapSrc" class="max-w-6xl mx-auto space-y-3 pt-6 border-t border-white/[0.06]">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-sky-400">Salon Location Map</span>
          <a
            v-if="content.contact_map_link"
            :href="content.contact_map_link"
            target="_blank"
            rel="noopener noreferrer"
            class="text-xs text-sky-400 hover:underline flex items-center gap-1"
          >
            <span>Open in Google Maps →</span>
          </a>
        </div>
        <div class="h-80 rounded-2xl overflow-hidden border border-white/[0.08] shadow-2xl">
          <iframe 
            :src="extractedMapSrc" 
            width="100%" 
            height="100%" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy"
          ></iframe>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import SocialIcon from '@/components/SocialIcon.vue';

defineProps({
  content: {
    type: Object,
    required: true
  },
  previewImage: {
    type: String,
    default: ''
  },
  heroOpacity: {
    type: Number,
    default: 76
  },
  extractedMapSrc: {
    type: String,
    default: ''
  },
  activeContactPreviewList: {
    type: Array,
    default: () => []
  },
  activeContactPreviewCount: {
    type: Number,
    default: 0
  }
});

defineEmits(['exit']);
</script>
