<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-obsidian/85 backdrop-blur-md" @click="$emit('close')"></div>
      <div class="relative w-full max-w-xl rounded-3xl border border-gold/20 bg-theme-surface shadow-[0_25px_60px_rgba(0,0,0,0.5)] overflow-hidden animate-slide-up">
        
        <!-- Header with gradient -->
        <div class="relative bg-gradient-to-r from-charcoal via-obsidian to-charcoal px-8 py-5 overflow-hidden">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(212,175,55,0.08),transparent_70%)]"></div>
          <div class="absolute top-0 right-0 w-32 h-32 bg-gold/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
          <div class="relative z-10 flex justify-between items-center">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/10 border border-gold/20">
                <BanknotesIcon class="h-5 w-5 text-gold" />
              </div>
              <div>
                <h3 class="font-display text-lg text-theme-text">Bank Details</h3>
                <p class="text-[11px] text-ivory/40 mt-0.5">Where customers send payments</p>
              </div>
            </div>
            <button @click="$emit('close')" class="text-ivory/30 hover:text-white hover:bg-white/10 rounded-full p-2 transition-all">
              <XMarkIcon class="h-5 w-5"/>
            </button>
          </div>
        </div>

        <div class="p-8 relative">
          <div class="absolute -right-24 -bottom-24 h-48 w-48 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
          <div class="absolute -left-16 top-20 h-32 w-32 rounded-full bg-blue-500/5 blur-3xl pointer-events-none"></div>
          
          <div v-if="loading" class="py-16 flex justify-center">
            <div class="h-8 w-8 animate-spin rounded-full border-2 border-gold border-t-transparent shadow-[0_0_15px_rgba(212,175,55,0.3)]"></div>
          </div>
          
          <template v-else>
            <!-- Live Bank Card Preview -->
            <div class="mb-8 relative group">
              <div class="relative rounded-2xl overflow-hidden border border-gold/15 bg-gradient-to-br from-[#1a1a2e] via-[#16213e] to-[#0f3460] p-6 shadow-lg transition-transform duration-300 group-hover:scale-[1.01]">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(212,175,55,0.1),transparent_60%)]"></div>
                <div class="absolute top-4 right-5 opacity-20">
                  <svg class="w-12 h-12 text-gold" viewBox="0 0 24 24" fill="currentColor"><path d="M2 10V8a2 2 0 012-2h16a2 2 0 012 2v2H2zm0 2v4a2 2 0 002 2h16a2 2 0 002-2v-4H2zm4 2h4v2H6v-2z"/></svg>
                </div>
                <div class="relative z-10 space-y-4">
                  <p class="text-[10px] uppercase tracking-[0.2em] text-gold/60 font-medium">Payment Account</p>
                  <p class="text-xl font-mono text-white/90 tracking-[0.15em]">
                    {{ form.account_number || '••••••••••' }}
                  </p>
                  <div class="flex items-end justify-between pt-2">
                    <div>
                      <p class="text-[9px] uppercase tracking-wider text-ivory/30 mb-0.5">Account Holder</p>
                      <p class="text-sm font-semibold text-ivory/80">{{ form.account_name || 'Your Name' }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-[9px] uppercase tracking-wider text-ivory/30 mb-0.5">Bank</p>
                      <p class="text-sm font-semibold text-gold/80">{{ form.bank_name || 'Bank Name' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="$emit('save')" class="space-y-5 relative z-10">
              <!-- Bank Name -->
              <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-ivory/50 uppercase tracking-wider mb-2">
                  <BuildingLibraryIcon class="h-3.5 w-3.5 text-gold/60" />
                  Bank Name
                </label>
                <div class="relative">
                  <input 
                    v-model="form.bank_name" 
                    type="text" 
                    required
                    placeholder="e.g. GTBank, Access Bank"
                    class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] pl-4 pr-4 py-3.5 text-sm text-theme-text outline-none transition-all focus:border-gold/40 focus:bg-white/[0.05] focus:shadow-[0_0_0_3px_rgba(212,175,55,0.08)] placeholder:text-ivory/20"
                  />
                </div>
              </div>

              <!-- Account Name -->
              <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-ivory/50 uppercase tracking-wider mb-2">
                  <UserIcon class="h-3.5 w-3.5 text-gold/60" />
                  Account Holder Name
                </label>
                <div class="relative">
                  <input 
                    v-model="form.account_name" 
                    type="text" 
                    required
                    placeholder="e.g. James Jonathan"
                    class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] pl-4 pr-4 py-3.5 text-sm text-theme-text outline-none transition-all focus:border-gold/40 focus:bg-white/[0.05] focus:shadow-[0_0_0_3px_rgba(212,175,55,0.08)] placeholder:text-ivory/20"
                  />
                </div>
              </div>

              <!-- Account Number -->
              <div>
                <label class="flex items-center gap-1.5 text-xs font-semibold text-ivory/50 uppercase tracking-wider mb-2">
                  <HashtagIcon class="h-3.5 w-3.5 text-gold/60" />
                  Account Number
                </label>
                <div class="relative">
                  <input 
                    v-model="form.account_number" 
                    type="text" 
                    required
                    placeholder="e.g. 0123456789"
                    class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] pl-4 pr-4 py-3.5 text-sm text-theme-text outline-none transition-all focus:border-gold/40 focus:bg-white/[0.05] focus:shadow-[0_0_0_3px_rgba(212,175,55,0.08)] placeholder:text-ivory/20 font-mono tracking-wider"
                  />
                </div>
              </div>
              
              <!-- Actions -->
              <div class="flex items-center gap-3 pt-5 mt-2 border-t border-white/[0.06]">
                <button 
                  type="button" 
                  @click="$emit('close')"
                  class="flex-1 rounded-xl border border-white/10 bg-white/5 py-3 text-sm font-semibold text-ivory/70 hover:bg-white/10 hover:text-white transition-all"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="saving"
                  class="flex-[2] rounded-xl bg-gradient-to-r from-gold to-gold-light py-3 text-sm font-bold text-obsidian hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                >
                  <ArrowPathIcon v-if="saving" class="h-4 w-4 animate-spin" />
                  <CheckCircleIcon v-else class="h-4 w-4" />
                  {{ saving ? 'Saving...' : 'Save Details' }}
                </button>
              </div>
            </form>
          </template>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import {
  BanknotesIcon,
  XMarkIcon,
  BuildingLibraryIcon,
  UserIcon,
  HashtagIcon,
  ArrowPathIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  show: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  saving: { type: Boolean, default: false },
  form: { type: Object, required: true },
});

defineEmits(['close', 'save']);
</script>
