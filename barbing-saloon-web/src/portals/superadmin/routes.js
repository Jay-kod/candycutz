export default [
  { path: '/superadmin/dashboard', name: 'superadmin-dashboard', component: () => import('../../modules/superadmin/pages/DashboardPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/users', name: 'superadmin-users', component: () => import('../../modules/superadmin/pages/UsersPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/settings', name: 'superadmin-settings', component: () => import('../../modules/superadmin/pages/SettingsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/audit-logs', name: 'superadmin-audit-logs', component: () => import('../../modules/superadmin/pages/AuditLogsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
];