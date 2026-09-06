import React, { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { SafeAreaView } from 'react-native-safe-area-context';
import { staffWalkInApi } from '../src/api/client';
import { Button } from '../src/components/common/Button';
import { Card } from '../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../src/constants/theme';
import { useChairStore } from '../src/store/chairStore';
import { Appointment, Service } from '../src/types';

export default function WalkInScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const setActiveClient = useChairStore((state) => state.setActiveClient);

  const [customerName, setCustomerName] = useState('');
  const [customerPhone, setCustomerPhone] = useState('');
  const [selectedServiceId, setSelectedServiceId] = useState<number | null>(null);
  const [paymentMethod, setPaymentMethod] = useState<'cash' | 'pos'>('cash');
  const [takeImmediately, setTakeImmediately] = useState(true);
  const [notes, setNotes] = useState('');

  const { data: services = [], isLoading: loadingServices } = useQuery<Service[]>({
    queryKey: ['services'],
    queryFn: staffWalkInApi.getServices,
  });

  const walkInMutation = useMutation({
    mutationFn: (payload: {
      customer_name: string;
      customer_phone?: string;
      service_id: number;
      payment_method: 'cash' | 'pos';
      notes?: string;
    }) => staffWalkInApi.createWalkIn(payload),
    onSuccess: (appointment: Appointment) => {
      queryClient.invalidateQueries({ queryKey: ['todayQueue'] });
      if (takeImmediately) {
        setActiveClient(appointment);
      }
      Alert.alert('Walk-In Added', `Client ${customerName} has been queued successfully.`);
      router.back();
    },
    onError: (e: any) => {
      Alert.alert('Error', e.response?.data?.message || 'Failed to add walk-in client.');
    },
  });

  const handleSubmit = () => {
    if (!customerName.trim()) {
      Alert.alert('Name Required', 'Please enter the client name.');
      return;
    }
    if (!selectedServiceId) {
      Alert.alert('Service Required', 'Please select a haircut or grooming service.');
      return;
    }

    walkInMutation.mutate({
      customer_name: customerName.trim(),
      customer_phone: customerPhone.trim() || undefined,
      service_id: selectedServiceId,
      payment_method: paymentMethod,
      notes: notes.trim() || undefined,
    });
  };

  const selectedService = services.find((s: Service) => s.id === selectedServiceId);

  return (
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={{ flex: 1 }}
      >
        <ScrollView contentContainerStyle={styles.container}>
          <Text style={styles.title}>Fast Walk-In Entry</Text>
          <Text style={styles.subtitle}>
            Register an in-shop walk-in guest in 30 seconds. No app account required.
          </Text>

          {/* Client Details */}
          <Card style={styles.formCard} elevated>
            <Text style={styles.fieldLabel}>Client Name *</Text>
            <TextInput
              style={styles.input}
              placeholder="e.g. Ibrahim Keffi"
              placeholderTextColor={COLORS.textMuted}
              value={customerName}
              onChangeText={setCustomerName}
            />

            <Text style={styles.fieldLabel}>Phone Number (Optional)</Text>
            <TextInput
              style={styles.input}
              placeholder="0803 000 0000"
              placeholderTextColor={COLORS.textMuted}
              keyboardType="phone-pad"
              value={customerPhone}
              onChangeText={setCustomerPhone}
            />

            <Text style={styles.fieldLabel}>Select Service *</Text>
            {loadingServices ? (
              <ActivityIndicator color={COLORS.primary} style={{ marginVertical: 10 }} />
            ) : (
              <View style={styles.servicesGrid}>
                {services.map((s: Service) => {
                  const isSelected = selectedServiceId === s.id;
                  return (
                    <TouchableOpacity
                      key={s.id}
                      style={[styles.serviceOption, isSelected && styles.serviceOptionActive]}
                      onPress={() => setSelectedServiceId(s.id)}
                    >
                      <Text style={[styles.serviceOptionName, isSelected && styles.serviceOptionNameActive]}>
                        {s.name}
                      </Text>
                      <Text style={[styles.serviceOptionPrice, isSelected && styles.serviceOptionPriceActive]}>
                        ₦{Number(s.price).toLocaleString()}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </View>
            )}

            <Text style={styles.fieldLabel}>Payment Collected Via *</Text>
            <View style={styles.paymentRow}>
              <TouchableOpacity
                style={[styles.payPill, paymentMethod === 'cash' && styles.payPillActive]}
                onPress={() => setPaymentMethod('cash')}
              >
                <Text style={[styles.payText, paymentMethod === 'cash' && styles.payTextActive]}>
                  💵 Cash in Hand
                </Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.payPill, paymentMethod === 'pos' && styles.payPillActive]}
                onPress={() => setPaymentMethod('pos')}
              >
                <Text style={[styles.payText, paymentMethod === 'pos' && styles.payTextActive]}>
                  💳 POS Card Terminal
                </Text>
              </TouchableOpacity>
            </View>

            <Text style={styles.fieldLabel}>Notes / Style Instructions</Text>
            <TextInput
              style={[styles.input, { height: 60 }]}
              placeholder="e.g. Skin fade with beard shape-up"
              placeholderTextColor={COLORS.textMuted}
              multiline
              value={notes}
              onChangeText={setNotes}
            />

            {selectedService && (
              <View style={styles.totalBox}>
                <Text style={styles.totalLabel}>Grand Total:</Text>
                <Text style={styles.totalAmount}>₦{Number(selectedService.price).toLocaleString()}</Text>
              </View>
            )}

            <Button
              title={walkInMutation.isPending ? 'Queuing Client...' : 'Confirm & Add Walk-In'}
              onPress={handleSubmit}
              loading={walkInMutation.isPending}
              disabled={!customerName || !selectedServiceId}
              style={styles.submitBtn}
            />
          </Card>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  container: {
    padding: SPACING.lg,
    paddingBottom: 40,
  },
  title: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
  },
  subtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
    marginTop: 4,
    marginBottom: SPACING.md,
  },
  formCard: {
    padding: SPACING.md,
  },
  fieldLabel: {
    color: COLORS.textSecondary,
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 6,
    marginTop: 10,
  },
  input: {
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    padding: 12,
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
  },
  servicesGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  serviceOption: {
    width: '48%',
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    padding: 10,
  },
  serviceOptionActive: {
    borderColor: COLORS.primary,
    backgroundColor: COLORS.primaryLight,
  },
  serviceOptionName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  serviceOptionNameActive: {
    color: COLORS.primary,
  },
  serviceOptionPrice: {
    color: COLORS.textMuted,
    fontSize: 11,
    marginTop: 4,
  },
  serviceOptionPriceActive: {
    color: COLORS.primary,
    fontWeight: '700',
  },
  paymentRow: {
    flexDirection: 'row',
    gap: 10,
  },
  payPill: {
    flex: 1,
    paddingVertical: 10,
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    alignItems: 'center',
  },
  payPillActive: {
    borderColor: COLORS.primary,
    backgroundColor: COLORS.surfaceHighlight,
  },
  payText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  payTextActive: {
    color: COLORS.primary,
  },
  totalBox: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: COLORS.surfaceHighlight,
    padding: 12,
    borderRadius: RADIUS.md,
    marginTop: 16,
  },
  totalLabel: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  totalAmount: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '900',
  },
  submitBtn: {
    marginTop: 18,
  },
});
