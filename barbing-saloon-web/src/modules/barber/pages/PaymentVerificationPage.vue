<template>
  <BarberLayout>
    <section class="max-w-7xl mx-auto pb-16 pt-4 px-4 sm:px-6 lg:px-8 space-y-6">
      <PaymentSkeleton v-if="loading" />

      <template v-else>
        <PaymentHeader
          :loading="loading"
          :search="search"
          @refresh="loadData"
          @open-account="openAccountModal"
          @update:search="search = $event"
        />

        <BarberPayoutCard
          :bank-details="bankDetails"
          :total-earnings="totalEarnings"
          :paid-count="paidCount"
          :awaiting-count="awaitingCount"
        />

        <PaymentList
          :payments-list="paymentsList"
          :current-tab="currentTab"
          :filter-tabs="filterTabs"
          @update:current-tab="currentTab = $event"
          @view-receipt="viewReceipt"
          @collect-payment="collectPaymentAtVenue"
        />
      </template>

      <!-- Decomposed Bank Details Modal (Read-Only View) -->
      <BarberBankDetailsModal
        :show="showAccountModal"
        :loading="loadingAccount"
        :form="accountForm"
        @close="showAccountModal = false"
      />
    </section>
  </BarberLayout>
</template>

<script setup>
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import { useBarberPayments } from '../composables/useBarberPayments';
import PaymentHeader from '../components/payments/PaymentHeader.vue';
import BarberPayoutCard from '../components/payments/BarberPayoutCard.vue';
import PaymentList from '../components/payments/PaymentList.vue';
import PaymentSkeleton from '../components/payments/PaymentSkeleton.vue';
import BarberBankDetailsModal from '../components/verification/BarberBankDetailsModal.vue';

const {
  loading,
  search,
  currentTab,
  filterTabs,
  paymentsList,
  bankDetails,
  totalEarnings,
  paidCount,
  awaitingCount,
  showAccountModal,
  loadingAccount,
  accountForm,
  loadData,
  viewReceipt,
  openAccountModal,
  collectPaymentAtVenue,
} = useBarberPayments();
</script>
