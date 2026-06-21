export default [
	{ path: '/admin/dashboard', name: 'admin-dashboard', component: () => import('./pages/DashboardPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
	{ path: '/admin/appointments', name: 'admin-appointments', component: () => import('./pages/AppointmentsPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
	{ path: '/admin/barbers', name: 'admin-barbers', component: () => import('./pages/BarbersPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
	{ path: '/admin/customers', name: 'admin-customers', component: () => import('./pages/CustomersPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
	{ path: '/admin/services', name: 'admin-services', component: () => import('./pages/ServicesPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
	{ path: '/admin/gallery', name: 'admin-gallery', component: () => import('./pages/GalleryPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
	{ path: '/admin/testimonials', name: 'admin-testimonials', component: () => import('./pages/TestimonialsPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
	{ path: '/admin/blog', name: 'admin-blog', component: () => import('./pages/BlogPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
	{ path: '/admin/working-hours', name: 'admin-working-hours', component: () => import('./pages/WorkingHoursPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
	{ path: '/admin/reports', name: 'admin-reports', component: () => import('./pages/ReportsPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
	{ path: '/admin/settings', name: 'admin-settings', component: () => import('./pages/SettingsPage.vue'), meta: { requiresAuth: true, roles: ['admin', 'super_admin'] } },
];