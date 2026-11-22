<template>
  <component :is="layoutComponent">
    <div class="max-w-7xl mx-auto space-y-8 animate-fade-in pb-16 pt-2 px-4 sm:px-6 lg:px-8">
      
      <!-- Top Hero Banner with Navigation Tabs -->
      <BankDetailsHero
        v-model:currentTab="currentTab"
        :loading="loading"
        :saving="saving"
        @refresh="loadBankDetails"
        @save="saveBankDetails"
      />

      <!-- Tab 1: Bank Account & Payout -->
      <div v-if="currentTab === 'account'" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <div class="lg:col-span-5">
          <BankCardPreview
            :bank-name="form.bank_name"
            :account-name="form.account_name"
            :account-number="form.account_number"
            @copy="copyToClipboard"
          />
        </div>
        <div class="lg:col-span-7">
          <BankConfigForm
            :form="form"
            :saving="saving"
            @save="saveBankDetails"
            @reset-defaults="resetToDefault"
          />
        </div>
      </div>

      <!-- Tab 2: Payment Page & Gateway Controls -->
      <div v-else-if="currentTab === 'gateway'" class="space-y-6">
        <PaymentGatewayControlsForm
          :form="form"
          :saving="saving"
          @save="saveBankDetails"
        />
      </div>

      <!-- Tab 3: Live Transactions & Barber Assignment Management -->
      <div v-else-if="currentTab === 'transactions'">
        <PaymentTransactionsManager />
      </div>

    </div>
  </component>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { client } from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

import BankDetailsHero from '../components/bank-details/BankDetailsHero.vue';
import BankCardPreview from '../components/bank-details/BankCardPreview.vue';
import BankConfigForm from '../components/bank-details/BankConfigForm.vue';
import PaymentGatewayControlsForm from '../components/bank-details/PaymentGatewayControlsForm.vue';
import PaymentTransactionsManager from '../components/bank-details/PaymentTransactionsManager.vue';

const route = useRoute();
const toast = useToast();

const layoutComponent = computed(() => {
  return route.path.startsWith('/superadmin') ? SuperAdminLayout : AdminLayout;
});

const currentTab = ref(route.query.tab || 'account');
const loading = ref(false);
const saving = ref(false);

const form = reactive({
  bank_name: '',
  account_name: '',
  account_number: '',
  instructions: '',
  payment_gateway_title: 'MTN FlyX Registration • CandyCutz',
  payment_expiry_minutes: 120,
  payment_notice_text: 'Before transferring, make sure the account name shown by your bank matches the one provided below.',
  payment_channels: ['bank_transfer', 'ussd', 'bank_account', 'nqr', 'card'],
  ussd_code_template: '*737*2*{amount}*{account_number}#',
  auto_confirm: true,
});

async function copyToClipboard(text, label = 'Text') {
  if (!text) return;
  try {
    await navigator.clipboard.writeText(String(text));
    toast.success(`${label} copied to clipboard!`);
  } catch {
    toast.error('Failed to copy');
  }
}

async function loadBankDetails() {
  loading.value = true;
  try {
    const res = await client.get('/admin/bank-details');
    const data = res.data?.data || res.data || {};
    form.bank_name = data.bank_name || '';
    form.account_name = data.account_name || '';
    form.account_number = data.account_number || '';
    form.instructions = data.instructions || '';
    if (data.payment_gateway_title) form.payment_gateway_title = data.payment_gateway_title;
    if (data.payment_expiry_minutes) form.payment_expiry_minutes = Number(data.payment_expiry_minutes);
    if (data.payment_notice_text) form.payment_notice_text = data.payment_notice_text;
    if (Array.isArray(data.payment_channels)) form.payment_channels = data.payment_channels;
    if (data.ussd_code_template) form.ussd_code_template = data.ussd_code_template;
    if (data.auto_confirm !== undefined) form.auto_confirm = Boolean(data.auto_confirm);
  } catch (err) {
    console.error('Failed to load bank settings', err);
    toast.error('Failed to load bank details from database');
  } finally {
    loading.value = false;
  }
}

async function saveBankDetails() {
  if (!form.bank_name.trim() || !form.account_name.trim() || !form.account_number.trim()) {
    toast.error('Please complete Bank Name, Account Name, and Account Number.');
    return;
  }

  saving.value = true;
  try {
    await client.post('/admin/bank-details', {
      bank_name: form.bank_name.trim(),
      account_name: form.account_name.trim(),
      account_number: form.account_number.trim(),
      instructions: form.instructions ? form.instructions.trim() : '',
      payment_gateway_title: form.payment_gateway_title.trim(),
      payment_expiry_minutes: form.payment_expiry_minutes,
      payment_notice_text: form.payment_notice_text.trim(),
      payment_channels: form.payment_channels,
      ussd_code_template: form.ussd_code_template.trim(),
      auto_confirm: form.auto_confirm,
    });
    toast.success('Account and checkout controls saved to database successfully!');
    await loadBankDetails();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to save settings';
    toast.error(msg);
  } finally {
    saving.value = false;
  }
}

function resetToDefault() {
  form.bank_name = 'Sterling Bank';
  form.account_name = 'NextGen PG';
  form.account_number = '5261935770';
  form.instructions = 'Please include your Booking Reference in your transfer remarks to speed up instant confirmation.';
}

onMounted(() => {
  loadBankDetails();
});
</script>
