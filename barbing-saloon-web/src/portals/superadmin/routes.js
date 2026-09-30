export default [
  // Overview
  { path: '/superadmin/dashboard', name: 'superadmin-dashboard', component: () => import('../../modules/superadmin/pages/DashboardPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/users', name: 'superadmin-users', component: () => import('../../modules/superadmin/pages/UsersPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/audit-logs', name: 'superadmin-audit-logs', component: () => import('../../modules/superadmin/pages/AuditLogsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },

  // Platform Operations — reuse admin page components (backend already grants super_admin access)
  { path: '/superadmin/appointments', name: 'superadmin-appointments', component: () => import('@/features/appointments/pages/AdminAppointmentsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/walk-in', name: 'superadmin-walk-in', component: () => import('../../modules/admin/pages/WalkInPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/customers', name: 'superadmin-customers', component: () => import('@/features/customers/pages/CustomersPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/barbers', name: 'superadmin-barbers', component: () => import('../../modules/admin/pages/BarbersPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/services', name: 'superadmin-services', component: () => import('../../modules/admin/pages/ServicesPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },

  // Content Management
  { path: '/superadmin/gallery', name: 'superadmin-gallery', component: () => import('../../features/gallery/pages/AdminGalleryPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/testimonials', name: 'superadmin-testimonials', component: () => import('../../modules/admin/pages/TestimonialsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/blog', name: 'superadmin-blog', component: () => import('../../modules/admin/pages/BlogPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },

  // System Governance
  { path: '/superadmin/feature-flags', name: 'superadmin-feature-flags', component: () => import('../../modules/admin/pages/FeatureFlagsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/working-hours', name: 'superadmin-working-hours', component: () => import('../../modules/admin/pages/WorkingHoursPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/integrations', name: 'superadmin-integrations', component: () => import('../../modules/admin/pages/IntegrationsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/api', name: 'superadmin-api', component: () => import('../../modules/admin/pages/ApiReferencePage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/system-logs', name: 'superadmin-system-logs', component: () => import('../../modules/admin/pages/SystemLogsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/verifications', name: 'superadmin-verifications', component: () => import('@/features/verification/pages/AdminVerificationPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/health', name: 'superadmin-health-checker', component: () => import('../../modules/admin/pages/HealthCheckerPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/system-errors', name: 'superadmin-system-errors', component: () => import('../../modules/admin/pages/SystemErrorsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/backups', name: 'superadmin-backups', component: () => import('../../modules/admin/pages/BackupRestorePage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },

  // Analytics & Reports
  { path: '/superadmin/analytics', name: 'superadmin-analytics', component: () => import('@/features/analytics/pages/AdminAnalyticsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/reports', name: 'superadmin-reports', component: () => import('@/features/analytics/pages/AdminReportsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },

  // Settings
  { path: '/superadmin/settings', name: 'superadmin-settings', component: () => import('../../modules/superadmin/pages/SettingsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
];