import { ref } from 'vue'
import { refundApi } from '../api/refundApi'
import { useToast } from '@/core/composables/useToast'

export function useCustomerBookingRefunds(onCompleted) {
  const toast = useToast()
  const cancelModalBooking = ref(null)
  const refundModalBooking = ref(null)
  const submittingRefundAction = ref(false)

  async function confirmCancellation({ appointmentId, payload }) {
    submittingRefundAction.value = true
    try {
      const res = await refundApi.cancelAppointmentWithRefund(appointmentId, payload)
      const refundReq = res?.refund_request
      if (refundReq?.status === 'completed') {
        toast.success(`Booking cancelled. Refund of ₦${Number(refundReq.refund_amount || 0).toLocaleString()} completed.`)
      } else if (refundReq) {
        toast.success('Booking cancelled and refund request submitted.')
      } else {
        toast.success('Booking cancelled.')
      }
      cancelModalBooking.value = null
      if (onCompleted) await onCompleted()
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Failed to cancel booking')
    } finally {
      submittingRefundAction.value = false
    }
  }

  async function submitCompassionateRefund(formData) {
    submittingRefundAction.value = true
    try {
      await refundApi.submitRefundRequest(formData)
      toast.success('Compassionate refund request submitted for review.')
      refundModalBooking.value = null
      if (onCompleted) await onCompleted()
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Could not submit refund request.')
    } finally {
      submittingRefundAction.value = false
    }
  }

  return {
    cancelModalBooking,
    refundModalBooking,
    submittingRefundAction,
    confirmCancellation,
    submitCompassionateRefund,
  }
}
