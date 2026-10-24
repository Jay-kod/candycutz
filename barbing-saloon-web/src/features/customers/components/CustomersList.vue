<template>
  <CustomersTable
    :customers="filteredCustomers"
    :search="search"
    :current-filter="currentFilter"
    @view-profile="$emit('viewProfile', $event)"
    @reset-filters="$emit('resetFilters')"
  />
</template>

<script setup>
import CustomersTable from './CustomersTable.vue';

defineProps({
  filteredCustomers: { type: Array, required: true },
  currentFilter: { type: String, default: 'all' },
  search: { type: String, default: '' }
});

defineEmits(['viewProfile', 'resetFilters']);
</script>