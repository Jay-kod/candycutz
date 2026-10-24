<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl space-y-8">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.15)]">
          <BuildingOffice2Icon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Business Channels & Inquiries Routing</h2>
          <p class="text-xs text-white/50 mt-0.5">Public communication channels, hidden message destination inbox, physical location, and hours.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 02
      </span>
    </div>

    <!-- 1. Public Contact Numbers & Emails -->
    <div class="space-y-4">
      <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-2">
        <PhoneIcon class="h-4 w-4" />
        Public Communication Channels
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Public Phone Number
          </label>
          <div class="relative">
            <input 
              v-model="content.contact_phone" 
              type="text" 
              placeholder="+234 800 000 0000" 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
            />
            <PhoneIcon class="h-4 w-4 text-white/30 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
          </div>
          <p class="text-[11px] text-white/40">Shown on the public contact card with direct 'tel:' click-to-call.</p>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Public Customer Support Email
          </label>
          <div class="relative">
            <input 
              v-model="content.contact_email" 
              type="email" 
              placeholder="info@candycutz.com" 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
            />
            <EnvelopeIcon class="h-4 w-4 text-white/30 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
          </div>
          <p class="text-[11px] text-white/40">Shown on the public contact card with direct 'mailto:' click-to-write.</p>
        </div>
      </div>
    </div>

    <!-- 2. Hidden Destination Email for Customer Submissions -->
    <div class="rounded-xl border border-admin/20 bg-admin/[0.04] p-5 space-y-3">
      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-admin">
          <InboxArrowDownIcon class="h-4 w-4" />
          Contact Form Destination Inbox (Confidential)
        </label>
        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-admin/10 text-admin border border-admin/20">
          Internal Routing
        </span>
      </div>
      <input 
        v-model="content.contact_receiver_email" 
        type="email" 
        placeholder="e.g. notifications@candycutz.com" 
        class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13] px-4 py-3 text-sm text-white placeholder-white/20 focus:border-admin focus:ring-2 focus:ring-admin/20 transition-all outline-none font-mono" 
      />
      <p class="text-[11px] text-white/50 leading-relaxed">
        Incoming messages sent by visitors through the public Contact page form will be routed to this private address. This email is never revealed publicly in client markup.
      </p>
    </div>

    <!-- 3. Address & Operating Hours -->
    <div class="space-y-4 pt-2 border-t border-white/[0.06]">
      <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-2">
        <MapPinIcon class="h-4 w-4" />
        Studio Location & Operating Hours
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Physical Business Address
          </label>
          <textarea 
            v-model="content.contact_address" 
            rows="3" 
            placeholder="123 Grooming Street&#10;Lagos, Nigeria" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none resize-none leading-relaxed font-sans"
          ></textarea>
          <p class="text-[11px] text-white/40">Enter line breaks to format street, district, and city.</p>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Salon Operating Hours
          </label>
          <textarea 
            v-model="content.contact_hours" 
            rows="3" 
            placeholder="Mon - Sat: 9:00 AM - 8:00 PM&#10;Sun: Closed" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none resize-none leading-relaxed font-sans"
          ></textarea>
          <p class="text-[11px] text-white/40">Displayed on the operational hours card on the contact page.</p>
        </div>
      </div>
    </div>

    <!-- 4. Contact Form Field Placeholders -->
    <div class="space-y-4 pt-2 border-t border-white/[0.06]">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-2">
            <ChatBubbleBottomCenterTextIcon class="h-4 w-4" />
            Public Form Field Placeholders
          </h3>
          <p class="text-[11px] text-white/40 mt-0.5">Customize placeholder cues shown to visitors typing into the public contact form.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="space-y-1.5">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/60">
            Name Placeholder
          </label>
          <input 
            v-model="content.contact_form_name_placeholder" 
            type="text" 
            placeholder="e.g. John Doe" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-xs text-white placeholder-white/20 focus:border-admin/60 focus:outline-none transition-all" 
          />
        </div>

        <div class="space-y-1.5">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/60">
            Email Placeholder
          </label>
          <input 
            v-model="content.contact_form_email_placeholder" 
            type="text" 
            placeholder="e.g. john@example.com" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-xs text-white placeholder-white/20 focus:border-admin/60 focus:outline-none transition-all" 
          />
        </div>

        <div class="space-y-1.5">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/60">
            Phone Placeholder
          </label>
          <input 
            v-model="content.contact_form_phone_placeholder" 
            type="text" 
            placeholder="e.g. +234..." 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-xs text-white placeholder-white/20 focus:border-admin/60 focus:outline-none transition-all" 
          />
        </div>

        <div class="space-y-1.5">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/60">
            Message Placeholder
          </label>
          <input 
            v-model="content.contact_form_message_placeholder" 
            type="text" 
            placeholder="e.g. How can we help?" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-xs text-white placeholder-white/20 focus:border-admin/60 focus:outline-none transition-all" 
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  BuildingOffice2Icon, 
  PhoneIcon, 
  EnvelopeIcon, 
  InboxArrowDownIcon, 
  MapPinIcon, 
  ChatBubbleBottomCenterTextIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  content: {
    type: Object,
    required: true
  }
});
</script>

