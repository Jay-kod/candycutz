import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function usePaymentCheckout() {
  const route = useRoute();
  const router = useRouter();
  const toast = useToast();

  const loading = ref(true);
  const submitting = ref(false);
  const success = ref(false);
  const selectedChannel = ref('bank_transfer');

  const gatewayTitle = ref('CandyCutz • Secure Payment');
  const noticeText = ref('Before transferring, make sure the account name shown by your bank matches the one provided below.');
  const enabledChannels = ref(['bank_transfer', 'ussd', 'bank_account', 'nqr', 'card']);
  const ussdCodeTemplate = ref('*737*2*{amount}*{account_number}#');

  const bankDetails = ref({
    bank_name: 'Guaranty Trust Bank (GTB)',
    account_name: 'Candy Cutz Saloon',
    account_number: '0123456789',
    currency: 'NGN'
  });

  const barbers = ref([]);
  const services = ref([]);
  const selectedBarberId = ref(null);
  const appointment = ref(null);
  const paymentResult = ref(null);

  const rawAmount = ref(30000);
  const reference = ref('');
  const transactionCode = ref('');

  // Countdown timer initialized to 119 minutes 21 seconds (7161 seconds)
  const secondsRemaining = ref(7161);
  let timerInterval = null;

  const formattedTimeRemaining = computed(() => {
    const mins = Math.floor(secondsRemaining.value / 60);
    const secs = secondsRemaining.value % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  });

  const formattedAmount = computed(() => {
    return new Intl.NumberFormat('en-NG', {
      style: 'currency',
      currency: 'NGN',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(rawAmount.value);
  });

  const selectedBarber = computed(() => {
    return barbers.value.find((b) => b.id === selectedBarberId.value) || barbers.value[0] || null;
  });

  const generateDefaultReferences = () => {
    const now = new Date();
    const dateStr = now.toISOString().slice(0, 10).replace(/-/g, '');
    const randPart = Math.random().toString(36).substring(2, 8).toUpperCase();
    reference.value = `FLYX-${dateStr}-${randPart}`;
    transactionCode.value = `594${dateStr}${Math.floor(10000000000 + Math.random() * 90000000000)}`;
  };

  const startTimer = () => {
    clearInterval(timerInterval);
    timerInterval = setInterval(() => {
      if (secondsRemaining.value > 0) {
        secondsRemaining.value -= 1;
      } else {
        clearInterval(timerInterval);
      }
    }, 1000);
  };

  const fetchCheckoutData = async () => {
    loading.value = true;
    generateDefaultReferences();

    const apptId = route?.params?.appointmentId || route?.query?.appointment_id;
    const refParam = route?.query?.reference;

    try {
      const response = await client.get('/payments/checkout-data', {
        params: { appointment_id: apptId, reference: refParam }
      });

      const data = response.data?.data || response.data || {};
      
      if (data.bank_details) {
        bankDetails.value = {
          bank_name: data.bank_details.bank_name || 'Guaranty Trust Bank (GTB)',
          account_name: data.bank_details.account_name || 'Candy Cutz Saloon',
          account_number: data.bank_details.account_number || '0123456789',
          currency: data.bank_details.currency || 'NGN'
        };
      }

      if (data.payment_settings) {
        if (data.payment_settings.gateway_title) {
          gatewayTitle.value = data.payment_settings.gateway_title;
        }
        if (data.payment_settings.notice_text) {
          noticeText.value = data.payment_settings.notice_text;
        }
        if (Array.isArray(data.payment_settings.enabled_channels)) {
          enabledChannels.value = data.payment_settings.enabled_channels;
          if (!enabledChannels.value.includes(selectedChannel.value)) {
            selectedChannel.value = enabledChannels.value[0] || 'bank_transfer';
          }
        }
        if (data.payment_settings.expiry_minutes) {
          secondsRemaining.value = Number(data.payment_settings.expiry_minutes) * 60;
        }
        if (data.payment_settings.ussd_code_template) {
          ussdCodeTemplate.value = data.payment_settings.ussd_code_template;
        }
      }

      barbers.value = data.barbers || [];
      services.value = data.services || [];

      if (data.appointment) {
        appointment.value = data.appointment;
        rawAmount.value = Number(data.appointment.amount || 30000);
        if (data.appointment.booking_reference) {
          reference.value = data.appointment.booking_reference;
        }
        if (data.appointment.barber?.id) {
          selectedBarberId.value = data.appointment.barber.id;
        }
      } else if (barbers.value.length > 0) {
        selectedBarberId.value = barbers.value[0].id;
      }
    } catch (err) {
      console.warn('Failed to fetch checkout data, using defaults:', err);
    } finally {
      loading.value = false;
      startTimer();
    }
  };

  const copyToClipboard = async (text, label = 'Information') => {
    if (!text) return;
    try {
      await navigator.clipboard.writeText(String(text));
      toast.success(`${label} copied to clipboard!`);
    } catch {
      toast.info(`Copied: ${text}`);
    }
  };

  const submitPayment = async () => {
    submitting.value = true;
    try {
      const payload = {
        appointment_id: appointment.value?.id || null,
        barber_id: selectedBarberId.value || null,
        service_id: appointment.value?.service?.id || (services.value[0]?.id || 1),
        amount: rawAmount.value,
        payment_method: selectedChannel.value,
        reference: reference.value,
      };

      const response = await client.post('/payments/confirm-transfer', payload);
      const data = response.data?.data || response.data || {};

      paymentResult.value = data;
      success.value = true;
      toast.success('Payment recorded and confirmed in database!');
    } catch (err) {
      console.error('Payment submission failed:', err);
      toast.error(err.response?.data?.message || 'Failed to submit payment confirmation.');
    } finally {
      submitting.value = false;
    }
  };

  const cancelTransaction = () => {
    if (window.history.length > 1) {
      router.back();
    } else {
      router.push('/customer/dashboard/bookings');
    }
  };

  onMounted(() => {
    fetchCheckoutData();
  });

  onUnmounted(() => {
    clearInterval(timerInterval);
  });

  return {
    loading,
    submitting,
    success,
    selectedChannel,
    gatewayTitle,
    noticeText,
    enabledChannels,
    ussdCodeTemplate,
    bankDetails,
    barbers,
    services,
    selectedBarberId,
    selectedBarber,
    appointment,
    paymentResult,
    rawAmount,
    formattedAmount,
    reference,
    transactionCode,
    secondsRemaining,
    formattedTimeRemaining,
    copyToClipboard,
    submitPayment,
    cancelTransaction,
    fetchCheckoutData,
  };
}
