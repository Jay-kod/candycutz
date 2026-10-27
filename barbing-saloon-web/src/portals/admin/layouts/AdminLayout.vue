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
  FlagIcon,
  HeartIcon,
  Cog6ToothIcon,
  ScissorsIcon,
  InformationCircleIcon,
  EnvelopeIcon,
  TrashIcon,
  BugAntIcon,
  ArchiveBoxIcon,
  RectangleStackIcon,
  BuildingStorefrontIcon,
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
  { name: 'Brand Identity', to: '/admin/brand-identity', icon: BuildingStorefrontIcon },
  { name: 'Home Page', to: '/admin/websites/home', icon: HomeIcon },
  { name: 'Features Block', to: '/admin/websites/features', icon: SparklesIcon, badge: 'Block' },
  { name: 'Homepage CTA', to: '/admin/websites/cta', icon: ScissorsIcon },
  { name: 'Page Heroes', to: '/admin/websites/heroes', icon: RectangleStackIcon },
  { name: 'About Us Page', to: '/admin/websites/about', icon: InformationCircleIcon },
  { name: 'Services Page', to: '/admin/websites/services', icon: SparklesIcon },
  { name: 'Gallery Page', to: '/admin/websites/gallery', icon: PhotoIcon },
  { name: 'Contact Page', to: '/admin/websites/contact', icon: EnvelopeIcon },
  { name: 'Privacy Policy Page', to: '/admin/websites/privacy', icon: ShieldCheckIcon },
  { name: 'Terms of Service Page', to: '/admin/websites/terms', icon: DocumentTextIcon },
  { name: 'Legal Stuff Page', to: '/admin/websites/legal', icon: DocumentTextIcon },
  { name: 'Security Page', to: '/admin/websites/security', icon: ShieldCheckIcon },
  { name: 'Manage Cookies Page', to: '/admin/websites/cookies', icon: Cog6ToothIcon },
  { name: 'Account Deletion Page', to: '/admin/websites/account-deletion', icon: TrashIcon },

  // Content & Catalog
  { name: 'Services', to: '/admin/services', icon: SparklesIcon },
  { name: 'Gallery', to: '/admin/gallery', icon: PhotoIcon },
  { name: 'Testimonials', to: '/admin/testimonials', icon: ChatBubbleLeftRightIcon },
  { name: 'Feedback Inbox', to: '/admin/feedback', icon: ChatBubbleLeftRightIcon },
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
  { name: 'Health Checker', to: '/admin/health', icon: HeartIcon },
  { name: 'System Errors', to: '/admin/system-errors', icon: BugAntIcon },
  { name: 'Backup & Restore', to: '/admin/backups', icon: ArchiveBoxIcon },
  { name: 'Notifications', to: '/admin/notifications', icon: BellAlertIcon },
];
</script>