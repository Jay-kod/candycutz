<template>
  <div class="h-screen flex bg-theme-bg overflow-hidden relative text-theme-text">
    
    <!-- Left Section: Cinematic Branding (Hidden on mobile) -->
    <div class="hidden lg:flex lg:w-1/2 relative flex-col items-center justify-between p-16 overflow-hidden text-center">
      <!-- Background Effects -->
      <div class="absolute inset-0 bg-theme-bg z-0"></div>
      <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-gold/10 via-transparent to-transparent z-0"></div>
      <div class="absolute bottom-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_bottom_right,_var(--tw-gradient-stops))] from-gold/5 via-transparent to-transparent z-0"></div>
      
      <!-- Decorative Lines -->
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1px] h-full bg-gradient-to-b from-transparent via-theme-border/20 to-transparent z-0"></div>
      <div class="absolute top-16 left-0 h-[1px] w-full bg-gradient-to-r from-transparent via-theme-border/20 to-transparent z-0"></div>
      
      <!-- Content -->
      <div class="relative z-10">
        <a href="/" class="text-2xl font-display font-bold text-theme-text tracking-widest uppercase flex flex-col items-center gap-2">
          <img src="/images/logo-icon.png" alt="CandyCutz Logo" class="h-36 w-36 object-contain drop-shadow-[0_0_16px_rgba(212,175,55,0.5)] rounded-full" />
          <span><span class="text-gold">Candy</span>Cutz</span>
        </a>
      </div>
      
      <div class="relative z-10 max-w-lg">
        <div class="w-12 h-1 bg-gold mb-8 mx-auto"></div>
        <h2 class="font-display text-5xl text-theme-text leading-tight mb-6">
          Join the<br />CandyCutz family.
        </h2>
        <p class="text-lg text-theme-muted font-light leading-relaxed">
          Create your account to unlock premium grooming services, manage bookings, and enjoy the full CandyCutz experience.
        </p>
      </div>
      
      <div class="relative z-10">
        <p class="text-xs uppercase tracking-[0.3em] text-theme-muted font-semibold">
          &copy; {{ new Date().getFullYear() }} CandyCutz. All rights reserved.
        </p>
      </div>
    </div>

    <!-- Right Section: Register Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-16 relative z-10 bg-theme-surface lg:bg-transparent shadow-[-20px_0_50px_rgba(0,0,0,0.5)] lg:shadow-none border-l border-theme-border overflow-y-auto">
      
      <!-- Mobile Logo -->
      <div class="absolute top-8 left-8 lg:hidden">
        <a href="/" class="text-xl font-display font-bold text-theme-text tracking-widest uppercase flex flex-col gap-1 w-fit">
          <img src="/images/logo-icon.png" alt="CandyCutz Logo" class="h-8 w-8 object-contain rounded-full" />
          <span><span class="text-gold">Candy</span>Cutz</span>
        </a>
      </div>

      <div class="w-full max-w-sm">
        <div class="mb-6">
          <div class="text-center">
            <h1 class="font-display font-bold text-3xl sm:text-4xl text-gold mb-4">Create Account</h1>
          </div>
          <p class="text-theme-muted text-xs sm:text-sm">Fill in your details to get started.</p>
        </div>

        <form class="space-y-4" @submit.prevent="submitForm">
          <!-- General Error -->
          <div v-if="generalError" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-xs text-red-400 font-medium flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ generalError }}</span>
          </div>

          <!-- Name -->
          <div class="relative group">
            <label for="reg-name" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Full Name</label>
            <input 
              v-model="name" 
              id="reg-name"
              class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none" 
              placeholder="John Doe" 
            />
            <p v-if="errors.name" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.name }}</p>
          </div>

          <!-- Phone -->
          <div class="relative group">
            <label for="reg-phone" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Phone Number</label>
            <input 
              v-model="phone" 
              id="reg-phone"
              class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none" 
              placeholder="+234 800 000 0000" 
            />
            <p v-if="errors.phone" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.phone }}</p>
          </div>

          <!-- Email -->
          <div class="relative group">
            <label for="reg-email" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Email Address</label>
            <input 
              v-model="email" 
              id="reg-email"
              class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none" 
              placeholder="you@example.com" 
              type="email" 
            />
            <p v-if="errors.email" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.email }}</p>
          </div>

          <!-- Password -->
          <div class="relative group">
            <label for="reg-password" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Password</label>
            <input 
              v-model="password" 
              id="reg-password"
              class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none" 
              type="password" 
              placeholder="Minimum 8 characters" 
            />
            <p v-if="errors.password" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.password }}</p>
          </div>

          <!-- Confirm Password -->
          <div class="relative group">
            <label for="reg-password-confirm" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Confirm Password</label>
            <input 
              v-model="passwordConfirmation" 
              id="reg-password-confirm"
              class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none" 
              type="password" 
              placeholder="Re-enter your password" 
            />
          </div>

          <!-- Submit Button -->
          <div class="pt-2">
            <button 
              :disabled="loading" 
              class="relative w-full overflow-hidden rounded-lg bg-gold px-4 py-3 text-sm font-bold text-obsidian tracking-wide transition-all duration-300 hover:bg-gold-dark hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-70 disabled:cursor-not-allowed">
              <span class="relative z-10 flex items-center justify-center gap-2">
                <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-obsidian" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ loading ? 'Creating Account...' : 'Create Account' }}
              </span>
            </button>
          </div>
        </form>

        <p class="mt-6 text-center text-xs text-theme-muted">
          Already have an account?
          <RouterLink to="/customer/login" class="font-semibold text-gold hover:text-gold-dark transition-colors">Sign in</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { object, string } from 'yup';
import { useAuth } from '../composables/useAuth';
import { useDark } from '@vueuse/core';

const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
});

isDark.value = true;

const router = useRouter();
const { register, redirectAfterLogin } = useAuth();

const name = ref('');
const phone = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const errors = reactive({});
const generalError = ref('');

const schema = object({
  name: string().required('Name is required').min(2, 'Name must be at least 2 characters'),
  phone: string().required('Phone is required'),
  email: string().required('Email is required').email('Enter a valid email'),
  password: string().required('Password is required').min(8, 'Password must be at least 8 characters'),
});

async function submitForm() {
  loading.value = true;
  generalError.value = '';
  Object.keys(errors).forEach((key) => delete errors[key]);

  try {
    await schema.validate({
      name: name.value,
      phone: phone.value,
      email: email.value,
      password: password.value,
    }, { abortEarly: false });

    await register({
      name: name.value,
      phone: phone.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });

    await router.push(redirectAfterLogin());
  } catch (error) {
    if (error.inner) {
      error.inner.forEach((item) => {
        errors[item.path] = item.message;
      });
    } else {
      generalError.value = error.message || 'Registration failed. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>