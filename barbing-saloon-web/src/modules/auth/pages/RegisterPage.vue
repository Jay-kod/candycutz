<template>
  <div class="min-h-[100dvh] flex flex-col lg:flex-row bg-theme-bg relative text-theme-text overflow-y-auto">
    
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
          <img :src="'/images/logo-icon.png'" :alt="`${brandName} logo`" class="h-36 w-36 object-contain rounded-full" />
          <BrandWordmark :first="brandNameFirst" :second="brandNameSecond" :gold-first="brandGoldPart === 'first'" :space-between="brandNameSeparator === 'space'" />
        </a>
      </div>
      
      <div class="relative z-10 max-w-lg">
        <div class="w-12 h-1 bg-gold mb-8 mx-auto"></div>
        <h2 class="font-display text-5xl text-theme-text leading-tight mb-6">
          Join the<br />{{ brandName }} family.
        </h2>
        <p class="text-lg text-theme-muted font-light leading-relaxed">
          Create your account to unlock premium grooming services, manage bookings, and enjoy the full {{ brandName }} experience.
        </p>
      </div>
      
      <div class="relative z-10">
        <p class="text-xs uppercase tracking-[0.3em] text-theme-muted font-semibold">
          &copy; {{ new Date().getFullYear() }} <BrandWordmark :first="brandNameFirst" :second="brandNameSecond" :gold-first="brandGoldPart === 'first'" :space-between="brandNameSeparator === 'space'" />. All rights reserved.
        </p>
      </div>
    </div>

    <!-- Right Section: Register Form -->
    <div class="w-full lg:w-1/2 flex min-h-[100dvh] flex-col items-center justify-center p-4 xs:p-6 sm:p-10 lg:p-16 relative z-10 bg-theme-surface lg:bg-transparent shadow-[-20px_0_50px_rgba(0,0,0,0.5)] lg:shadow-none border-l border-theme-border py-10 lg:py-16">
      
      <!-- Mobile Logo -->
      <div class="mb-6 lg:hidden flex justify-center w-full">
        <a href="/" class="text-xl font-display font-bold text-theme-text tracking-widest uppercase flex flex-col items-center gap-1 w-fit">
          <img :src="'/images/logo-icon.png'" :alt="`${brandName} logo`" class="h-10 w-10 object-contain rounded-full" />
          <BrandWordmark :first="brandNameFirst" :second="brandNameSecond" :gold-first="brandGoldPart === 'first'" :space-between="brandNameSeparator === 'space'" />
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
            <div class="relative">
              <input 
                v-model="password" 
                id="reg-password"
                class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 pr-12 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none" 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="Minimum 8 characters" 
                autocomplete="new-password"
              />
              <button 
                type="button" 
                @click="showPassword = !showPassword" 
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                :title="showPassword ? 'Hide password' : 'Show password'"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-theme-muted hover:text-gold focus:text-gold active:text-gold transition-colors p-2.5 rounded-md flex items-center justify-center touch-manipulation focus:outline-none">
                <!-- Eye Open -->
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <!-- Eye Closed -->
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>
            <!-- Password Strength Meter -->
            <PasswordStrengthMeter :password="password" />
            <p v-if="errors.password" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.password }}</p>
          </div>

          <!-- Confirm Password -->
          <div class="relative group">
            <label for="reg-password-confirm" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Confirm Password</label>
            <div class="relative">
              <input 
                v-model="passwordConfirmation" 
                id="reg-password-confirm"
                class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 pr-12 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none" 
                :type="showConfirmPassword ? 'text' : 'password'" 
                placeholder="Re-enter your password" 
                autocomplete="new-password"
              />
              <button 
                type="button" 
                @click="showConfirmPassword = !showConfirmPassword" 
                :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                :title="showConfirmPassword ? 'Hide password' : 'Show password'"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-theme-muted hover:text-gold focus:text-gold active:text-gold transition-colors p-2.5 rounded-md flex items-center justify-center touch-manipulation focus:outline-none">
                <!-- Eye Open -->
                <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <!-- Eye Closed -->
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>
            <!-- Live match indicator -->
            <div v-if="passwordConfirmation" class="mt-1 flex items-center gap-1.5 text-xs font-medium">
              <span v-if="password === passwordConfirmation" class="text-emerald-400 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Passwords match
              </span>
              <span v-else class="text-red-400 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Passwords do not match
              </span>
            </div>
            <p v-if="errors.passwordConfirmation" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.passwordConfirmation }}</p>
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

        <SocialAuthButtons label="or sign up with" button-text="Sign up with Google" />

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
import { useBrandIdentity } from '@/shared/composables/useBrandIdentity';
import BrandWordmark from '@/shared/ui/BrandWordmark.vue';
import PasswordStrengthMeter from '@/shared/ui/PasswordStrengthMeter.vue';
import { RouterLink, useRouter } from 'vue-router';
import { object, string } from 'yup';
import { useAuth } from '../composables/useAuth';
import SocialAuthButtons from '../components/SocialAuthButtons.vue';

const { brandName, brandNameFirst, brandNameSecond, brandNameSeparator, brandGoldPart } = useBrandIdentity();

const router = useRouter();
const { register, redirectAfterLogin } = useAuth();

const name = ref('');
const phone = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const loading = ref(false);
const errors = reactive({
  name: '',
  phone: '',
  email: '',
  password: '',
  passwordConfirmation: '',
});
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
  errors.name = '';
  errors.phone = '';
  errors.email = '';
  errors.password = '';
  errors.passwordConfirmation = '';

  if (password.value !== passwordConfirmation.value) {
    errors.passwordConfirmation = 'Passwords do not match';
    loading.value = false;
    return;
  }

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