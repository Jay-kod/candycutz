<template>
  <div class="space-y-8 animate-fade-in pb-12">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-12 shadow-2xl">
      <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/10 blur-3xl"></div>
      <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-admin/80 font-bold flex items-center gap-2">
            <ShieldCheckIcon class="w-4 h-4" /> Legal Pages
          </p>
          <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Privacy Policy</span>
          </h1>
          <p class="mt-3 text-sm lg:text-base text-ivory/60 max-w-xl">Edit the content of your public Privacy Policy page.</p>
        </div>
        <button 
          @click="saveContent" 
          :disabled="saving" 
          class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-6 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
          <CheckIcon v-else class="h-5 w-5" />
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>

    <!-- Content Editor -->
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <h2 class="font-display text-xl text-theme-text">Page Header</h2>
          <p class="mt-1 text-xs text-ivory/40">The main title shown at the top of the page.</p>
        </div>
        <div class="p-8 space-y-6">
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Page Title</label>
            <input v-model="content.privacy_title" type="text" placeholder="Privacy Policy" 
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
          </div>
        </div>
      </div>

      <!-- Sections -->
      <div v-for="(section, index) in sections" :key="index" class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-5 flex items-center justify-between">
          <h2 class="font-display text-lg text-theme-text">Section {{ index + 1 }}</h2>
          <button v-if="sections.length > 1" @click="removeSection(index)" class="text-red-400/60 hover:text-red-400 text-xs uppercase tracking-widest font-bold transition-colors">Remove</button>
        </div>
        <div class="p-8 space-y-6">
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Section Heading</label>
            <input v-model="section.heading" type="text" placeholder="1. Introduction" 
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
          </div>
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Content</label>
            <textarea v-model="section.body" rows="5" placeholder="Section content..."
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-y"></textarea>
          </div>
        </div>
      </div>

      <!-- Add Section Button -->
      <button @click="addSection" class="w-full rounded-2xl border-2 border-dashed border-white/10 bg-black/10 py-5 text-ivory/40 hover:border-admin/30 hover:text-admin/80 transition-all duration-300 font-display text-sm uppercase tracking-widest">
        + Add New Section
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from '../../../../core/composables/useToast';
import { adminApi } from '../../api/admin.api';
import { ShieldCheckIcon, CheckIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const saving = ref(false);

const content = ref({
  privacy_title: 'Privacy Policy'
});

const sections = ref([
  {
    heading: '1. Introduction',
    body: 'Welcome to CandyCutz. We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you as to how we look after your personal data when you visit our website (regardless of where you visit it from) and tell you about your privacy rights and how the law protects you.'
  },
  {
    heading: '2. The Data We Collect About You',
    body: "Personal data, or personal information, means any information about an individual from which that person can be identified. We may collect, use, store and transfer different kinds of personal data about you:\n\n• Identity Data includes first name, last name, username or similar identifier.\n• Contact Data includes billing address, email address and telephone numbers.\n• Transaction Data includes details about payments to and from you and other details of services you have purchased from us.\n• Profile Data includes your username and password, purchases or orders made by you, your interests, preferences, feedback and survey responses."
  },
  {
    heading: '3. How We Use Your Personal Data',
    body: 'We will only use your personal data when the law allows us to. Most commonly, we will use your personal data to register you as a new customer, process and deliver your order, manage our relationship with you, and to improve our website, products/services, marketing or customer relationships.'
  },
  {
    heading: '4. Data Security',
    body: 'We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used or accessed in an unauthorised way, altered or disclosed. In addition, we limit access to your personal data to those employees, agents, contractors and other third parties who have a business need to know.'
  },
  {
    heading: '5. Your Legal Rights',
    body: "Under certain circumstances, you have rights under data protection laws in relation to your personal data, including the right to:\n\n• Request access to your personal data.\n• Request correction of your personal data.\n• Request erasure of your personal data.\n• Object to processing of your personal data."
  }
]);

const addSection = () => {
  sections.value.push({ heading: '', body: '' });
};

const removeSection = (index) => {
  sections.value.splice(index, 1);
};

const fetchContent = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data.data;
    if (data) {
      if (data.privacy_title) content.value.privacy_title = data.privacy_title;
      if (data.privacy_sections) {
        try {
          const parsed = JSON.parse(data.privacy_sections);
          if (Array.isArray(parsed) && parsed.length > 0) sections.value = parsed;
        } catch (e) { /* use defaults */ }
      }
    }
  } catch (error) {
    console.error('Failed to load privacy content:', error);
  }
};

const saveContent = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('settings[privacy_title]', content.value.privacy_title);
    formData.append('settings[privacy_sections]', JSON.stringify(sections.value));
    await adminApi.updateSettings(formData);
    toast.success('Privacy Policy saved!');
  } catch (error) {
    toast.error('Failed to save changes.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchContent();
});
</script>
