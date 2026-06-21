export default [
  { path: '/superadmin/dashboard', name: 'superadmin-dashboard', component: () => import('./pages/DashboardPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/users', name: 'superadmin-users', component: () => import('./pages/UsersPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/settings', name: 'superadmin-settings', component: () => import('./pages/SettingsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
  { path: '/superadmin/audit-logs', name: 'superadmin-audit-logs', component: () => import('./pages/AuditLogsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
];