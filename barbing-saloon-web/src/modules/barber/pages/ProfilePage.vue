<template>
  <BarberLayout>
    <section class="max-w-7xl mx-auto pb-16 pt-4 px-4 sm:px-6 lg:px-8 space-y-6">
      <ProfileSkeleton v-if="loading" />

      <template v-else>
        <ProfileHeader
          :saving="saving"
          @save="saveProfile"
        />

        <form @submit.prevent="saveProfile" class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr] items-start">
          <ProfilePersonalCard
            :form="form"
            @avatar-selected="onAvatarSelected"
            @cover-selected="onCoverSelected"
          />

          <div class="lg:sticky lg:top-8 space-y-6">
            <EmailVerificationCard :email="form.email" />

            <BarberSpecialtiesCard
              :specialties="form.specialties"
              :all-styles="allStyles"
              @toggle="toggleSpecialty"
              @add="addSpecialty"
            />

            <UserProfileCookiePreferencesCard />
          </div>
        </form>
      </template>
    </section>
  </BarberLayout>
</template>

<script setup>
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import EmailVerificationCard from '@/shared/components/EmailVerificationCard.vue';
import UserProfileCookiePreferencesCard from '@/shared/components/UserProfileCookiePreferencesCard.vue';
import { useBarberProfile } from '../composables/useBarberProfile';
import ProfileHeader from '../components/profile/ProfileHeader.vue';
import ProfilePersonalCard from '../components/profile/ProfilePersonalCard.vue';
import BarberSpecialtiesCard from '../components/profile/BarberSpecialtiesCard.vue';
import ProfileSkeleton from '../components/profile/ProfileSkeleton.vue';

const {
  loading,
  saving,
  form,
  allStyles,
  toggleSpecialty,
  addSpecialty,
  onAvatarSelected,
  onCoverSelected,
  saveProfile,
} = useBarberProfile();
</script>
