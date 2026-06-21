<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Customer Dashboard</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
              Your <span class="text-gold">Profile</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
              Manage your personal information and view your recent activity.
            </p>
          </div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        <form class="rounded-2xl border border-theme-border bg-theme-surface/80 p-8 backdrop-blur-sm" @submit.prevent="submit">
          <h2 class="font-display text-2xl text-theme-text">Personal Info</h2>
          <div class="mt-6 space-y-5">
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Name</label>
              <input v-model="form.name" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50" />
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Phone</label>
              <input v-model="form.phone" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50" />
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Avatar</label>
              <input type="file" @change="handleFile" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-sm text-theme-text file:mr-4 file:rounded-full file:border-0 file:bg-gold/10 file:px-4 file:py-2 file:text-xs file:font-bold file:text-gold hover:file:bg-gold/20 outline-none transition-colors" />
            </div>
          </div>
          <button class="mt-8 w-full rounded-xl bg-gradient-to-r from-gold to-gold-dark py-4 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(212,175,55,0.25)] transition-all hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] hover:scale-[1.02]">
            Save profile
          </button>
          <p v-if="status" class="mt-4 text-center text-sm font-medium text-emerald-400 bg-emerald-400/10 py-2 rounded-lg">{{ status }}</p>
        </form>

        <div class="rounded-2xl border border-theme-border bg-theme-surface/80 p-8 backdrop-blur-sm">
          <h2 class="font-display text-2xl text-theme-text">Recent <span class="text-gold">Activity</span></h2>
          <div class="mt-6 space-y-4">
            <article v-for="booking in history.latest_bookings || []" :key="booking.id" class="rounded-xl border border-theme-border bg-theme-bg/50 p-5 transition-all hover:border-gold/30 hover:bg-theme-surface/50">
              <div class="flex items-start justify-between">
                <div>
                  <p class="font-display text-lg text-theme-text">{{ booking.service?.name }}</p>
                  <p class="mt-1 text-sm text-ivory/50">{{ booking.appointment_date }} at {{ booking.appointment_time }}</p>
                </div>
              </div>
            </article>
            <div v-if="(history.latest_bookings || []).length === 0" class="rounded-xl border border-dashed border-theme-border p-8 text-center">
              <p class="text-sm text-ivory/40">No recent activity.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { customerApi } from '../api/customer.api';
import { useToast } from 'vue-toastification';

const toast = useToast();
const form = reactive({ name: '', phone: '', avatar: null });
const status = ref('');
const history = ref({});

function handleFile(event) {
  form.avatar = event.target.files[0] || null;
}

async function submit() {
  const payload = new FormData();
  payload.append('name', form.name);
  payload.append('phone', form.phone || '');

  if (form.avatar) {
    payload.append('avatar', form.avatar);
  }

  try {
    const response = await customerApi.updateProfile(payload);
    status.value = response.data.message;
    toast.success('Profile updated');
  } catch (error) {
    toast.error('Failed to update profile');
  }
}

onMounted(async () => {
  const response = await customerApi.profile();
  const profile = response.data.data.profile.data ?? response.data.data.profile;
  form.name = profile.name;
  form.phone = profile.phone || '';
  history.value = response.data.data.history;
});
</script>