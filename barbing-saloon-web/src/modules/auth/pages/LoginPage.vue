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
          Your style,<br />our expertise.
        </h2>
        <p class="text-lg text-theme-muted font-light leading-relaxed">
          Sign in to book appointments, manage your wishlist, and experience the premium standard of grooming at CandyCutz.
        </p>
      </div>
      
      <div class="relative z-10">
        <p class="text-xs uppercase tracking-[0.3em] text-theme-muted font-semibold">
          &copy; {{ new Date().getFullYear() }} CandyCutz. All rights reserved.
        </p>
      </div>
    </div>

    <!-- Right Section: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24 relative z-10 bg-theme-surface lg:bg-transparent shadow-[-20px_0_50px_rgba(0,0,0,0.5)] lg:shadow-none border-l border-theme-border">
      
      <!-- Mobile Logo -->
      <div class="absolute top-8 left-8 lg:hidden">
        <a href="/" class="text-xl font-display font-bold text-theme-text tracking-widest uppercase flex flex-col gap-1 w-fit">
          <img src="/images/logo-icon.png" alt="CandyCutz Logo" class="h-8 w-8 object-contain rounded-full" />
          <span><span class="text-gold">Candy</span>Cutz</span>
        </a>
      </div>

      <div class="w-full max-w-sm">
        <div class="mb-8">
          <div class="text-center">
            <h1 class="font-display font-bold text-3xl sm:text-4xl text-gold mb-4">Customer Portal</h1>
          </div>
          <p class="text-theme-muted text-xs sm:text-sm mb-4">Please enter your credentials to access your dashboard.</p>
          
          <div class="bg-theme-surface border border-theme-border rounded-lg p-3 text-xs mb-4">
            <p class="text-theme-text font-semibold mb-1">Demo Credentials:</p>
            <p class="text-theme-text"><span class="text-theme-muted">Email:</span> customer@candycutz.com</p>
            <p class="text-theme-text"><span class="text-theme-muted">Password:</span> customer123</p>
          </div>
        </div>

        <form class="space-y-4" @submit.prevent="submitForm">
          <!-- General Error -->
          <div v-if="generalError" class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-sm text-red-400 font-medium flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ generalError }}</span>
          </div>
          
          <!-- Email Input -->
          <div class="relative group">
            <label for="email" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Email Address</label>
            <input 
              v-model="email" 
              id="email"
              class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none" 
              placeholder="you@example.com" 
              type="email" 
            />
            <p v-if="errors.email" class="mt-2 text-xs text-red-400 font-medium">{{ errors.email }}</p>
          </div>
          
          <!-- Password Input -->
          <div class="relative group">
            <label for="password" class="block text-[10px] font-semibold uppercase tracking-wider text-gold mb-1.5">Password</label>
            <div class="relative">
              <input 
                v-model="password" 
                id="password"
                class="w-full bg-theme-surface border border-graphite rounded-lg px-4 py-3 pr-12 text-theme-text text-sm font-medium placeholder-theme-muted focus:border-gold focus:ring-1 focus:ring-gold transition-all duration-300 outline-none" 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="Enter your password" 
              />
              <button 
                type="button" 
                @click="showPassword = !showPassword" 
                class="absolute right-4 top-1/2 -translate-y-1/2 text-neutral hover:text-gold transition-colors p-1">
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
            <p v-if="errors.password" class="mt-2 text-xs text-red-400 font-medium">{{ errors.password }}</p>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between mt-2">
            <label class="flex items-center gap-2 cursor-pointer group">
              <div class="relative flex items-center justify-center w-4 h-4 rounded border border-theme-border bg-theme-bg group-hover:border-gold-dark transition-colors">
                <input type="checkbox" v-model="rememberMe" class="peer sr-only" />
                <svg class="w-3 h-3 text-gold opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <span class="text-xs text-theme-muted group-hover:text-gold-dark transition-colors">Remember me</span>
            </label>
            <RouterLink to="/forgot-password" class="text-xs font-semibold text-gold hover:text-gold-dark transition-colors">
              Forgot password?
            </RouterLink>
          </div>
          
          <div class="space-y-3 mt-6">
            <!-- Submit Button -->
            <button 
              :disabled="loading" 
              class="relative w-full overflow-hidden rounded-lg bg-gold px-4 py-3 text-sm font-bold text-obsidian tracking-wide transition-all duration-300 hover:bg-gold-dark hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-70 disabled:cursor-not-allowed group">
              <span class="relative z-10 flex items-center justify-center gap-2">
                <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-obsidian" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ loading ? 'Authenticating...' : 'Sign In' }}
              </span>
            </button>

            <button 
              type="button"
              @click="demoCustomerLogin"
              :disabled="loading" 
              class="w-full rounded-lg border-2 border-theme-border bg-theme-surface px-4 py-2.5 text-sm font-bold text-theme-text transition-all duration-300 hover:border-gold hover:text-gold disabled:opacity-70 flex items-center justify-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
              Use Customer Demo
            </button>
          </div>
        </form>

        <!-- Register Link -->
        <div class="mt-10 text-center text-sm text-theme-muted">
          Don't have an account yet? 
          <RouterLink to="/customer/register" class="font-bold text-gold hover:text-gold-dark transition-colors ml-1">
            Create an account
          </RouterLink>
        </div>

        <!-- Staff Portals -->
        <div class="mt-8 border-t border-theme-border pt-6 flex flex-col md:flex-row justify-center gap-4 text-xs text-theme-muted">
          <RouterLink to="/barber/login" class="hover:text-gold transition-colors">Barber Portal</RouterLink>
          <span class="hidden md:inline text-theme-border">•</span>
          <RouterLink to="/admin/login" class="hover:text-gold transition-colors">Admin Portal</RouterLink>
        </div>
        
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { object, string } from 'yup';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { login, redirectAfterLogin } = useAuth();

const email = ref('customer@candycutz.com');
const password = ref('customer123');
const showPassword = ref(false);
const rememberMe = ref(false);
const loading = ref(false);
const generalError = ref('');
const errors = reactive({});

const schema = object({
  email: string().required('Email is required').email('Enter a valid email'),
  password: string().required('Password is required'),
});

const demoCustomerLogin = async () => {
  email.value = 'customer@candycutz.com';
  password.value = 'customer123';
  rememberMe.value = true;
  await submitForm();
};

async function submitForm() {
  loading.value = true;
  generalError.value = '';
  errors.email = '';
  errors.password = '';

  try {
    await schema.validate({ email: email.value, password: password.value }, { abortEarly: false });
    await login({ email: email.value, password: password.value });
    await router.push(redirectAfterLogin());
  } catch (error) {
    if (error.inner) {
      error.inner.forEach((item) => {
        errors[item.path] = item.message;
      });
      loading.value = false;
      return;
    }

    generalError.value = 'Credentials not correct, either the password or the email.';
  } finally {
    loading.value = false;
  }
}
</script>