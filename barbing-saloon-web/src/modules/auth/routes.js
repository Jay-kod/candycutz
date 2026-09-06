export default [
	{ path: '/customer/login', name: 'login', component: () => import('./pages/LoginPage.vue'), meta: { public: true } },
	{ path: '/customer/register', name: 'register', component: () => import('./pages/RegisterPage.vue'), meta: { public: true } },
	{ path: '/forgot-password', name: 'forgot-password', component: () => import('./pages/ForgotPasswordPage.vue'), meta: { public: true } },
	{ path: '/reset-password', name: 'reset-password', component: () => import('./pages/ResetPasswordPage.vue'), meta: { public: true } },
	{ path: '/admin/login', name: 'admin-login', component: () => import('./pages/AdminLoginPage.vue'), meta: { public: true } },
	{ path: '/barber/login', name: 'barber-login', component: () => import('./pages/BarberLoginPage.vue'), meta: { public: true } },
];