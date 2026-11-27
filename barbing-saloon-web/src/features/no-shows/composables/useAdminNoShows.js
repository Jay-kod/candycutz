import { reactive, ref } from 'vue';
import { noShowApi } from '../api/noShowApi';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';

export function useAdminNoShows() {
  const toast = useToast();
  const { confirm } = useConfirm();

  const loading = ref(true);
  const savingPolicy = ref(false);
  const processingId = ref(null);
  const filter = ref('all');
  const search = ref('');

  const records = ref([]);
  const flaggedCustomers = ref([]);
  const stats = ref({
    total_no_shows: 0,
    pending_appeals: 0,
    forfeited_count: 0,
    flagged_customers: 0,
  });

  const policy = reactive({
    no_show_delay_minutes: 15,
    no_show_warning_lead_minutes: 5,
    no_show_grace_limit: 1,
    no_show_grace_extension_minutes: 15,
    no_show_threshold: 2,
    no_show_threshold_window_days: 90,
    no_show_auto_mark_enabled: true,
    no_show_auto_forfeit_deposit: false,
  });

  const loadDashboard = async (silent = false) => {
    if (!silent) loading.value = true;
    try {
      const res = await noShowApi.getDashboard({
        filter: filter.value,
        search: search.value || undefined,
      });
      const data = res.data?.data || {};
      records.value = data.items || [];
      stats.value = data.stats || stats.value;
      flaggedCustomers.value = data.flagged_customers || [];
      if (data.policy) {
        Object.assign(policy, data.policy);
      }
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to load no-show dashboard.');
    } finally {
      loading.value = false;
    }
  };

  const savePolicy = async () => {
    savingPolicy.value = true;
    try {
      const res = await noShowApi.updatePolicy({ ...policy });
      if (res.data?.data) {
        Object.assign(policy, res.data.data);
      }
      toast.success('No-show policy settings saved.');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to save policy settings.');
    } finally {
      savingPolicy.value = false;
    }
  };

  const forfeitDeposit = async (record) => {
    const refCode = record.appointment?.booking_reference || `#${record.appointment_id}`;
    const ok = await confirm({
      title: 'Forfeit Customer Deposit',
      message: `Mark the paid deposit for ${refCode} as forfeited? The customer will be notified by email and push notification.`,
      confirmText: 'Forfeit Deposit',
    });
    if (!ok) return;

    processingId.value = record.id;
    try {
      await noShowApi.forfeitRecordDeposit(record.id, 'Marked forfeited from Admin No-Show Console');
      toast.success('Deposit marked as forfeited.');
      await loadDashboard(true);
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to forfeit deposit.');
    } finally {
      processingId.value = null;
    }
  };

  const submitRefund = async (recordId, payload) => {
    processingId.value = recordId;
    try {
      await noShowApi.initiateRefund(recordId, payload);
      toast.success('Refund process initiated and customer notified.');
      await loadDashboard(true);
      return true;
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to initiate refund.');
      return false;
    } finally {
      processingId.value = recordId;
    }
  };

  const submitAppealDecision = async (recordId, payload) => {
    processingId.value = recordId;
    try {
      await noShowApi.resolveAppeal(recordId, payload);
      toast.success(`Appeal ${payload.outcome}.`);
      await loadDashboard(true);
      return true;
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to resolve appeal.');
      return false;
    } finally {
      processingId.value = null;
    }
  };

  const resetCustomerFlag = async (customer) => {
    const ok = await confirm({
      title: 'Clear Deposit Requirement',
      message: `Remove the mandatory deposit requirement for ${customer.name}?`,
      confirmText: 'Clear Requirement',
    });
    if (!ok) return;

    try {
      await noShowApi.resetCustomerFlag(customer.id);
      toast.success(`Deposit flag cleared for ${customer.name}.`);
      await loadDashboard(true);
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to clear customer flag.');
    }
  };

  return {
    loading,
    savingPolicy,
    processingId,
    filter,
    search,
    records,
    flaggedCustomers,
    stats,
    policy,
    loadDashboard,
    savePolicy,
    forfeitDeposit,
    submitRefund,
    submitAppealDecision,
    resetCustomerFlag,
  };
}
