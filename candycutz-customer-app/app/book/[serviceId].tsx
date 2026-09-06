import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useMutation, useQuery } from '@tanstack/react-query';
import { SafeAreaView } from 'react-native-safe-area-context';
import { availabilityApi, barbersApi, bookingsApi, servicesApi, zonesApi } from '../../src/api/client';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';
import { Barber, ServiceZone, TimeSlot } from '../../src/types';

export default function BookingWizardScreen() {
  const router = useRouter();
  const { serviceId } = useLocalSearchParams<{ serviceId: string }>();
  const { isAuthenticated } = useAuthStore();

  const [step, setStep] = useState<number>(1);
  const [appointmentType, setAppointmentType] = useState<'in_shop' | 'home_service'>('in_shop');
  const [selectedDate, setSelectedDate] = useState<string>(new Date().toISOString().split('T')[0]);
  const [selectedTime, setSelectedTime] = useState<string>('');
  const [selectedBarberId, setSelectedBarberId] = useState<number | undefined>(undefined);
  const [paymentMethod, setPaymentMethod] = useState<'pay_at_venue' | 'stripe' | 'wallet'>('pay_at_venue');
  const [notes, setNotes] = useState<string>('');

  // Home service address state
  const [streetAddress, setStreetAddress] = useState<string>('');
  const [landmark, setLandmark] = useState<string>('');
  const [selectedZone, setSelectedZone] = useState<ServiceZone | null>(null);

  // Fetch service details
  const { data: service, isLoading: loadingService } = useQuery({
    queryKey: ['service', serviceId],
    queryFn: () => servicesApi.getById(Number(serviceId)),
    enabled: !!serviceId,
  });

  // Fetch barbers
  const { data: barbers = [] } = useQuery({
    queryKey: ['barbers'],
    queryFn: barbersApi.getAll,
  });

  // Fetch zones for home service
  const { data: serviceZones = [] } = useQuery({
    queryKey: ['serviceZones'],
    queryFn: zonesApi.getServiceZones,
    enabled: appointmentType === 'home_service',
  });

  // Fetch available slots
  const { data: timeSlots = [], isLoading: loadingSlots } = useQuery({
    queryKey: ['slots', selectedDate, serviceId, selectedBarberId, appointmentType],
    queryFn: () =>
      availabilityApi.getSlots({
        date: selectedDate,
        service_id: Number(serviceId),
        barber_id: selectedBarberId,
        type: appointmentType,
      }),
    enabled: !!serviceId && !!selectedDate,
  });

  // Generate next 5 calendar dates
  const nextDates = Array.from({ length: 5 }).map((_, i) => {
    const d = new Date();
    d.setDate(d.getDate() + i);
    return {
      dateString: d.toISOString().split('T')[0],
      dayName: i === 0 ? 'Today' : i === 1 ? 'Tomorrow' : d.toLocaleDateString('en-US', { weekday: 'short' }),
      dayNumber: d.getDate(),
      month: d.toLocaleDateString('en-US', { month: 'short' }),
    };
  });

  const createBookingMutation = useMutation({
    mutationFn: () => {
      return bookingsApi.create({
        service_id: Number(serviceId),
        barber_id: selectedBarberId,
        appointment_date: selectedDate,
        start_time: selectedTime,
        appointment_type: appointmentType,
        destination_address:
          appointmentType === 'home_service'
            ? {
                street_address: streetAddress,
                area_landmark: landmark,
                city: 'Keffi',
                state: 'Nasarawa',
                service_zone_id: selectedZone?.id,
              }
            : undefined,
        payment_method: paymentMethod,
        notes,
      });
    },
    onSuccess: (appointment) => {
      router.replace({
        pathname: '/booking/confirmation',
        params: {
          reference: appointment.booking_reference,
          date: appointment.appointment_date,
          time: appointment.start_time,
          total: appointment.grand_total,
          type: appointment.appointment_type,
        },
      });
    },
    onError: (e: any) => {
      Alert.alert('Booking Error', e.response?.data?.message || 'Could not complete your booking.');
    },
  });

  const handleSubmit = () => {
    if (!isAuthenticated) {
      Alert.alert('Sign In Required', 'Please sign in or create an account to finalize your booking.', [
        { text: 'Sign In', onPress: () => router.push('/auth/login') },
        { text: 'Cancel', style: 'cancel' },
      ]);
      return;
    }

    if (appointmentType === 'home_service' && (!streetAddress || !landmark)) {
      Alert.alert('Address Missing', 'Please enter your street address and landmark in Keffi.');
      return;
    }

    if (!selectedTime) {
      Alert.alert('Time Missing', 'Please select an appointment time slot.');
      return;
    }

    createBookingMutation.mutate();
  };

  if (loadingService) {
    return (
      <SafeAreaView style={styles.centerContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </SafeAreaView>
    );
  }

  const basePrice = Number(service?.price || 0);
  const homeSurcharge = appointmentType === 'home_service' ? Number(selectedZone?.surcharge || 1500) : 0;
  const grandTotal = basePrice + homeSurcharge;

  return (
    <SafeAreaView style={styles.safeArea} edges={['bottom']}>
      <ScrollView contentContainerStyle={styles.container}>
        {/* Service Header Preview */}
        <Card style={styles.servicePreviewCard} elevated>
          <View>
            <Text style={styles.serviceName}>{service?.name}</Text>
            <Text style={styles.serviceMeta}>
              ⏱ {service?.duration_minutes} mins • ₦{basePrice.toLocaleString()} base
            </Text>
          </View>
        </Card>

        {/* Step 1: Appointment Type */}
        <Text style={styles.stepTitle}>1. Choose Service Location</Text>
        <View style={styles.typeSelectorRow}>
          <TouchableOpacity
            style={[
              styles.typeCard,
              appointmentType === 'in_shop' && styles.typeCardActive,
            ]}
            onPress={() => setAppointmentType('in_shop')}
          >
            <Text style={styles.typeIcon}>✂</Text>
            <Text style={styles.typeHeading}>In-Shop Saloon</Text>
            <Text style={styles.typeSub}>Angwan Kare, BCG, Keffi</Text>
            <Text style={styles.typePriceTag}>Standard Rate</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[
              styles.typeCard,
              appointmentType === 'home_service' && styles.typeCardActive,
            ]}
            onPress={() => setAppointmentType('home_service')}
          >
            <Text style={styles.typeIcon}>🏠</Text>
            <Text style={styles.typeHeading}>Home Concierge</Text>
            <Text style={styles.typeSub}>We travel to your door</Text>
            <Text style={styles.typePriceTag}>+₦1,500 zone fee</Text>
          </TouchableOpacity>
        </View>

        {/* Home Service Address Input (if applicable) */}
        {appointmentType === 'home_service' && (
          <Card style={styles.addressCard}>
            <Text style={styles.cardHeader}>Enter Keffi Delivery Address</Text>
            <TextInput
              style={styles.input}
              placeholder="Street / Compound Address (e.g. Near Total)"
              placeholderTextColor={COLORS.textMuted}
              value={streetAddress}
              onChangeText={setStreetAddress}
            />
            <TextInput
              style={styles.input}
              placeholder="Landmark (e.g. Opposite Angwan Kare BCG Gas)"
              placeholderTextColor={COLORS.textMuted}
              value={landmark}
              onChangeText={setLandmark}
            />

            {/* Zone Selector */}
            <Text style={styles.subLabel}>Select Keffi Area Zone:</Text>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.zoneScroll}>
              {serviceZones.map((zone: ServiceZone) => (
                <TouchableOpacity
                  key={zone.id}
                  style={[
                    styles.zonePill,
                    selectedZone?.id === zone.id && styles.zonePillActive,
                  ]}
                  onPress={() => setSelectedZone(zone)}
                >
                  <Text style={[styles.zoneText, selectedZone?.id === zone.id && styles.zoneTextActive]}>
                    {zone.name} (+₦{Number(zone.surcharge).toLocaleString()})
                  </Text>
                </TouchableOpacity>
              ))}
            </ScrollView>
          </Card>
        )}

        {/* Step 2: Date Selection */}
        <Text style={styles.stepTitle}>2. Select Date</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.dateScroll}>
          {nextDates.map((item) => {
            const isSelected = selectedDate === item.dateString;
            return (
              <TouchableOpacity
                key={item.dateString}
                style={[styles.dateCard, isSelected && styles.dateCardActive]}
                onPress={() => {
                  setSelectedDate(item.dateString);
                  setSelectedTime('');
                }}
              >
                <Text style={[styles.dateDay, isSelected && styles.dateDayActive]}>{item.dayName}</Text>
                <Text style={[styles.dateNum, isSelected && styles.dateNumActive]}>{item.dayNumber}</Text>
                <Text style={[styles.dateMonth, isSelected && styles.dateMonthActive]}>{item.month}</Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>

        {/* Step 3: Available Time Slots */}
        <Text style={styles.stepTitle}>3. Select Available Time</Text>
        {loadingSlots ? (
          <ActivityIndicator color={COLORS.primary} style={{ marginVertical: 14 }} />
        ) : (
          <View style={styles.slotsGrid}>
            {timeSlots.map((slot: TimeSlot) => {
              const isSelected = selectedTime === slot.time;
              return (
                <TouchableOpacity
                  key={slot.time}
                  disabled={!slot.available}
                  style={[
                    styles.slotPill,
                    isSelected && styles.slotPillActive,
                    !slot.available && styles.slotPillDisabled,
                  ]}
                  onPress={() => setSelectedTime(slot.time)}
                >
                  <Text
                    style={[
                      styles.slotText,
                      isSelected && styles.slotTextActive,
                      !slot.available && styles.slotTextDisabled,
                    ]}
                  >
                    {slot.time.substring(0, 5)}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
        )}

        {/* Step 4: Stylist Selection */}
        <Text style={styles.stepTitle}>4. Preferred Barber</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.barberScroll}>
          <TouchableOpacity
            style={[
              styles.barberChoiceCard,
              selectedBarberId === undefined && styles.barberChoiceActive,
            ]}
            onPress={() => setSelectedBarberId(undefined)}
          >
            <Text style={styles.barberChoiceName}>Any Master Barber</Text>
            <Text style={styles.barberChoiceSub}>Fastest availability</Text>
          </TouchableOpacity>

          {barbers.map((barber: Barber) => (
            <TouchableOpacity
              key={barber.id}
              style={[
                styles.barberChoiceCard,
                selectedBarberId === barber.id && styles.barberChoiceActive,
              ]}
              onPress={() => setSelectedBarberId(barber.id)}
            >
              <Text style={styles.barberChoiceName}>{barber.name}</Text>
              <Text style={styles.barberChoiceSub}>★ {Number(barber.rating || 5.0).toFixed(1)}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        {/* Step 5: Payment Method */}
        <Text style={styles.stepTitle}>5. Payment Method</Text>
        <View style={styles.paymentMethods}>
          <TouchableOpacity
            style={[
              styles.paymentOption,
              paymentMethod === 'pay_at_venue' && styles.paymentOptionActive,
            ]}
            onPress={() => setPaymentMethod('pay_at_venue')}
          >
            <Text style={styles.paymentTitle}>💵 Pay at Venue / On Delivery</Text>
            <Text style={styles.paymentSub}>Cash or POS terminal at Keffi branch or to barber</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[
              styles.paymentOption,
              paymentMethod === 'stripe' && styles.paymentOptionActive,
            ]}
            onPress={() => setPaymentMethod('stripe')}
          >
            <Text style={styles.paymentTitle}>💳 Card / Online Payment</Text>
            <Text style={styles.paymentSub}>Instant debit card checkout via Stripe / Paystack</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[
              styles.paymentOption,
              paymentMethod === 'wallet' && styles.paymentOptionActive,
            ]}
            onPress={() => setPaymentMethod('wallet')}
          >
            <Text style={styles.paymentTitle}>👛 CandyCutz Wallet</Text>
            <Text style={styles.paymentSub}>Deduct instantly from your wallet balance</Text>
          </TouchableOpacity>
        </View>

        {/* Cost Breakdown */}
        <Card style={styles.breakdownCard} elevated>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Base Service:</Text>
            <Text style={styles.summaryVal}>₦{basePrice.toLocaleString()}</Text>
          </View>
          {appointmentType === 'home_service' && (
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Home Service Surcharge:</Text>
              <Text style={styles.summaryVal}>+₦{homeSurcharge.toLocaleString()}</Text>
            </View>
          )}
          <View style={styles.divider} />
          <View style={styles.totalRow}>
            <Text style={styles.totalLabel}>Total Payable:</Text>
            <Text style={styles.totalVal}>₦{grandTotal.toLocaleString()}</Text>
          </View>
        </Card>

        <Button
          title={createBookingMutation.isPending ? 'Securing Booking...' : `Confirm & Book (₦${grandTotal.toLocaleString()})`}
          onPress={handleSubmit}
          loading={createBookingMutation.isPending}
          disabled={!selectedTime}
          style={styles.submitBtn}
        />
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  container: {
    padding: SPACING.md,
    paddingBottom: 40,
  },
  centerContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.background,
  },
  servicePreviewCard: {
    padding: SPACING.md,
    marginBottom: SPACING.lg,
    borderLeftWidth: 4,
    borderLeftColor: COLORS.primary,
  },
  serviceName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
  },
  serviceMeta: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
    marginTop: 4,
  },
  stepTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '800',
    marginTop: SPACING.md,
    marginBottom: SPACING.sm,
  },
  typeSelectorRow: {
    flexDirection: 'row',
    gap: 12,
  },
  typeCard: {
    flex: 1,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.lg,
    padding: SPACING.md,
    alignItems: 'center',
  },
  typeCardActive: {
    borderColor: COLORS.primary,
    backgroundColor: COLORS.surfaceHighlight,
  },
  typeIcon: {
    fontSize: 26,
    marginBottom: 6,
  },
  typeHeading: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  typeSub: {
    color: COLORS.textMuted,
    fontSize: 11,
    textAlign: 'center',
    marginTop: 2,
  },
  typePriceTag: {
    color: COLORS.primary,
    fontSize: 11,
    fontWeight: '800',
    marginTop: 6,
  },
  addressCard: {
    marginTop: SPACING.md,
    padding: SPACING.md,
  },
  cardHeader: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
    marginBottom: 10,
  },
  input: {
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    padding: 12,
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    marginBottom: 10,
  },
  subLabel: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
    marginTop: 6,
    marginBottom: 6,
  },
  zoneScroll: {
    marginTop: 4,
  },
  zonePill: {
    backgroundColor: COLORS.surfaceHighlight,
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: RADIUS.full,
    borderWidth: 1,
    borderColor: COLORS.border,
    marginRight: 8,
  },
  zonePillActive: {
    borderColor: COLORS.primary,
    backgroundColor: COLORS.primaryLight,
  },
  zoneText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
  },
  zoneTextActive: {
    color: COLORS.primary,
  },
  dateScroll: {
    flexDirection: 'row',
  },
  dateCard: {
    width: 72,
    paddingVertical: 12,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    alignItems: 'center',
    marginRight: 10,
  },
  dateCardActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  dateDay: {
    color: COLORS.textMuted,
    fontSize: 11,
    fontWeight: '600',
  },
  dateDayActive: {
    color: '#0A0A0C',
  },
  dateNum: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
    marginVertical: 2,
  },
  dateNumActive: {
    color: '#0A0A0C',
  },
  dateMonth: {
    color: COLORS.textMuted,
    fontSize: 10,
  },
  dateMonthActive: {
    color: '#0A0A0C',
  },
  slotsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  slotPill: {
    width: '31%',
    paddingVertical: 10,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    alignItems: 'center',
  },
  slotPillActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  slotPillDisabled: {
    opacity: 0.3,
  },
  slotText: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  slotTextActive: {
    color: '#0A0A0C',
  },
  slotTextDisabled: {
    color: COLORS.textMuted,
  },
  barberScroll: {
    flexDirection: 'row',
  },
  barberChoiceCard: {
    paddingVertical: 12,
    paddingHorizontal: 16,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    marginRight: 10,
  },
  barberChoiceActive: {
    borderColor: COLORS.primary,
    backgroundColor: COLORS.surfaceHighlight,
  },
  barberChoiceName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  barberChoiceSub: {
    color: COLORS.primary,
    fontSize: 11,
    marginTop: 2,
  },
  paymentMethods: {
    gap: 10,
  },
  paymentOption: {
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    padding: SPACING.md,
  },
  paymentOptionActive: {
    borderColor: COLORS.primary,
    backgroundColor: COLORS.surfaceHighlight,
  },
  paymentTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  paymentSub: {
    color: COLORS.textMuted,
    fontSize: 11,
    marginTop: 3,
  },
  breakdownCard: {
    marginTop: SPACING.lg,
    padding: SPACING.md,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
  summaryLabel: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
  },
  summaryVal: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginVertical: 8,
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'baseline',
  },
  totalLabel: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '800',
  },
  totalVal: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '900',
  },
  submitBtn: {
    marginTop: SPACING.lg,
  },
});
