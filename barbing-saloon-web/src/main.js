import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { registerRouteGuards } from './core/guards/routeGuards';
import { useAuthStore } from './modules/auth/store/auth.store';
import './assets/css/main.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);

async function bootstrap() {
	const auth = useAuthStore();

	await auth.fetchUser();

	app.use(router);
	registerRouteGuards(router);
	app.mount('#app');
}

bootstrap();