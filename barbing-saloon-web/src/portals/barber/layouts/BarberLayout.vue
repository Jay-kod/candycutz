<template>
  <DashboardLayout
    portalName="Barber"
    homeRoute="/barber/dashboard"
    profileRoute="/barber/profile"
    :navItems="navItems"
    theme="barber"
  >
    <slot />
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '../../../core/layouts/DashboardLayout.vue';
import {
  HomeIcon,
  CalendarIcon,
  ClipboardDocumentCheckIcon,
  ScissorsIcon,
  PhotoIcon,
  PencilSquareIcon,
  BellAlertIcon,
  BanknotesIcon,
  BuildingLibraryIcon,
  ChartBarIcon,
  PresentationChartLineIcon,
  UserPlusIcon,
  StarIcon
} from '@heroicons/vue/24/outline';

const navItems = [
  { name: 'Dashboard', to: '/barber/dashboard', icon: HomeIcon },
  { name: 'Walk-In', to: '/barber/walk-in', icon: UserPlusIcon },
  { name: 'Payments', to: '/barber/payments', icon: BanknotesIcon },
  { name: 'Bank Details', to: '/barber/bank-details', icon: BuildingLibraryIcon },
  { name: 'Schedule', to: '/barber/schedule', icon: CalendarIcon },
  { name: 'Appointments', to: '/barber/appointments', icon: ClipboardDocumentCheckIcon },
  { name: 'Services', to: '/barber/services', icon: ScissorsIcon },
  { name: 'Reviews', to: '/barber/reviews', icon: StarIcon },
  { name: 'Gallery', to: '/barber/gallery', icon: PhotoIcon },
  { name: 'Blog', to: '/barber/blog', icon: PencilSquareIcon },
  { name: 'Notifications', to: '/barber/notifications', icon: BellAlertIcon },
  { name: 'Analytics', to: '/barber/analytics', icon: PresentationChartLineIcon },
  { name: 'Reports', to: '/barber/reports', icon: ChartBarIcon },
];
</script>
