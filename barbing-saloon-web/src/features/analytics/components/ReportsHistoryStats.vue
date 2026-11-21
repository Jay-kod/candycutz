<template>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="rounded-xl border border-theme-border bg-theme-surface/90 p-3.5 backdrop-blur-xl">
      <p class="text-[10px] uppercase font-bold text-theme-text-muted">Total Exports Logged</p>
      <p class="mt-1 text-xl font-black text-theme-text font-mono">{{ totalCount }}</p>
      <p class="text-[10px] text-theme-text-muted">Reconciled audit events</p>
    </div>

    <div class="rounded-xl border border-theme-border bg-theme-surface/90 p-3.5 backdrop-blur-xl">
      <p class="text-[10px] uppercase font-bold text-orange-400">PDF Dossiers</p>
      <p class="mt-1 text-xl font-black text-orange-400 font-mono">{{ pdfCount }}</p>
      <p class="text-[10px] text-theme-text-muted">Downloaded documents</p>
    </div>

    <div class="rounded-xl border border-theme-border bg-theme-surface/90 p-3.5 backdrop-blur-xl">
      <p class="text-[10px] uppercase font-bold text-emerald-400">CSV Spreadsheets</p>
      <p class="mt-1 text-xl font-black text-emerald-400 font-mono">{{ csvCount }}</p>
      <p class="text-[10px] text-theme-text-muted">Tabular data exports</p>
    </div>

    <div class="rounded-xl border border-theme-border bg-theme-surface/90 p-3.5 backdrop-blur-xl">
      <p class="text-[10px] uppercase font-bold text-sky-400">Latest Event</p>
      <p class="mt-1 text-sm font-bold text-theme-text truncate font-mono">{{ latestEventTime }}</p>
      <p class="text-[10px] text-theme-text-muted truncate">{{ latestEventUser }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  totalCount: { type: Number, default: 0 },
  pdfCount: { type: Number, default: 0 },
  csvCount: { type: Number, default: 0 },
  latestEventTime: { type: String, default: 'No exports yet' },
  latestEventUser: { type: String, default: 'System ready' }
});
</script>

