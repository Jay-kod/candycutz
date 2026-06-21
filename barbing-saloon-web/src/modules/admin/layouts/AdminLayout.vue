<template>
  <DashboardLayout
    portalName="Admin"
    homeRoute="/admin/dashboard"
    profileRoute="/admin/settings"
    :navItems="navItems"
    theme="admin"
  >
    <slot />
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '../../../core/layouts/DashboardLayout.vue';
import {
  HomeIcon,
  CalendarDaysIcon,
  SparklesIcon,
  PhotoIcon,
  ChatBubbleLeftRightIcon,
  DocumentTextIcon,
  ClockIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  UserGroupIcon,
  UsersIcon
} from '@heroicons/vue/24/outline';

const navItems = [
  { name: 'Dashboard', to: '/admin/dashboard', icon: HomeIcon },
  { name: 'Appointments', to: '/admin/appointments', icon: CalendarDaysIcon },
  { name: 'Customers', to: '/admin/customers', icon: UserGroupIcon },
  { name: 'Barbers', to: '/admin/barbers', icon: UsersIcon },
  { name: 'Services', to: '/admin/services', icon: SparklesIcon },
  { name: 'Gallery', to: '/admin/gallery', icon: PhotoIcon },
  { name: 'Testimonials', to: '/admin/testimonials', icon: ChatBubbleLeftRightIcon },
  { name: 'Blog', to: '/admin/blog', icon: DocumentTextIcon },
  { name: 'Working Hours', to: '/admin/working-hours', icon: ClockIcon },
  { name: 'Reports', to: '/admin/reports', icon: ChartBarIcon },
  { name: 'Settings', to: '/admin/settings', icon: Cog6ToothIcon },
];
</script>