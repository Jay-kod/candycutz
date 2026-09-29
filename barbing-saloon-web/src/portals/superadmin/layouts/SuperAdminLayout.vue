<template>
  <DashboardLayout
    portalName="Super Admin"
    homeRoute="/superadmin/dashboard"
    profileRoute="/superadmin/settings"
    :navItems="navItems"
    theme="superadmin"
  >
    <slot />
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '../../../core/layouts/DashboardLayout.vue';
import {
  HomeIcon,
  UserGroupIcon,
  UsersIcon,
  ClipboardDocumentListIcon,
  Cog6ToothIcon,
  CalendarDaysIcon,
  UserPlusIcon,
  SparklesIcon,
  PhotoIcon,
  ChatBubbleLeftRightIcon,
  DocumentTextIcon,
  FlagIcon,
  ClockIcon,
  KeyIcon,
  CodeBracketIcon,
  ShieldCheckIcon,
  PresentationChartLineIcon,
  ChartBarIcon,
  ScissorsIcon,
} from '@heroicons/vue/24/outline';

const navItems = [
  // Overview
  { name: 'Dashboard', to: '/superadmin/dashboard', icon: HomeIcon },
  { name: 'Users', to: '/superadmin/users', icon: UserGroupIcon },
  { name: 'Audit Logs', to: '/superadmin/audit-logs', icon: ClipboardDocumentListIcon },

  // Platform Operations
  { name: 'Appointments', to: '/superadmin/appointments', icon: CalendarDaysIcon },
  { name: 'Walk-In Queue', to: '/superadmin/walk-in', icon: UserPlusIcon },
  { name: 'Customers', to: '/superadmin/customers', icon: UsersIcon },
  { name: 'Barbers', to: '/superadmin/barbers', icon: ScissorsIcon },
  { name: 'Services', to: '/superadmin/services', icon: SparklesIcon },

  // Content Management
  { name: 'Gallery', to: '/superadmin/gallery', icon: PhotoIcon },
  { name: 'Testimonials', to: '/superadmin/testimonials', icon: ChatBubbleLeftRightIcon },
  { name: 'Blog', to: '/superadmin/blog', icon: DocumentTextIcon },

  // System Governance
  { name: 'Feature Flags', to: '/superadmin/feature-flags', icon: FlagIcon },
  { name: 'Working Hours', to: '/superadmin/working-hours', icon: ClockIcon },
  { name: 'Integrations', to: '/superadmin/integrations', icon: KeyIcon },
  { name: 'API Directory', to: '/superadmin/api', icon: CodeBracketIcon },
  { name: 'System Logs', to: '/superadmin/system-logs', icon: ClipboardDocumentListIcon },
  { name: 'Verifications', to: '/superadmin/verifications', icon: ShieldCheckIcon },

  // Analytics & Reports
  { name: 'Analytics', to: '/superadmin/analytics', icon: PresentationChartLineIcon },
  { name: 'Reports', to: '/superadmin/reports', icon: ChartBarIcon },

  // Settings
  { name: 'Settings', to: '/superadmin/settings', icon: Cog6ToothIcon },
];
</script>
