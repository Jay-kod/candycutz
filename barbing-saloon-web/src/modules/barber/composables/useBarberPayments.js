import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useBarberPayments() {
  const toast = useToast();
  const router = useRouter();

  const loading = ref(true);
  const appointments = ref([]);
  const search = ref('');

  const showAccountModal = ref(false);
  const loadingAccount = ref(false);
  const savingAccount = ref(false);
  const accountForm = ref({
    bank_name: '',
    account_name: '',
    account_number: '',
  });

  let pollingInterval = null;

  const paymentsList = computed(() => {
    const list = appointments.value || [];
    let filtered = list.filter((a) =>
      Boolean(a.receipt_image || a.payment_status || a.status === 'pending')
    );

    if (search.value.trim()) {
      const q = search.value.trim().toLowerCase();
      filtered = filtered.filter((a) => {
        const name = (a.customer?.name || a.customer_name || a.client_name || '').toLowerCase();
        const service = (a.service?.name || a.service_name || '').toLowerCase();
        return name.includes(q) || service.includes(q);
      });
    }

    return filtered;
  });

  const pendingCount = computed(() => {
    return paymentsList.value.filter((p) => {
      const st = (p.payment_status || p.status || '').toLowerCase();
      return st === 'awaiting_verification' || st === 'pending';
    }).length;
  });

  async function loadData(silent = false) {
    if (!silent) loading.value = true;
    try {
      const response = await api.get('/barber/appointments', {
        params: { _t: Date.now() },
      });
      const data = response.data?.data || [];
      appointments.value = Array.isArray(data) ? data : (data.items || []);
    } catch {
      if (!silent) {
        toast.error('Failed to load pending payments');
      }
    } finally {
      if (!silent) loading.value = false;
    }
  }

  function viewReceipt(booking) {
    router.push(`/barber/payments/${booking.id}/receipt`);
  }

  async function openAccountModal() {
    showAccountModal.value = true;
    loadingAccount.value = true;
    try {
      const res = await api.get('/barber/account');
      const barber = res.data?.data?.barber || {};
      accountForm.value.bank_name = barber.bank_name || '';
      accountForm.value.account_name = barber.account_name || '';
      accountForm.value.account_number = barber.account_number || '';
    } catch {
      toast.error('Failed to load account details');
    } finally {
      loadingAccount.value = false;
    }
  }

  async function saveAccount() {
    savingAccount.value = true;
    try {
      await api.patch('/barber/account', {
        bank_name: accountForm.value.bank_name,
        account_name: accountForm.value.account_name,
        account_number: accountForm.value.account_number,
      });
      toast.success('Account details updated successfully');
      showAccountModal.value = false;
    } catch (error) {
      const msg = error.response?.data?.message || 'Failed to update account details';
      toast.error(msg);
    } finally {
      savingAccount.value = false;
    }
  }

  onMounted(() => {
    loadData();
    pollingInterval = setInterval(() => {
      loadData(true);
    }, 15000);
  });

  onUnmounted(() => {
    if (pollingInterval) {
      clearInterval(pollingInterval);
    }
  });

  return {
    loading,
    search,
    paymentsList,
    pendingCount,
    showAccountModal,
    loadingAccount,
    savingAccount,
    accountForm,
    loadData,
    viewReceipt,
    openAccountModal,
    saveAccount,
  };
}
