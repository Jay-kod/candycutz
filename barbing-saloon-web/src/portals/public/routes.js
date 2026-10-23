export default [
	{ path: '/', name: 'home', component: () => import('../../modules/public/pages/HomePage.vue'), meta: { public: true } },
	{ path: '/services', name: 'public-services', component: () => import('../../modules/public/pages/ServicesPage.vue'), meta: { public: true } },
	{ path: '/gallery', name: 'public-gallery', component: () => import('../../modules/public/pages/GalleryPage.vue'), meta: { public: true } },
	{ path: '/about', name: 'public-about', component: () => import('../../modules/public/pages/AboutPage.vue'), meta: { public: true } },
	{ path: '/contact', name: 'public-contact', component: () => import('../../modules/public/pages/ContactPage.vue'), meta: { public: true } },
	{ path: '/privacy', name: 'privacy', component: () => import('../../modules/public/pages/PrivacyPage.vue'), meta: { public: true } },
	{ path: '/terms', name: 'terms', component: () => import('../../modules/public/pages/TermsPage.vue'), meta: { public: true } },
	{
		path: '/legal',
		name: 'legal',
		component: () => import('../../modules/public/pages/LegalContentPage.vue'),
		props: {
			settingPrefix: 'legal_stuff',
			eyebrow: 'Legal',
			fallbackTitle: 'Legal Stuff',
			defaultSections: [
				{ heading: 'Legal information', body: 'Review the CandyCutz terms, privacy information, security information, and cookie preferences using the links below.' },
			],
			showLegalLinks: true,
		},
		meta: { public: true },
	},
	{
		path: '/security',
		name: 'security',
		component: () => import('../../modules/public/pages/LegalContentPage.vue'),
		props: {
			settingPrefix: 'security',
			eyebrow: 'Security',
			fallbackTitle: 'Security',
			defaultSections: [
				{ heading: 'Security information', body: 'Security information is being prepared. For questions or to report a security concern, please contact CandyCutz.' },
			],
		},
		meta: { public: true },
	},
	{ path: '/cookies', name: 'cookie-preferences', component: () => import('../../modules/public/pages/CookiePreferencesPage.vue'), meta: { public: true } },
	{ path: '/account-deletion', name: 'account-deletion', component: () => import('../../modules/public/pages/AccountDeletionPage.vue'), meta: { public: true } },

	// Error pages
	{ path: '/403', name: 'forbidden', component: () => import('../../modules/public/pages/ForbiddenPage.vue'), meta: { public: true } },
	{ path: '/500', name: 'server-error', component: () => import('../../modules/public/pages/ServerErrorPage.vue'), meta: { public: true } },
	{ path: '/offline', name: 'offline', component: () => import('../../modules/public/pages/OfflinePage.vue'), meta: { public: true } },

	// Catch-all 404 — must be last
	{ path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../../modules/public/pages/NotFoundPage.vue'), meta: { public: true } },
];