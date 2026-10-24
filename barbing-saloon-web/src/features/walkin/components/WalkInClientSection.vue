<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#0B0F13]/90 p-6 sm:p-7 relative overflow-hidden group hover:border-admin/40 transition-all">
    <!-- Technical Corner Ticks -->
    <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

    <!-- Section Title -->
    <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-white/[0.04]">
      <div class="h-10 w-10 rounded-xl bg-admin/10 border border-admin/20 flex items-center justify-center text-admin">
        <UserIcon class="h-5 w-5" />
      </div>
      <div>
        <h2 class="text-base font-bold text-white tracking-wide">Client Profile & Intake</h2>
        <p class="text-xs text-ivory/50">Primary contact and intake scheduling</p>
      </div>
    </div>

    <!-- Inputs -->
    <div class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Customer Name -->
        <div>
          <label class="block text-[11px] uppercase tracking-wider text-ivory/60 font-semibold mb-1.5">
            Full Name <span class="text-admin">*</span>
          </label>
          <div class="relative">
            <input
              :value="modelValue.customer_name"
              @input="updateField('customer_name', $event.target.value)"
              type="text"
              placeholder="e.g. Tunde Balogun"
              class="w-full rounded-xl border border-white/[0.1] bg-white/[0.02] pl-10 pr-4 py-3 text-sm text-white placeholder-ivory/30 outline-none transition-all focus:border-admin/60 focus:bg-white/[0.04] focus:ring-1 focus:ring-admin/30"
              required
            />
            <UserCircleIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-ivory/40 pointer-events-none" />
          </div>
        </div>

        <!-- Phone Number -->
        <div>
          <label class="block text-[11px] uppercase tracking-wider text-ivory/60 font-semibold mb-1.5">
            Phone Number <span class="text-admin">*</span>
          </label>
          <div class="relative">
            <input
              :value="modelValue.customer_phone"
              @input="updateField('customer_phone', $event.target.value)"
              type="tel"
              placeholder="e.g. 0803 123 4567"
              class="w-full rounded-xl border border-white/[0.1] bg-white/[0.02] pl-10 pr-4 py-3 text-sm text-white placeholder-ivory/30 outline-none transition-all focus:border-admin/60 focus:bg-white/[0.04] focus:ring-1 focus:ring-admin/30 font-mono"
              required
            />
            <PhoneIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-ivory/40 pointer-events-none" />
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Optional Email -->
        <div>
          <label class="block text-[11px] uppercase tracking-wider text-ivory/60 font-semibold mb-1.5">
            Email <span class="text-ivory/30 text-[10px] lowercase">(optional for digital receipt)</span>
          </label>
          <div class="relative">
            <input
              :value="modelValue.customer_email"
              @input="updateField('customer_email', $event.target.value)"
              type="email"
              placeholder="client@example.com"
              class="w-full rounded-xl border border-white/[0.1] bg-white/[0.02] pl-10 pr-4 py-3 text-sm text-white placeholder-ivory/30 outline-none transition-all focus:border-admin/60 focus:bg-white/[0.04] focus:ring-1 focus:ring-admin/30"
            />
            <EnvelopeIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-ivory/40 pointer-events-none" />
          </div>
        </div>

        <!-- Appointment Time with 'Right Now' Snap Button -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="text-[11px] uppercase tracking-wider text-ivory/60 font-semibold">
              Time Slot <span class="text-admin">*</span>
            </label>
            <button
              type="button"
              @click="setNow"
              class="text-[10px] font-mono text-admin hover:text-admin-light transition-colors underline uppercase tracking-wider"
            >
              Snap to Right Now
            </button>
          </div>
          <div class="relative">
            <input
              :value="modelValue.appointment_time"
              @input="updateField('appointment_time', $event.target.value)"
              type="time"
              class="w-full rounded-xl border border-white/[0.1] bg-white/[0.02] pl-10 pr-4 py-3 text-sm text-white outline-none transition-all focus:border-admin/60 focus:bg-white/[0.04] focus:ring-1 focus:ring-admin/30 font-mono [color-scheme:dark]"
              required
            />
            <ClockIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-ivory/40 pointer-events-none" />
          </div>
        </div>
      </div>

      <!-- Payment Method Pills -->
      <div class="pt-2">
        <label class="block text-[11px] uppercase tracking-wider text-ivory/60 font-semibold mb-2">
          Payment Method <span class="text-admin">*</span>
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
          <button
            v-for="pm in paymentMethods"
            :key="pm.id"
            type="button"
            @click="updateField('payment_method', pm.id)"
            class="flex items-center justify-center gap-2 p-2.5 rounded-xl border text-xs font-semibold transition-all"
            :class="modelValue.payment_method === pm.id 
              ? 'border-admin bg-admin/15 text-white shadow-sm ring-1 ring-admin/40' 
              : 'border-white/[0.08] bg-white/[0.02] text-ivory/60 hover:border-white/20 hover:text-white'"
          >
            <component :is="pm.icon" class="h-4 w-4" :class="modelValue.payment_method === pm.id ? 'text-admin' : 'text-ivory/40'" />
            <span>{{ pm.label }}</span>
          </button>
        </div>
      </div>

      <!-- Optional Barber / Styling Notes -->
      <div class="pt-1">
        <label class="block text-[11px] uppercase tracking-wider text-ivory/60 font-semibold mb-1.5">
          Styling Notes & Preferences <span class="text-ivory/30 text-[10px] lowercase">(optional)</span>
        </label>
        <div class="relative">
          <input
            :value="modelValue.notes"
            @input="updateField('notes', $event.target.value)"
            type="text"
            placeholder="e.g. Skin taper with hot towel finish, allergic to alcohol aftershave"
            class="w-full rounded-xl border border-white/[0.1] bg-white/[0.02] pl-10 pr-4 py-3 text-xs text-white placeholder-ivory/30 outline-none transition-all focus:border-admin/60 focus:bg-white/[0.04]"
          />
          <ChatBubbleBottomCenterTextIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-ivory/40 pointer-events-none" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  UserIcon, UserCircleIcon, PhoneIcon, EnvelopeIcon, ClockIcon, 
  BanknotesIcon, CreditCardIcon, DevicePhoneMobileIcon, CheckBadgeIcon,
  ChatBubbleBottomCenterTextIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: { type: Object, required: true }
});

const emit = defineEmits(['update:modelValue']);

const paymentMethods = [
  { id: 'cash', label: 'Cash', icon: BanknotesIcon },
  { id: 'pos', label: 'POS Terminal', icon: CreditCardIcon },
  { id: 'transfer', label: 'Bank Transfer', icon: DevicePhoneMobileIcon },
  { id: 'pending', label: 'Pay at Exit', icon: CheckBadgeIcon }
];

const updateField = (field, val) => {
  emit('update:modelValue', { ...props.modelValue, [field]: val });
};

const setNow = () => {
  const now = new Date();
  const timeStr = now.toTimeString().slice(0, 5);
  const dateStr = now.toISOString().split('T')[0];
  emit('update:modelValue', {
    ...props.modelValue,
    appointment_date: dateStr,
    appointment_time: timeStr
  });
};
</script>

