export default [
	{ path: '/', name: 'home', component: () => import('./pages/HomePage.vue'), meta: { public: true } },
	{ path: '/services', redirect: '/customer/login' },
	{ path: '/about', name: 'public-about', component: () => import('./pages/AboutPage.vue'), meta: { public: true } },
	{ path: '/barber/:id', redirect: '/customer/login' },
	{ path: '/gallery', redirect: '/customer/login' },
	{ path: '/blog', redirect: '/customer/login' },
	{ path: '/blog/:slug', redirect: '/customer/login' },
	{ path: '/testimonials', redirect: '/customer/login' },
	{ path: '/contact', name: 'public-contact', component: () => import('./pages/ContactPage.vue'), meta: { public: true } },
	{ path: '/privacy', name: 'privacy', component: () => import('./pages/PrivacyPage.vue'), meta: { public: true } },
	{ path: '/terms', name: 'terms', component: () => import('./pages/TermsPage.vue'), meta: { public: true } },

	// Error pages
	{ path: '/403', name: 'forbidden', component: () => import('./pages/ForbiddenPage.vue'), meta: { public: true } },
	{ path: '/500', name: 'server-error', component: () => import('./pages/ServerErrorPage.vue'), meta: { public: true } },
	{ path: '/offline', name: 'offline', component: () => import('./pages/OfflinePage.vue'), meta: { public: true } },

	// Catch-all 404 — must be last
	{ path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('./pages/NotFoundPage.vue'), meta: { public: true } },
];