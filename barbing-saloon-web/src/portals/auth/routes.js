export default [
	{ path: '/customer/login', name: 'login', component: () => import('../../modules/auth/pages/LoginPage.vue'), meta: { public: true } },
	{ path: '/customer/register', name: 'register', component: () => import('../../modules/auth/pages/RegisterPage.vue'), meta: { public: true } },
	{ path: '/forgot-password', name: 'forgot-password', component: () => import('../../modules/auth/pages/ForgotPasswordPage.vue'), meta: { public: true } },
	{ path: '/reset-password', name: 'reset-password', component: () => import('../../modules/auth/pages/ResetPasswordPage.vue'), meta: { public: true } },
	{ path: '/admin/login', name: 'admin-login', component: () => import('../../modules/auth/pages/AdminLoginPage.vue'), meta: { public: true } },
	{ path: '/barber/login', name: 'barber-login', component: () => import('../../modules/auth/pages/BarberLoginPage.vue'), meta: { public: true } },
];