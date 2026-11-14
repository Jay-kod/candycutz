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
  const currentTab = ref('all');

  const bankDetails = ref({
    bank_name: 'Zenith Bank',
    account_name: 'Candy Cutz Luxury Grooming',
    account_number: '1012345678',
    instructions: 'Please include your Booking Reference (e.g. CC-1234) in your transfer remarks to speed up instant confirmation.',
    is_custom: false,
  });

  const showAccountModal = ref(false);
  const loadingAccount = ref(false);
  const accountForm = ref({
    bank_name: 'Zenith Bank',
    account_name: 'Candy Cutz Luxury Grooming',
    account_number: '1012345678',
  });

  let pollingInterval = null;

  // Filter tabs for the barber
  const filterTabs = computed(() => {
    const list = appointments.value || [];
    const paid = list.filter((a) => a.payment_status === 'paid' || a.deposit_paid === true || a.status === 'completed');
    const awaiting = list.filter((a) => a.payment_status === 'awaiting_verification' || Boolean(a.receipt_image || a.receipt_url));
    const pending = list.filter((a) => (a.payment_status === 'pending' || !a.deposit_paid) && a.status === 'pending');

    return [
      { id: 'all', label: 'All Services & Payments', count: list.length },
      { id: 'paid', label: 'Paid Services', count: paid.length },
      { id: 'awaiting_verification', label: 'Awaiting Verification', count: awaiting.length },
      { id: 'pending', label: 'Pending Payment', count: pending.length },
    ];
  });

  const paymentsList = computed(() => {
    const list = appointments.value || [];
    let filtered = list;

    if (currentTab.value === 'paid') {
      filtered = list.filter((a) => a.payment_status === 'paid' || a.deposit_paid === true || a.status === 'completed');
    } else if (currentTab.value === 'awaiting_verification') {
      filtered = list.filter((a) => a.payment_status === 'awaiting_verification' || Boolean(a.receipt_image || a.receipt_url));
    } else if (currentTab.value === 'pending') {
      filtered = list.filter((a) => (a.payment_status === 'pending' || !a.deposit_paid) && a.status === 'pending');
    }

    if (search.value.trim()) {
      const q = search.value.trim().toLowerCase();
      filtered = filtered.filter((a) => {
        const name = (a.customer?.name || a.customer_name || a.client_name || '').toLowerCase();
        const service = (a.service?.name || a.service_name || '').toLowerCase();
        const ref = (a.booking_reference || a.transaction_ref || '').toLowerCase();
        return name.includes(q) || service.includes(q) || ref.includes(q);
      });
    }

    return filtered;
  });

  const totalEarnings = computed(() => {
    const list = appointments.value || [];
    return list
      .filter((a) => a.payment_status === 'paid' || a.deposit_paid === true || a.status === 'completed')
      .reduce((sum, a) => sum + Number(a.grand_total || a.total_price || a.service?.price || a.price || 0), 0);
  });

  const paidCount = computed(() => {
    const list = appointments.value || [];
    return list.filter((a) => a.payment_status === 'paid' || a.deposit_paid === true || a.status === 'completed').length;
  });

  const awaitingCount = computed(() => {
    const list = appointments.value || [];
    return list.filter((a) => a.payment_status === 'awaiting_verification' || Boolean(a.receipt_image || a.receipt_url)).length;
  });

  const pendingCount = computed(() => {
    const list = appointments.value || [];
    return list.filter((a) => (a.payment_status === 'pending' || !a.deposit_paid) && a.status === 'pending').length;
  });

  async function loadData(silent = false) {
    if (!silent) loading.value = true;
    try {
      const [apptsRes, bankRes] = await Promise.allSettled([
        api.get('/barber/appointments', { params: { per_page: 100, _t: Date.now() } }),
        api.get('/barber/bank-details'),
      ]);

      if (apptsRes.status === 'fulfilled' && apptsRes.value.data) {
        const d = apptsRes.value.data?.data || apptsRes.value.data || [];
        appointments.value = Array.isArray(d) ? d : (d.items || []);
      }

      let salonBank = {
        bank_name: 'Zenith Bank',
        account_name: 'Candy Cutz Luxury Grooming',
        account_number: '1012345678',
        instructions: 'Please include your Booking Reference (e.g. CC-1234) in your transfer remarks to speed up instant confirmation.',
        is_custom: false,
      };

      if (bankRes.status === 'fulfilled' && bankRes.value.data) {
        const b = bankRes.value.data?.data || bankRes.value.data || {};
        if (b.bank_name || b.account_number) {
          salonBank = {
            bank_name: b.bank_name || 'Zenith Bank',
            account_name: b.account_name || 'Candy Cutz Luxury Grooming',
            account_number: b.account_number || '1012345678',
            instructions: b.instructions || salonBank.instructions,
            is_custom: false,
          };
        }
      } else {
        // Fallback to public settings
        try {
          const setRes = await api.get('/settings');
          const s = setRes.data?.data || setRes.data || {};
          if (s.bank_name || s.manual_transfer_bank) {
            salonBank = {
              bank_name: s.bank_name || s.manual_transfer_bank || 'Zenith Bank',
              account_name: s.account_name || s.manual_transfer_account_name || 'Candy Cutz Luxury Grooming',
              account_number: s.account_number || s.manual_transfer_account_number || '1012345678',
              instructions: s.payment_instructions || salonBank.instructions,
              is_custom: false,
            };
          }
        } catch {
          // ignore fallback error
        }
      }

      bankDetails.value = { ...salonBank };
      accountForm.value = {
        bank_name: salonBank.bank_name,
        account_name: salonBank.account_name,
        account_number: salonBank.account_number,
      };
    } catch (err) {
      if (!silent) toast.error('Failed to load payments data');
    } finally {
      if (!silent) loading.value = false;
    }
  }

  function viewReceipt(booking) {
    router.push(`/barber/payments/${booking.id}/receipt`);
  }

  function openAccountModal() {
    accountForm.value = {
      bank_name: bankDetails.value.bank_name,
      account_name: bankDetails.value.account_name,
      account_number: bankDetails.value.account_number,
    };
    showAccountModal.value = true;
  }

  async function collectPaymentAtVenue(booking) {
    try {
      await api.post(`/barber/appointments/${booking.id}/collect-payment`);
      toast.success(`Payment of ₦${Number(booking.grand_total || booking.total_price || booking.service?.price || booking.price || 0).toLocaleString()} recorded successfully!`);
      await loadData(true);
    } catch (err) {
      const msg = err.response?.data?.message || 'Failed to record venue payment';
      toast.error(msg);
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
    currentTab,
    filterTabs,
    paymentsList,
    bankDetails,
    totalEarnings,
    paidCount,
    awaitingCount,
    pendingCount,
    showAccountModal,
    loadingAccount,
    accountForm,
    loadData,
    viewReceipt,
    openAccountModal,
    collectPaymentAtVenue,
  };
}
