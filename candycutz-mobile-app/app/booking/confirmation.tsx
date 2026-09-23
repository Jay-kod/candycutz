import React from 'react';
import {
  Linking,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { CONFIG } from '../../src/constants/config';
import { FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAppTheme } from '../../src/hooks/useAppTheme';

export default function BookingConfirmationScreen() {
  const router = useRouter();
  const { colors } = useAppTheme();
  const params = useLocalSearchParams<{
    reference: string;
    date: string;
    time: string;
    total: string;
    type: string;
  }>();

  const handleOpenMaps = () => {
    Linking.openURL(CONFIG.BRANCH.MAPS_URL);
  };

  const isInShop = params.type === 'in_shop' || !params.type;
  const styles = createStyles(colors);

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.container}>
        <View style={styles.successIconBox}>
          <Text style={styles.checkIcon}>✓</Text>
        </View>

        <Text style={styles.confirmedTitle}>Booking Confirmed!</Text>
        <Text style={styles.confirmedSubtitle}>
          We look forward to giving you an immaculate grooming session.
        </Text>

        {/* Reference & Details Card */}
        <Card style={styles.detailsCard} elevated>
          <View style={styles.refRow}>
            <Text style={styles.refLabel}>BOOKING REFERENCE</Text>
            <Text style={styles.refValue}>{params.reference || 'CC-CONFIRMED'}</Text>
          </View>

          <View style={styles.divider} />

          <View style={styles.row}>
            <Text style={styles.label}>Date & Time:</Text>
            <Text style={styles.val}>
              {params.date} at {(params.time || '').substring(0, 5)}
            </Text>
          </View>

          <View style={styles.row}>
            <Text style={styles.label}>Service Type:</Text>
            <Text style={styles.val}>
              {isInShop ? 'In-Shop Saloon' : 'Home Service Concierge'}
            </Text>
          </View>

          <View style={styles.row}>
            <Text style={styles.label}>Total Amount:</Text>
            <Text style={[styles.val, { color: colors.primary, fontWeight: '800' }]}>
              ₦{Number(params.total || 0).toLocaleString()}
            </Text>
          </View>
        </Card>

        {/* Branch Directions Card for In-Shop */}
        {isInShop && (
          <Card style={styles.locationCard} elevated>
            <Text style={styles.locationTitle}>📍 Keffi Flagship Saloon</Text>
            <Text style={styles.locationAddress}>{CONFIG.BRANCH.ADDRESS}</Text>
            <TouchableOpacity onPress={handleOpenMaps} style={styles.mapsBtn}>
              <Text style={styles.mapsBtnText}>Open Directions in Google Maps &rarr;</Text>
            </TouchableOpacity>
          </Card>
        )}

        <View style={styles.buttonStack}>
          <Button
            title="View in My Bookings"
            onPress={() => router.replace('/(tabs)/bookings')}
            style={styles.primaryBtn}
          />
          <Button
            title="Return to Home"
            variant="outline"
            onPress={() => router.replace('/(tabs)')}
          />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

function createStyles(colors: ReturnType<typeof useAppTheme>['colors']) {
  return StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: colors.background,
  },
  container: {
    padding: SPACING.lg,
    alignItems: 'center',
    paddingTop: 40,
    paddingBottom: 40,
  },
  successIconBox: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: colors.primaryLight,
    borderWidth: 2,
    borderColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: SPACING.md,
  },
  checkIcon: {
    fontSize: 40,
    color: colors.primary,
    fontWeight: '900',
  },
  confirmedTitle: {
    color: colors.textPrimary,
    fontSize: FONTS.sizes.hero - 6,
    fontWeight: '900',
    textAlign: 'center',
  },
  confirmedSubtitle: {
    color: colors.textSecondary,
    fontSize: FONTS.sizes.sm,
    textAlign: 'center',
    marginTop: 6,
    marginBottom: SPACING.xl,
    lineHeight: 20,
  },
  detailsCard: {
    width: '100%',
    padding: SPACING.md,
    marginBottom: SPACING.md,
  },
  refRow: {
    alignItems: 'center',
    paddingVertical: 8,
  },
  refLabel: {
    color: colors.textMuted,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1.5,
  },
  refValue: {
    color: colors.primary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '900',
    letterSpacing: 1,
    marginTop: 4,
  },
  divider: {
    height: 1,
    backgroundColor: colors.border,
    marginVertical: 12,
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  label: {
    color: colors.textSecondary,
    fontSize: FONTS.sizes.sm,
  },
  val: {
    color: colors.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
  },
  locationCard: {
    width: '100%',
    padding: SPACING.md,
    marginBottom: SPACING.xl,
    borderLeftWidth: 4,
    borderLeftColor: colors.primary,
  },
  locationTitle: {
    color: colors.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  locationAddress: {
    color: colors.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
    marginVertical: 6,
  },
  mapsBtn: {
    marginTop: 4,
  },
  mapsBtnText: {
    color: colors.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  buttonStack: {
    width: '100%',
    gap: 12,
  },
  primaryBtn: {
    width: '100%',
  },
  });
}
