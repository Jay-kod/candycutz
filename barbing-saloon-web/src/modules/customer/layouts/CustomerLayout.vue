<template>
  <DashboardLayout
    portalName="Customer"
    homeRoute="/customer/dashboard"
    profileRoute="/customer/dashboard/profile"
    :navItems="navItems"
  >
    <slot />
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '../../../core/layouts/DashboardLayout.vue';
import {
  HomeIcon,
  CalendarDaysIcon,
  UserIcon,
  StarIcon,
  HeartIcon,
  ScissorsIcon,
  PhotoIcon,
  BookOpenIcon,
  ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/outline';

const navItems = [
  { name: 'Dashboard', to: '/customer/dashboard', icon: HomeIcon },
  { name: 'Browse Services', to: '/customer/dashboard/services', icon: ScissorsIcon },
  { name: 'Bookings', to: '/customer/dashboard/bookings', icon: CalendarDaysIcon },
  { name: 'Reviews', to: '/customer/dashboard/reviews', icon: StarIcon },
  { name: 'Wishlist', to: '/customer/dashboard/wishlist', icon: HeartIcon },
  { name: 'Gallery', to: '/customer/dashboard/gallery', icon: PhotoIcon },
  { name: 'Journal', to: '/customer/dashboard/blog', icon: BookOpenIcon },
  { name: 'Testimonials', to: '/customer/dashboard/testimonials', icon: ChatBubbleLeftRightIcon },
];
</script>