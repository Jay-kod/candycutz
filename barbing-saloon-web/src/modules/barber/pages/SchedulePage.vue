<template>
  <BarberLayout>
    <section class="max-w-7xl mx-auto pb-16 pt-4 px-4 sm:px-6 lg:px-8 space-y-6">
      <ScheduleSkeleton v-if="loading" />

      <template v-else>
        <ScheduleHeader
          :is-saving="isSaving"
          @save="saveSchedule"
        />

        <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr] items-start">
          <ScheduleHoursCard
            :working-hours="workingHours"
            :day-name="dayName"
          />

          <div class="lg:sticky lg:top-8">
            <ScheduleUpcomingCard
              :appointments="appointments"
              :get-month="getMonth"
              :get-day="getDay"
              :format-time-display="formatTimeDisplay"
            />
          </div>
        </div>
      </template>
    </section>
  </BarberLayout>
</template>

<script setup>
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import { useBarberSchedule } from '../composables/useBarberSchedule';
import ScheduleHeader from '../components/schedule/ScheduleHeader.vue';
import ScheduleHoursCard from '../components/schedule/ScheduleHoursCard.vue';
import ScheduleUpcomingCard from '../components/schedule/ScheduleUpcomingCard.vue';
import ScheduleSkeleton from '../components/schedule/ScheduleSkeleton.vue';

const {
  loading,
  isSaving,
  appointments,
  workingHours,
  dayName,
  getMonth,
  getDay,
  formatTimeDisplay,
  saveSchedule,
} = useBarberSchedule();
</script>
