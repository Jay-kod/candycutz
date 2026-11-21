<template>
  <div class="overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/90 backdrop-blur-xl shadow-xl">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-theme-border bg-theme-bg/60 text-[10px] font-bold uppercase tracking-wider text-theme-text-muted">
            <th class="py-3 px-4">Event Type</th>
            <th class="py-3 px-4">Audit Reference ID</th>
            <th class="py-3 px-4 text-center">Timeframe</th>
            <th class="py-3 px-4">Authorized By</th>
            <th class="py-3 px-4">Date & Time</th>
            <th class="py-3 px-4 text-center">Integrity</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-theme-border/50 font-mono">
          <tr
            v-for="record in records"
            :key="record.id || record.report_id"
            class="hover:bg-theme-bg/40 transition-colors"
          >
            <!-- Event Type -->
            <td class="py-3 px-4">
              <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg" :class="getActionBadgeClass(record.action)">
                  <component :is="getActionIcon(record.action)" class="h-4 w-4" />
                </span>
                <div class="font-sans">
                  <p class="font-bold text-theme-text text-xs leading-none">{{ record.action_label || formatAction(record.action) }}</p>
                  <p class="text-[10px] text-theme-text-muted mt-0.5">{{ record.title || 'Report' }}</p>
                </div>
              </div>
            </td>

            <!-- Reference ID -->
            <td class="py-3 px-4">
              <div class="flex items-center gap-1.5">
                <span class="font-bold text-theme-text select-all">{{ record.report_id }}</span>
                <button
                  type="button"
                  @click="copyText(record.report_id, 'Reference ID')"
                  class="text-theme-text-muted hover:text-[#FF6700] transition-colors cursor-pointer"
                  title="Copy Reference ID"
                >
                  <DocumentDuplicateIcon class="h-3.5 w-3.5" />
                </button>
              </div>
            </td>

            <!-- Timeframe -->
            <td class="py-3 px-4 text-center">
              <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase border border-theme-border bg-theme-bg text-theme-text">
                {{ record.range_label || record.range || 'Custom' }}
              </span>
            </td>

            <!-- Authorized By -->
            <td class="py-3 px-4 font-sans">
              <div class="flex items-center gap-1.5">
                <span class="font-medium text-theme-text text-xs">{{ record.user_name || 'Admin' }}</span>
                <span class="text-[9px] uppercase font-bold px-1.5 py-0.2 rounded bg-sky-500/10 text-sky-400 border border-sky-500/20">
                  {{ record.user_role || 'Staff' }}
                </span>
              </div>
              <p class="text-[10px] text-theme-text-muted font-mono mt-0.5">{{ record.ip_address || '127.0.0.1' }}</p>
            </td>

            <!-- Date & Time -->
            <td class="py-3 px-4">
              <p class="text-theme-text font-bold text-xs">{{ record.formatted_date || formatDate(record.created_at) }}</p>
              <p class="text-[10px] text-theme-text-muted font-sans">{{ getRelativeTime(record.created_at) }}</p>
            </td>

            <!-- Integrity -->
            <td class="py-3 px-4 text-center">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <CheckCircleIcon class="h-3 w-3" />
                <span>VERIFIED</span>
              </span>
            </td>

            <!-- Action: Verify Record -->
            <td class="py-3 px-4 text-right">
              <button
                type="button"
                @click="$emit('verify', record)"
                class="inline-flex items-center gap-1 rounded-lg border border-theme-border bg-theme-bg hover:border-emerald-500/40 hover:bg-emerald-500/10 hover:text-emerald-400 px-2.5 py-1 text-xs font-semibold text-theme-text transition-all cursor-pointer"
                title="Verify authenticity and audit parameters"
              >
                <ShieldCheckIcon class="h-3.5 w-3.5 text-emerald-400" />
                <span>Verify</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="records.length === 0" class="py-12 px-4 text-center space-y-3 font-sans">
        <div class="h-12 w-12 rounded-2xl bg-theme-bg border border-theme-border text-theme-text-muted flex items-center justify-center mx-auto">
          <ClipboardDocumentListIcon class="h-6 w-6" />
        </div>
        <div>
          <h4 class="text-sm font-bold text-theme-text">No Report Export Records Found</h4>
          <p class="text-xs text-theme-text-muted mt-1 max-w-sm mx-auto">
            {{ isFiltered ? 'No logs match your search filter criteria.' : 'Download a PDF, export a CSV, or print an operational dossier to start populating this audit history.' }}
          </p>
        </div>
        <button
          v-if="!isFiltered"
          type="button"
          @click="$emit('switch-to-dossier')"
          class="mt-2 inline-flex items-center gap-1.5 rounded-xl bg-[#FF6700] hover:bg-[#FF6700]/90 text-white px-4 py-2 text-xs font-bold transition-all cursor-pointer"
        >
          <DocumentArrowDownIcon class="h-4 w-4" />
          <span>Generate First Report</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  DocumentDuplicateIcon,
  CheckCircleIcon,
  ShieldCheckIcon,
  ArrowDownTrayIcon,
  TableCellsIcon,
  PrinterIcon,
  ClipboardDocumentCheckIcon,
  ClipboardDocumentListIcon,
  DocumentArrowDownIcon
} from '@heroicons/vue/24/outline';
import { useToast } from '@/core/composables/useToast';

defineProps({
  records: { type: Array, default: () => [] },
  isFiltered: { type: Boolean, default: false }
});

defineEmits(['verify', 'switch-to-dossier']);

const toast = useToast();

const getActionIcon = (action) => {
  if (action === 'report_pdf_download' || String(action).includes('pdf')) return ArrowDownTrayIcon;
  if (action === 'report_csv_export' || String(action).includes('csv')) return TableCellsIcon;
  if (action === 'report_print' || String(action).includes('print')) return PrinterIcon;
  return ClipboardDocumentCheckIcon;
};

const getActionBadgeClass = (action) => {
  if (action === 'report_pdf_download' || String(action).includes('pdf')) return 'bg-orange-500/15 text-[#FF6700] border border-orange-500/30';
  if (action === 'report_csv_export' || String(action).includes('csv')) return 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30';
  if (action === 'report_print' || String(action).includes('print')) return 'bg-sky-500/15 text-sky-400 border border-sky-500/30';
  return 'bg-amber-500/15 text-amber-400 border border-amber-500/30';
};

const formatAction = (a) => String(a || 'Event').replace(/report_|_/g, ' ').toUpperCase();

const formatDate = (iso) => {
  if (!iso) return 'Just now';
  try {
    return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  } catch {
    return iso;
  }
};

const getRelativeTime = (iso) => {
  if (!iso) return 'Recent';
  try {
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return `${Math.floor(diff / 86400)}d ago`;
  } catch {
    return '';
  }
};

const copyText = async (text, label) => {
  try {
    await navigator.clipboard.writeText(text);
    toast.success(`${label} copied to clipboard`);
  } catch {
    toast.info(`${label}: ${text}`);
  }
};
</script>

