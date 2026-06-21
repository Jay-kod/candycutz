<template>
  <BarberLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Profile</p>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-ivory">
            Barber <span class="text-gold">Profile</span>
          </h1>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <!-- Profile Form -->
        <form
          class="rounded-2xl border border-white/[0.06] bg-charcoal/80 backdrop-blur-sm overflow-hidden"
          @submit.prevent="submit"
        >
          <div class="flex items-center gap-3 border-b border-white/[0.06] px-6 py-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/10">
              <UserIcon class="h-5 w-5 text-gold" />
            </div>
            <h2 class="font-display text-lg text-ivory">Personal Details</h2>
          </div>

          <div class="p-6 space-y-5">
            <label class="block">
              <span class="text-xs uppercase tracking-wider text-ivory/40 font-semibold">Name</span>
              <input
                v-model="form.name"
                class="mt-1.5 w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-3 text-sm text-ivory placeholder:text-ivory/20 outline-none transition-colors focus:border-gold/30 focus:bg-white/[0.06]"
                placeholder="Your full name"
              />
            </label>
            <label class="block">
              <span class="text-xs uppercase tracking-wider text-ivory/40 font-semibold">Phone</span>
              <input
                v-model="form.phone"
                class="mt-1.5 w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-3 text-sm text-ivory placeholder:text-ivory/20 outline-none transition-colors focus:border-gold/30 focus:bg-white/[0.06]"
                placeholder="Phone number"
              />
            </label>
            <label class="block">
              <span class="text-xs uppercase tracking-wider text-ivory/40 font-semibold">Bio</span>
              <textarea
                v-model="form.bio"
                rows="4"
                class="mt-1.5 w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-3 text-sm text-ivory placeholder:text-ivory/20 outline-none transition-colors focus:border-gold/30 focus:bg-white/[0.06] resize-none"
                placeholder="Tell clients about yourself..."
              ></textarea>
            </label>
            <label class="block">
              <span class="text-xs uppercase tracking-wider text-ivory/40 font-semibold">Instagram</span>
              <input
                v-model="form.instagram_url"
                class="mt-1.5 w-full rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-3 text-sm text-ivory placeholder:text-ivory/20 outline-none transition-colors focus:border-gold/30 focus:bg-white/[0.06]"
                placeholder="https://instagram.com/..."
              />
            </label>

            <div class="pt-2 flex items-center gap-3">
              <button
                type="submit"
                class="rounded-xl bg-gradient-to-r from-gold to-gold-dark px-6 py-3 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(212,175,55,0.2)] hover:shadow-[0_0_30px_rgba(212,175,55,0.35)] transition-all"
              >
                Save Profile
              </button>
              <p v-if="status" class="text-xs text-emerald-400 font-medium">{{ status }}</p>
            </div>
          </div>
        </form>

        <!-- Specialties Card -->
        <div class="rounded-2xl border border-white/[0.06] bg-charcoal/80 backdrop-blur-sm overflow-hidden h-fit">
          <div class="flex items-center gap-3 border-b border-white/[0.06] px-6 py-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-400/10">
              <SparklesIcon class="h-5 w-5 text-emerald-400" />
            </div>
            <h2 class="font-display text-lg text-ivory">Specialties</h2>
          </div>

          <div class="p-6">
            <div class="flex flex-wrap gap-2" v-if="form.specialties && form.specialties.length > 0">
              <span
                v-for="item in form.specialties"
                :key="item"
                class="inline-flex items-center rounded-full bg-gold/10 border border-gold/15 px-3.5 py-1.5 text-xs font-semibold text-gold"
              >
                {{ item }}
              </span>
            </div>
            <div v-else class="py-8 text-center">
              <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white/[0.04]">
                <SparklesIcon class="h-6 w-6 text-ivory/20" />
              </div>
              <p class="mt-3 text-sm text-ivory/30">No specialties added yet.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </BarberLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { UserIcon, SparklesIcon } from '@heroicons/vue/24/outline';

const form = reactive({ name: '', phone: '', bio: '', instagram_url: '', specialties: [] });
const status = ref('');

async function submit() {
  try {
    const response = await barberApi.updateProfile(form);
    status.value = response.data.message || 'Profile saved!';
    setTimeout(() => { status.value = ''; }, 3000);
  } catch (e) {
    status.value = '';
  }
}

onMounted(async () => {
  try {
    const response = await barberApi.profile();
    const profile = response.data.data;
    form.name = profile.name;
    form.phone = profile.phone || '';
    form.bio = profile.bio || '';
    form.instagram_url = profile.instagram_url || '';
    form.specialties = profile.specialties || [];
  } catch (e) {
    // API may not have data yet
  }
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>