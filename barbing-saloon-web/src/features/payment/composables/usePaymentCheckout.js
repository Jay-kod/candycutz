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
  const paystackPublicKey = ref('');

  const rawAmount = ref(30000);
  const reference = ref('');
  const transactionCode = ref('');

  // Countdown timer initialized to 15 minutes (900 seconds)
  const secondsRemaining = ref(900);
  let timerInterval = null;
  let targetExpiryMs = null;

  const isHoldExpired = computed(() => {
    return secondsRemaining.value <= 0;
  });

  const formattedTimeRemaining = computed(() => {
    if (secondsRemaining.value <= 0) {
      return 'Hold Expired';
    }
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

  const syncTimer = () => {
    if (targetExpiryMs) {
      secondsRemaining.value = Math.max(0, Math.floor((targetExpiryMs - Date.now()) / 1000));
      if (secondsRemaining.value <= 0 && timerInterval) {
        clearInterval(timerInterval);
      }
    }
  };

  const startTimer = () => {
    clearInterval(timerInterval);
    if (appointment.value?.draft_expires_at) {
      targetExpiryMs = new Date(appointment.value.draft_expires_at).getTime();
    } else {
      targetExpiryMs = Date.now() + (secondsRemaining.value * 1000);
    }
    syncTimer();
    timerInterval = setInterval(() => {
      syncTimer();
    }, 1000);
  };

  const handleVisibilityOrFocus = () => {
    if (typeof document !== 'undefined' && !document.hidden) {
      syncTimer();
    }
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

      if (data.paystack) {
        paystackPublicKey.value = data.paystack.public_key || '';
        if (data.paystack.default_gateway === 'paystack') {
          selectedChannel.value = 'card';
        }
      }

      if (data.appointment) {
        appointment.value = data.appointment;
        rawAmount.value = Number(data.appointment.amount || 30000);
        if (data.appointment.booking_reference) {
          reference.value = data.appointment.booking_reference;
        }
        if (data.appointment.barber?.id) {
          selectedBarberId.value = data.appointment.barber.id;
        }
        if (typeof data.appointment.draft_time_remaining_seconds === 'number' && data.appointment.draft_time_remaining_seconds > 0) {
          secondsRemaining.value = data.appointment.draft_time_remaining_seconds;
        } else if (data.appointment.draft_expires_at) {
          const diff = Math.floor((new Date(data.appointment.draft_expires_at).getTime() - Date.now()) / 1000);
          if (diff > 0) {
            secondsRemaining.value = diff;
          }
        }
        if (data.appointment.payment_status === 'paid' || data.appointment.status === 'confirmed') {
          success.value = true;
          paymentResult.value = {
            reference: data.appointment.booking_reference,
            barber: data.appointment.barber,
            service: data.appointment.service,
            status: 'successful'
          };
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

  const loadPaystackScript = () => {
    return new Promise((resolve, reject) => {
      if (typeof window === 'undefined') return reject(new Error('Window unavailable'));
      if (window.PaystackPop) return resolve(window.PaystackPop);

      let settled = false;
      const timeoutId = setTimeout(() => {
        if (!settled) {
          settled = true;
          reject(new Error('Paystack SDK load timed out'));
        }
      }, 15000);

      const handleLoad = () => {
        if (settled) return;
        settled = true;
        clearTimeout(timeoutId);
        if (window.PaystackPop) {
          resolve(window.PaystackPop);
        } else {
          reject(new Error('Paystack SDK loaded without PaystackPop'));
        }
      };

      const handleError = () => {
        if (settled) return;
        settled = true;
        clearTimeout(timeoutId);
        reject(new Error('Failed to load Paystack SDK'));
      };

      const existing = document.getElementById('paystack-inline-js');
      if (existing) {
        existing.addEventListener('load', handleLoad, { once: true });
        existing.addEventListener('error', handleError, { once: true });
        return;
      }

      const script = document.createElement('script');
      script.id = 'paystack-inline-js';
      script.src = 'https://js.paystack.co/v2/inline.js';
      script.async = true;
      script.addEventListener('load', handleLoad, { once: true });
      script.addEventListener('error', handleError, { once: true });
      document.head.appendChild(script);
    });
  };

  const verifyPaymentOnBackend = async (finalRef) => {
    submitting.value = true;
    try {
      const response = await client.get(`/customer/payments/${finalRef}`);
      const data = response.data?.data || response.data || {};

      if (data.status === 'successful' || data.appointment?.payment_status === 'paid') {
        paymentResult.value = data;
        success.value = true;
        toast.success('Payment verified successfully! Your chair is booked.');
      } else {
        toast.warning(`Payment status is ${data.status || 'pending'}. Refreshing...`);
      }
    } catch (err) {
      console.error('Payment verification failed:', err);
      toast.error('Payment verification check failed. Please check your bookings.');
    } finally {
      submitting.value = false;
    }
  };

  const renewingHold = ref(false);

  const renewHold = async () => {
    if (renewingHold.value) return false;
    const apptId = appointment.value?.id || route?.params?.appointmentId || route?.query?.appointment_id;
    if (!apptId) return false;

    renewingHold.value = true;
    try {
      const res = await client.post(`/appointments/drafts/${apptId}/resume`).catch(() => {
        return client.post(`/customer/bookings/${apptId}/resume`);
      });
      const data = res.data?.data || res.data || {};
      if (data.draft_expires_at) {
        appointment.value = { ...(appointment.value || {}), ...data };
        const diff = Math.floor((new Date(data.draft_expires_at).getTime() - Date.now()) / 1000);
        secondsRemaining.value = Math.max(0, diff);
        startTimer();
      } else {
        secondsRemaining.value = 900;
        startTimer();
      }
      toast.success('Hold renewed for 15 minutes! Please complete payment.');
      return true;
    } catch (err) {
      const msg = err.response?.data?.message || 'Slot is no longer available. Please choose a new time.';
      toast.error(msg);
      if (err.response?.status === 409) {
        router.push('/customer/dashboard/services');
      }
      return false;
    } finally {
      renewingHold.value = false;
    }
  };

  const ensureHoldValid = async () => {
    if (!isHoldExpired.value) {
      return true;
    }
    return await renewHold();
  };

  const payWithPaystack = async () => {
    if (submitting.value || success.value) return;
    if (!appointment.value?.id) {
      toast.error('Missing appointment ID for Paystack checkout.');
      return;
    }

    if (isHoldExpired.value) {
      const valid = await ensureHoldValid();
      if (!valid) return;
    }

    submitting.value = true;
    try {
      const res = await client.post('/payments/checkout', {
        appointment_id: appointment.value.id,
        payment_method: 'paystack',
        client: 'web'
      });

      const checkout = res.data?.data?.checkout || res.data?.data || {};
      const accessCode = checkout.access_code;
      const authUrl = checkout.authorization_url;
      const txnRef = checkout.reference || reference.value;

      try {
        await loadPaystackScript();
        const popup = new window.PaystackPop();

        popup.resumeTransaction(accessCode, {
          onSuccess: async (tx) => {
            const finalRef = tx?.reference || txnRef;
            await verifyPaymentOnBackend(finalRef);
          },
          onCancel: () => {
            submitting.value = false;
            toast.info('Paystack checkout closed. You can retry when ready.');
          }
        });
      } catch (inlineErr) {
        console.warn('Paystack inline popup could not open, redirecting to hosted checkout:', inlineErr);
        if (authUrl) {
          window.location.href = authUrl;
        } else {
          throw inlineErr;
        }
      }
    } catch (err) {
      submitting.value = false;
      const msg = err.response?.data?.message || err.message || 'Could not initiate Paystack checkout.';
      toast.error(msg);
    }
  };

  const submitPayment = async () => {
    if (submitting.value || success.value) return;
    if (selectedChannel.value === 'card' || selectedChannel.value === 'paystack') {
      return payWithPaystack();
    }

    if (isHoldExpired.value) {
      const valid = await ensureHoldValid();
      if (!valid) return;
    }

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

  const saveAsDraftAndExit = () => {
    toast.info('Booking saved as draft. You have 15 minutes to complete payment.');
    router.push('/customer/dashboard/bookings');
  };

  const discardDraftAndExit = async () => {
    const apptId = appointment.value?.id || route?.params?.appointmentId || route?.query?.appointment_id;
    if (apptId) {
      submitting.value = true;
      try {
        await client.delete(`/customer/drafts/${apptId}`).catch(() => {
          return client.delete(`/appointments/drafts/${apptId}`);
        });
        toast.success('Draft booking discarded. Your reserved slot is now free.');
      } catch (err) {
        toast.warning('Draft booking removed.');
      } finally {
        submitting.value = false;
      }
    }
    router.push('/customer/dashboard/bookings');
  };

  onMounted(() => {
    fetchCheckoutData();
    if (typeof window !== 'undefined') {
      window.addEventListener('visibilitychange', handleVisibilityOrFocus);
      window.addEventListener('focus', handleVisibilityOrFocus);
    }
  });

  onUnmounted(() => {
    clearInterval(timerInterval);
    if (typeof window !== 'undefined') {
      window.removeEventListener('visibilitychange', handleVisibilityOrFocus);
      window.removeEventListener('focus', handleVisibilityOrFocus);
    }
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
    isHoldExpired,
    renewingHold,
    renewHold,
    copyToClipboard,
    submitPayment,
    payWithPaystack,
    paystackPublicKey,
    cancelTransaction,
    saveAsDraftAndExit,
    discardDraftAndExit,
    fetchCheckoutData,
  };
}
