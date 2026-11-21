<template>
  <BarberLayout>
    <section class="max-w-7xl mx-auto pb-12 pt-4 px-4 sm:px-6 lg:px-8">
      <WalkInSkeleton v-if="loading" />

      <div v-else class="space-y-6">
        <WalkInHeader />

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-8 items-start">
          <!-- Left Column: Intake Forms -->
          <div class="space-y-6">
            <WalkInCustomerCard
              :form="form"
              @update:customer_name="form.customer_name = $event"
              @update:customer_phone="form.customer_phone = $event"
              @update:customer_email="form.customer_email = $event"
            />

            <WalkInServiceCard
              :form="form"
              :services-list="servicesList"
              @update:service_id="form.service_id = $event"
              @update:appointment_date="form.appointment_date = $event"
              @update:appointment_time="form.appointment_time = $event"
              @update:payment_method="form.payment_method = $event"
            />
          </div>

          <!-- Right Column: Live Voucher & Recent Walk-ins -->
          <div class="space-y-6 lg:sticky lg:top-8">
            <WalkInTicketSummary
              :form="form"
              :current-date-formatted="currentDateFormatted"
              :selected-service-name="selectedServiceName"
              :selected-service-price="selectedServicePrice"
              :is-form-valid="isFormValid"
              :submitting="submitting"
              @reset="resetForm"
              @submit="submitWalkIn"
            />

            <WalkInRecentList :recent-walk-ins="recentWalkIns" />
          </div>
        </div>
      </div>
    </section>
  </BarberLayout>
</template>

<script setup>
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import { useBarberWalkIn } from '../composables/useBarberWalkIn';
import WalkInHeader from '../components/walk-in/WalkInHeader.vue';
import WalkInCustomerCard from '../components/walk-in/WalkInCustomerCard.vue';
import WalkInServiceCard from '../components/walk-in/WalkInServiceCard.vue';
import WalkInTicketSummary from '../components/walk-in/WalkInTicketSummary.vue';
import WalkInRecentList from '../components/walk-in/WalkInRecentList.vue';
import WalkInSkeleton from '../components/walk-in/WalkInSkeleton.vue';

const {
  loading,
  submitting,
  form,
  servicesList,
  recentWalkIns,
  currentDateFormatted,
  selectedServiceName,
  selectedServicePrice,
  isFormValid,
  resetForm,
  submitWalkIn,
} = useBarberWalkIn();
</script>
