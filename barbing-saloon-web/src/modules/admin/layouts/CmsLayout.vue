<template>
  <AdminLayout>
    <div class="flex flex-col lg:flex-row gap-8 pb-12">
      
      <!-- CMS Sidebar -->
      <aside class="w-full lg:w-64 shrink-0">
        <div class="sticky top-28 space-y-6">
          
          <div>
            <h2 class="text-xs uppercase tracking-widest text-admin/60 font-bold px-4 mb-3">Public Pages</h2>
            <nav class="space-y-1">
              <RouterLink 
                v-for="page in pages" :key="page.id"
                :to="page.route"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 text-sm font-medium border border-transparent"
                :class="route.path.includes(page.route) ? 'bg-admin/10 text-admin border-admin/20 shadow-[inset_0_0_0_1px_rgba(255,103,0,0.1)]' : 'text-ivory/60 hover:bg-white/5 hover:text-ivory/90'"
              >
                <component :is="page.icon" class="w-5 h-5 transition-transform" :class="route.path.includes(page.route) ? 'scale-110' : ''" />
                {{ page.name }}
              </RouterLink>
            </nav>
          </div>

        </div>
      </aside>

      <!-- CMS Content Area -->
      <main class="flex-1 min-w-0">
        <RouterView v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </RouterView>
      </main>

    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AdminLayout from './AdminLayout.vue';
import { 
  HomeIcon, 
  InformationCircleIcon, 
  SparklesIcon, 
  PhotoIcon, 
  EnvelopeIcon,
  ShieldCheckIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline';

const route = useRoute();

const pages = [
  { id: 'home', name: 'Home Page', route: '/admin/websites/home', icon: HomeIcon },
  { id: 'about', name: 'About Us', route: '/admin/websites/about', icon: InformationCircleIcon },
  { id: 'contact', name: 'Contact', route: '/admin/websites/contact', icon: EnvelopeIcon },
  { id: 'privacy', name: 'Privacy Policy', route: '/admin/websites/privacy', icon: ShieldCheckIcon },
  { id: 'terms', name: 'Terms of Service', route: '/admin/websites/terms', icon: DocumentTextIcon },
];
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
