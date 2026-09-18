export default [
	{ path: '/', name: 'home', component: () => import('../../modules/public/pages/HomePage.vue'), meta: { public: true } },
	{ path: '/about', name: 'public-about', component: () => import('../../modules/public/pages/AboutPage.vue'), meta: { public: true } },
	{ path: '/contact', name: 'public-contact', component: () => import('../../modules/public/pages/ContactPage.vue'), meta: { public: true } },
	{ path: '/privacy', name: 'privacy', component: () => import('../../modules/public/pages/PrivacyPage.vue'), meta: { public: true } },
	{ path: '/terms', name: 'terms', component: () => import('../../modules/public/pages/TermsPage.vue'), meta: { public: true } },
	{ path: '/account-deletion', name: 'account-deletion', component: () => import('../../modules/public/pages/AccountDeletionPage.vue'), meta: { public: true } },

	// Error pages
	{ path: '/403', name: 'forbidden', component: () => import('../../modules/public/pages/ForbiddenPage.vue'), meta: { public: true } },
	{ path: '/500', name: 'server-error', component: () => import('../../modules/public/pages/ServerErrorPage.vue'), meta: { public: true } },
	{ path: '/offline', name: 'offline', component: () => import('../../modules/public/pages/OfflinePage.vue'), meta: { public: true } },

	// Catch-all 404 — must be last
	{ path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../../modules/public/pages/NotFoundPage.vue'), meta: { public: true } },
];