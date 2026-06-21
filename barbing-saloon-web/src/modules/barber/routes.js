export default [
	{ path: '/barber/dashboard', name: 'barber-dashboard', component: () => import('./pages/DashboardPage.vue'), meta: { requiresAuth: true, roles: ['barber'] } },
	{ path: '/barber/schedule', name: 'barber-schedule', component: () => import('./pages/SchedulePage.vue'), meta: { requiresAuth: true, roles: ['barber'] } },
	{ path: '/barber/appointments', name: 'barber-appointments', component: () => import('./pages/AppointmentsPage.vue'), meta: { requiresAuth: true, roles: ['barber'] } },
	{ path: '/barber/profile', name: 'barber-profile', component: () => import('./pages/ProfilePage.vue'), meta: { requiresAuth: true, roles: ['barber'] } },
	{ path: '/barber/services', name: 'barber-services', component: () => import('./pages/ServicesManagementPage.vue'), meta: { requiresAuth: true, roles: ['barber'] } },
	{ path: '/barber/gallery', name: 'barber-gallery', component: () => import('./pages/GalleryManagementPage.vue'), meta: { requiresAuth: true, roles: ['barber'] } },
	{ path: '/barber/blog', name: 'barber-blog', component: () => import('./pages/BlogManagementPage.vue'), meta: { requiresAuth: true, roles: ['barber'] } },
];