<template>
  <div class="rounded-2xl border border-theme-border bg-theme-surface shadow-xl overflow-hidden">
    <!-- Empty State -->
    <div v-if="records.length === 0" class="py-16 px-6 text-center">
      <p class="text-sm font-bold text-theme-text">No no-show records match this filter</p>
      <p class="text-xs text-theme-text-muted mt-1">
        Confirmed appointments marked manually or automatically as no-show will appear here.
      </p>
    </div>

    <!-- Mobile Cards -->
    <div v-else class="md:hidden divide-y divide-theme-border">
      <article v-for="rec in records" :key="rec.id" class="p-4 space-y-3">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <p class="text-sm font-bold text-theme-text truncate">
              {{ rec.customer?.name || rec.appointment?.client_name || 'Guest Client' }}
            </p>
            <p class="text-[11px] font-mono text-theme-text-muted">
              {{ rec.appointment?.booking_reference }} · {{ rec.appointment?.appointment_date }} {{ rec.appointment?.appointment_time }}
            </p>
          </div>
          <span
            class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border shrink-0"
            :class="rec.was_automatic ? 'bg-amber-500/15 text-amber-500 border-amber-500/30' : 'bg-purple-500/15 text-purple-400 border-purple-500/30'"
          >
            {{ rec.was_automatic ? 'Auto' : 'Manual' }}
          </span>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-[11px]">
          <span v-if="rec.deposit_forfeited" class="px-2 py-0.5 rounded-md bg-red-500/15 text-red-400 border border-red-500/30 font-semibold">
            Deposit Forfeited (₦{{ Number(rec.deposit_forfeited_amount || 0).toLocaleString() }})
          </span>
          <span v-else-if="rec.appointment?.payment_status === 'refunded'" class="px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-semibold">
            Refunded
          </span>
          <span v-else-if="rec.appointment?.payment_status === 'refund_pending'" class="px-2 py-0.5 rounded-md bg-blue-500/15 text-blue-400 border border-blue-500/30 font-semibold">
            Refund Pending
          </span>
          <span v-if="rec.appealed" class="px-2 py-0.5 rounded-md bg-sky-500/15 text-sky-400 border border-sky-500/30 font-semibold">
            Appeal: {{ rec.appeal_outcome }}
          </span>
        </div>

        <p v-if="rec.grace_extended" class="text-[11px] text-amber-400">
          Extended once: "{{ rec.grace_reason }}"
        </p>

        <div class="flex flex-wrap gap-2 pt-1">
          <button
            v-if="rec.can_forfeit_deposit"
            type="button"
            :disabled="processingId === rec.id"
            @click="$emit('forfeit', rec)"
            class="px-3 py-2 rounded-xl bg-red-500/15 border border-red-500/30 text-red-400 text-xs font-bold cursor-pointer"
          >
            Forfeit Deposit
          </button>
          <button
            v-if="rec.can_initiate_refund"
            type="button"
            :disabled="processingId === rec.id"
            @click="$emit('open-refund', rec)"
            class="px-3 py-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold cursor-pointer"
          >
            Start Refund
          </button>
          <button
            v-if="rec.appealed && rec.appeal_outcome === 'pending'"
            type="button"
            :disabled="processingId === rec.id"
            @click="$emit('open-appeal', rec)"
            class="px-3 py-2 rounded-xl bg-admin/15 border border-admin/30 text-admin text-xs font-bold cursor-pointer"
          >
            Review Appeal
          </button>
        </div>
      </article>
    </div>

    <!-- Desktop Table -->
    <div v-if="records.length > 0" class="hidden md:block overflow-x-auto">
      <table class="w-full text-left border-collapse md:min-w-[900px]">
        <thead>
          <tr class="border-b border-theme-border bg-theme-bg text-[10px] uppercase tracking-[0.18em] text-theme-text-muted font-bold">
            <th class="px-5 py-3.5">Client & Booking</th>
            <th class="px-5 py-3.5">Barber & Grace</th>
            <th class="px-5 py-3.5">Deposit & Payment</th>
            <th class="px-5 py-3.5">Appeal Status</th>
            <th class="px-5 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-theme-border text-xs">
          <tr v-for="rec in records" :key="rec.id" class="hover:bg-theme-surface-hover transition-colors">
            <td class="px-5 py-4 align-middle">
              <p class="font-bold text-theme-text">{{ rec.customer?.name || rec.appointment?.client_name || 'Guest' }}</p>
              <p class="font-mono text-[11px] text-theme-text-muted mt-0.5">
                {{ rec.appointment?.booking_reference }} · {{ rec.appointment?.appointment_date }} {{ rec.appointment?.appointment_time }}
              </p>
              <span
                class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase border"
                :class="rec.was_automatic ? 'bg-amber-500/15 text-amber-500 border-amber-500/30' : 'bg-purple-500/15 text-purple-400 border-purple-500/30'"
              >
                {{ rec.was_automatic ? 'Auto-Marked' : `By ${rec.marked_by_name || 'Staff'}` }}
              </span>
            </td>

            <td class="px-5 py-4 align-middle">
              <p class="font-semibold text-theme-text">{{ rec.appointment?.service_name || 'Service' }}</p>
              <p class="text-[11px] text-theme-text-muted">{{ rec.appointment?.barber_name || 'Barber' }}</p>
              <p v-if="rec.grace_extended" class="text-[11px] text-amber-400 mt-1 max-w-xs truncate" :title="rec.grace_reason">
                Extended: "{{ rec.grace_reason }}"
              </p>
            </td>

            <td class="px-5 py-4 align-middle">
              <p class="font-mono font-bold text-theme-text">
                ₦{{ Number(rec.paid_amount_naira || 0).toLocaleString() }}
              </p>
              <span v-if="rec.deposit_forfeited" class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/15 text-red-400 border border-red-500/30">
                Forfeited
              </span>
              <span v-else-if="rec.appointment?.payment_status === 'refunded'" class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                Refunded
              </span>
              <span v-else-if="rec.appointment?.payment_status === 'refund_pending'" class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/15 text-blue-400 border border-blue-500/30">
                Refund Initiated
              </span>
              <span v-else class="inline-block mt-1 text-[11px] text-theme-text-muted capitalize">
                {{ (rec.appointment?.payment_status || 'unpaid').replace('_', ' ') }}
              </span>
            </td>

            <td class="px-5 py-4 align-middle">
              <template v-if="rec.appealed">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border"
                  :class="{
                    'bg-amber-500/15 text-amber-400 border-amber-500/30': rec.appeal_outcome === 'pending',
                    'bg-emerald-500/15 text-emerald-400 border-emerald-500/30': rec.appeal_outcome === 'approved',
                    'bg-red-500/15 text-red-400 border-red-500/30': rec.appeal_outcome === 'rejected',
                  }"
                >
                  {{ rec.appeal_outcome }}
                </span>
                <p class="text-[11px] text-theme-text-muted mt-1 max-w-xs truncate" :title="rec.appeal_reason">
                  "{{ rec.appeal_reason }}"
                </p>
              </template>
              <span v-else class="text-theme-text-muted">No appeal</span>
            </td>

            <td class="px-5 py-4 align-middle text-right">
              <div class="inline-flex items-center justify-end gap-2">
                <button
                  v-if="rec.can_forfeit_deposit"
                  type="button"
                  :disabled="processingId === rec.id"
                  @click="$emit('forfeit', rec)"
                  class="px-3 py-1.5 rounded-xl bg-red-500/15 border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white text-[11px] font-bold transition-all cursor-pointer"
                >
                  Forfeit Deposit
                </button>
                <button
                  v-if="rec.can_initiate_refund"
                  type="button"
                  :disabled="processingId === rec.id"
                  @click="$emit('open-refund', rec)"
                  class="px-3 py-1.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white text-[11px] font-bold transition-all cursor-pointer"
                >
                  Start Refund
                </button>
                <button
                  v-if="rec.appealed && rec.appeal_outcome === 'pending'"
                  type="button"
                  :disabled="processingId === rec.id"
                  @click="$emit('open-appeal', rec)"
                  class="px-3 py-1.5 rounded-xl bg-admin/15 border border-admin/30 text-admin hover:bg-admin hover:text-obsidian text-[11px] font-bold transition-all cursor-pointer"
                >
                  Review Appeal
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  records: { type: Array, required: true },
  processingId: { type: [Number, String, null], default: null },
});

defineEmits(['forfeit', 'open-refund', 'open-appeal']);
</script>
