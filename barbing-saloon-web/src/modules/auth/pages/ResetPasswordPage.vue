<template>
  <div class="min-h-[100dvh] flex bg-theme-bg overflow-hidden relative text-theme-text">
    <div class="hidden lg:flex lg:w-1/2 relative flex-col items-center justify-between p-16 overflow-hidden text-center">
      <div class="absolute inset-0 bg-theme-bg z-0"></div>
      <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-gold/10 via-transparent to-transparent z-0"></div>
      <div class="absolute bottom-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_bottom_right,_var(--tw-gradient-stops))] from-gold/5 via-transparent to-transparent z-0"></div>
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1px] h-full bg-gradient-to-b from-transparent via-theme-border/20 to-transparent z-0"></div>

      <div class="relative z-10">
        <a href="/" class="text-2xl font-display font-bold text-theme-text tracking-widest uppercase flex flex-col items-center gap-2">
          <img :src="'/images/logo-icon.png'" :alt="`${brandName} logo`" class="h-36 w-36 object-contain rounded-full" />
          <BrandWordmark :first="brandNameFirst" :second="brandNameSecond" :gold-first="brandGoldPart === 'first'" :space-between="brandNameSeparator === 'space'" />
        </a>
      </div>

      <div class="relative z-10 max-w-lg">
        <div class="w-12 h-1 bg-gold mb-8 mx-auto"></div>
        <h2 class="font-display text-5xl text-theme-text leading-tight mb-6">
          Fresh cut,<br />fresh password.
        </h2>
        <p class="text-lg text-theme-muted font-light leading-relaxed">
          Choose a new password to secure your {{ brandName }} account.
        </p>
      </div>

      <div class="relative z-10">
        <p class="text-xs uppercase tracking-[0.3em] text-theme-muted font-semibold">
          &copy; {{ new Date().getFullYear() }} <BrandWordmark :first="brandNameFirst" :second="brandNameSecond" :gold-first="brandGoldPart === 'first'" :space-between="brandNameSeparator === 'space'" />. All rights reserved.
        </p>
      </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24 relative z-10 bg-theme-surface lg:bg-transparent shadow-[-20px_0_50px_rgba(0,0,0,0.5)] lg:shadow-none border-l border-theme-border overflow-y-auto">
      <div class="w-full max-w-sm">
        <div class="mb-8">
          <div class="text-center">
            <h1 class="font-display font-bold text-3xl sm:text-4xl text-gold mb-4">Reset Password</h1>
          </div>
          <p class="text-theme-muted text-xs sm:text-sm text-center">Set a new password for your account.</p>
        </div>

        <form class="space-y-4" @submit.prevent="submitForm">
          <div v-if="generalError" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-xs text-red-400 font-medium flex items-center gap-2">
            <span>{{ generalError }}</span>
          </div>

          <div v-if="message" class="bg-green-500/10 border border-green-500/30 rounded-lg p-3 text-xs text-green-400 font-medium flex items-center gap-2">
            <span>{{ message }}</span>
          </div>

          <div class="relative group">
            <label for="rp-email" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Email Address</label>
            <input
              v-model="email"
              id="rp-email"
              class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none"
              placeholder="you@example.com"
              type="email"
              required
            />
            <p v-if="errors.email" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.email }}</p>
          </div>

          <div class="relative group">
            <label for="rp-code" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">6-Digit Reset Code (OTP)</label>
            <input
              v-model="code"
              id="rp-code"
              class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 text-theme-text text-sm font-mono tracking-widest placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none"
              placeholder="123456"
              maxlength="64"
              required
            />
            <p v-if="errors.code" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.code }}</p>
          </div>

          <div class="relative group">
            <label for="rp-password" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">New Password</label>
            <div class="relative">
              <input
                v-model="password"
                id="rp-password"
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
            <PasswordStrengthMeter :password="password" />
            <p v-if="errors.password" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.password }}</p>
          </div>

          <div class="relative group">
            <label for="rp-password-confirm" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Confirm New Password</label>
            <div class="relative">
              <input
                v-model="passwordConfirmation"
                id="rp-password-confirm"
                class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 pr-12 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none"
                :type="showConfirmPassword ? 'text' : 'password'"
                placeholder="Re-enter your new password"
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
            <p v-if="errors.password_confirmation" class="mt-1.5 text-xs text-red-400 font-medium">{{ errors.password_confirmation }}</p>
          </div>

          <button
            :disabled="loading"
            class="w-full rounded-lg bg-gold px-4 py-3 text-sm font-bold text-obsidian tracking-wide transition-all duration-300 hover:bg-gold-dark hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <svg v-if="loading" class="animate-spin h-4 w-4 text-obsidian" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            {{ loading ? 'Resetting...' : 'Reset Password' }}
          </button>
        </form>

        <p class="mt-6 text-center text-xs text-theme-muted">
          Remembered your password?
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
import { RouterLink, useRoute } from 'vue-router';
import { object, string } from 'yup';
import { authApi } from '@/shared/api/old_authApi';

const { brandName, brandNameFirst, brandNameSecond, brandNameSeparator, brandGoldPart } = useBrandIdentity();

const route = useRoute();

const email = ref((route.query.email || '').toString());
const code = ref((route.query.code || route.query.token || '').toString());
const password = ref('');
const passwordConfirmation = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const loading = ref(false);
const message = ref('');
const generalError = ref('');
const errors = reactive({
  email: '',
  code: '',
  password: '',
  password_confirmation: '',
});

const schema = object({
  email: string().required('Email address is required').email('Enter a valid email address'),
  code: string().required('6-digit reset code or token is required'),
  password: string().required('Password is required').min(8, 'Password must be at least 8 characters'),
});

async function submitForm() {
  loading.value = true;
  message.value = '';
  generalError.value = '';
  errors.email = '';
  errors.code = '';
  errors.password = '';
  errors.password_confirmation = '';

  if (password.value !== passwordConfirmation.value) {
    errors.password_confirmation = 'Passwords do not match';
    loading.value = false;
    return;
  }

  const cleanEmail = (email.value || '').trim().toLowerCase();
  const cleanCode = (code.value || '').replace(/[\s-]/g, '').trim();

  try {
    await schema.validate({
      email: cleanEmail,
      code: cleanCode,
      password: password.value,
    }, { abortEarly: false });

    const response = await authApi.resetPassword({
      email: cleanEmail,
      token: cleanCode,
      code: cleanCode,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });
    message.value = response.data.message || 'Your password has been reset. You can now sign in.';
  } catch (error) {
    if (error.inner) {
      error.inner.forEach((item) => {
        errors[item.path] = item.message;
      });
    } else {
      generalError.value = error?.response?.data?.message || error?.response?.data?.error || 'Something went wrong. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>