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
  UserGroupIcon,
  UsersIcon,
  ClipboardDocumentListIcon,
  BellAlertIcon,
  PresentationChartLineIcon,
  ShieldCheckIcon,
  GlobeAltIcon,
  UserPlusIcon,
  KeyIcon,
  CodeBracketIcon,
  DevicePhoneMobileIcon,
  FlagIcon,
  CheckBadgeIcon,
  BookmarkSquareIcon,
  CalendarIcon,
  UserCircleIcon,
  IdentificationIcon,
  HeartIcon,
  StarIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
  DocumentCheckIcon,
  ScissorsIcon,
  InformationCircleIcon,
  EnvelopeIcon,
  TrashIcon
} from '@heroicons/vue/24/outline';

const navItems = [
  // Overview & Operations
  { name: 'Dashboard', to: '/admin/dashboard', icon: HomeIcon },
  { name: 'Walk-In', to: '/admin/walk-in', icon: UserPlusIcon },
  { name: 'Appointments', to: '/admin/appointments', icon: CalendarDaysIcon },
  { name: 'Customers', to: '/admin/customers', icon: UserGroupIcon },
  { name: 'Barbers', to: '/admin/barbers', icon: UsersIcon },

  // The Website (All Website CMS Pages)
  { name: 'Website Overview', to: '/admin/websites', icon: GlobeAltIcon, badge: 'CMS' },
  { name: 'Home Page', to: '/admin/websites/home', icon: HomeIcon },
  { name: 'About Us Page', to: '/admin/websites/about', icon: InformationCircleIcon },
  { name: 'Services Page', to: '/admin/websites/services', icon: SparklesIcon },
  { name: 'Gallery Page', to: '/admin/websites/gallery', icon: PhotoIcon },
  { name: 'Contact Page', to: '/admin/websites/contact', icon: EnvelopeIcon },
  { name: 'Privacy Policy Page', to: '/admin/websites/privacy', icon: ShieldCheckIcon },
  { name: 'Terms of Service Page', to: '/admin/websites/terms', icon: DocumentTextIcon },
  { name: 'Account Deletion Page', to: '/admin/websites/account-deletion', icon: TrashIcon },

  // The App (All Mobile App Pages)
  { name: 'App Control Center', to: '/admin/mobile-app', icon: DevicePhoneMobileIcon, badge: 'Hub' },
  { name: 'Onboarding', to: '/admin/mobile-app/pages/onboarding', icon: SparklesIcon },
  { name: 'Home / Discover', to: '/admin/mobile-app/pages/home', icon: HomeIcon },
  { name: 'Services Catalog', to: '/admin/mobile-app/pages/services', icon: ScissorsIcon },
  { name: 'Book Appointment', to: '/admin/mobile-app/pages/book', icon: CalendarDaysIcon },
  { name: 'Booking Confirmation', to: '/admin/mobile-app/pages/confirmation', icon: CheckBadgeIcon },
  { name: 'Walk-In Queue', to: '/admin/mobile-app/pages/walkin', icon: UserPlusIcon },
  { name: 'My Bookings', to: '/admin/mobile-app/pages/bookings', icon: BookmarkSquareIcon },
  { name: 'Barber Appointments', to: '/admin/mobile-app/pages/appointments', icon: ClockIcon },
  { name: 'Barber Schedule', to: '/admin/mobile-app/pages/schedule', icon: CalendarIcon },
  { name: 'Customer Profile', to: '/admin/mobile-app/pages/profile', icon: UserCircleIcon },
  { name: 'Edit Profile', to: '/admin/mobile-app/pages/profile-edit', icon: UserCircleIcon },
  { name: 'Barber Profile', to: '/admin/mobile-app/pages/barber-profile', icon: IdentificationIcon },
  { name: 'Wishlist', to: '/admin/mobile-app/pages/wishlist', icon: HeartIcon },
  { name: 'Reviews & Ratings', to: '/admin/mobile-app/pages/reviews', icon: StarIcon },
  { name: 'Hairstyle Gallery', to: '/admin/mobile-app/pages/gallery', icon: PhotoIcon },
  { name: 'Grooming Blog', to: '/admin/mobile-app/pages/blog', icon: DocumentTextIcon },
  { name: 'Push Notifications', to: '/admin/mobile-app/pages/notifications', icon: BellAlertIcon },
  { name: 'App Settings & Theme', to: '/admin/mobile-app/pages/settings', icon: Cog6ToothIcon },
  { name: 'Personal Analytics', to: '/admin/mobile-app/pages/analytics', icon: ChartBarIcon },
  { name: 'Sign In', to: '/admin/mobile-app/pages/login', icon: ArrowRightOnRectangleIcon },
  { name: 'Sign Up', to: '/admin/mobile-app/pages/register', icon: UserPlusIcon },
  { name: 'Forgot Password', to: '/admin/mobile-app/pages/forgot-password', icon: KeyIcon },
  { name: 'Privacy Policy', to: '/admin/mobile-app/pages/privacy', icon: ShieldCheckIcon },
  { name: 'Terms of Service', to: '/admin/mobile-app/pages/terms', icon: DocumentCheckIcon },

  // Content & Catalog
  { name: 'Services', to: '/admin/services', icon: SparklesIcon },
  { name: 'Gallery', to: '/admin/gallery', icon: PhotoIcon },
  { name: 'Testimonials', to: '/admin/testimonials', icon: ChatBubbleLeftRightIcon },
  { name: 'Blog', to: '/admin/blog', icon: DocumentTextIcon },

  // Management & Security
  { name: 'Feature Flags', to: '/admin/feature-flags', icon: FlagIcon },
  { name: 'Working Hours', to: '/admin/working-hours', icon: ClockIcon },
  { name: 'Integrations', to: '/admin/integrations', icon: KeyIcon },
  { name: 'API Directory', to: '/admin/api', icon: CodeBracketIcon },
  { name: 'Analytics', to: '/admin/analytics', icon: PresentationChartLineIcon },
  { name: 'Reports', to: '/admin/reports', icon: ChartBarIcon },
  { name: 'Verifications', to: '/admin/verifications', icon: ShieldCheckIcon },
  { name: 'System Logs', to: '/admin/system-logs', icon: ClipboardDocumentListIcon },
  { name: 'Notifications', to: '/admin/notifications', icon: BellAlertIcon },
];
</script>