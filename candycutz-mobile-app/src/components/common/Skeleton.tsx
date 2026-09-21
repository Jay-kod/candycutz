import React, { useEffect, useRef } from 'react';
import { Animated, DimensionValue, Dimensions, StyleProp, StyleSheet, View, ViewStyle } from 'react-native';
import { COLORS, RADIUS, SPACING } from '../../constants/theme';

const { width: SCREEN_WIDTH } = Dimensions.get('window');
const SHIMMER_WIDTH = SCREEN_WIDTH * 0.6;

// ─── Base Skeleton Primitive ─────────────────────────────────────────────────

interface SkeletonProps {
  style?: StyleProp<ViewStyle>;
  width?: DimensionValue;
  height?: DimensionValue;
  variant?: 'rect' | 'circle' | 'pill';
}

export function Skeleton({ style, width, height, variant = 'rect' }: SkeletonProps) {
  const pulse = useRef(new Animated.Value(0.45)).current;
  const shimmer = useRef(new Animated.Value(-SHIMMER_WIDTH)).current;

  useEffect(() => {
    const pulseAnim = Animated.loop(
      Animated.sequence([
        Animated.timing(pulse, { toValue: 0.85, duration: 700, useNativeDriver: true }),
        Animated.timing(pulse, { toValue: 0.45, duration: 700, useNativeDriver: true }),
      ])
    );

    const shimmerAnim = Animated.loop(
      Animated.timing(shimmer, {
        toValue: SCREEN_WIDTH + SHIMMER_WIDTH,
        duration: 1400,
        useNativeDriver: true,
      })
    );

    pulseAnim.start();
    shimmerAnim.start();
    return () => {
      pulseAnim.stop();
      shimmerAnim.stop();
    };
  }, [pulse, shimmer]);

  const variantStyle =
    variant === 'circle'
      ? { borderRadius: typeof height === 'number' ? height / 2 : RADIUS.full }
      : variant === 'pill'
        ? { borderRadius: RADIUS.full }
        : {};

  return (
    <Animated.View
      style={[
        baseStyles.block,
        variantStyle,
        style,
        width !== undefined && { width },
        height !== undefined && { height },
        { opacity: pulse },
      ]}
    >
      {/* Shimmer sweep overlay */}
      <Animated.View
        style={[
          baseStyles.shimmer,
          { transform: [{ translateX: shimmer }] },
        ]}
      />
    </Animated.View>
  );
}

const baseStyles = StyleSheet.create({
  block: {
    backgroundColor: COLORS.surfaceHighlight,
    borderRadius: RADIUS.sm,
    overflow: 'hidden',
  },
  shimmer: {
    position: 'absolute',
    top: 0,
    bottom: 0,
    width: SHIMMER_WIDTH,
    backgroundColor: 'rgba(255, 255, 255, 0.04)',
  },
});

// ─── Helper: Skeleton Card Wrapper ───────────────────────────────────────────

function SkeletonCard({ children, style }: { children: React.ReactNode; style?: StyleProp<ViewStyle> }) {
  return <View style={[cardStyles.card, style]}>{children}</View>;
}

const cardStyles = StyleSheet.create({
  card: {
    padding: SPACING.md,
    borderRadius: RADIUS.lg,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
});

// ─── ServiceSkeletons ────────────────────────────────────────────────────────

export function ServiceSkeletons({ count = 4 }: { count?: number }) {
  return (
    <View style={svcStyles.list}>
      {Array.from({ length: count }).map((_, i) => (
        <SkeletonCard key={i} style={svcStyles.card}>
          <View style={svcStyles.topRow}>
            <View style={svcStyles.meta}>
              <Skeleton width="70%" height={18} />
              <Skeleton width="45%" height={12} style={{ marginTop: SPACING.sm }} />
            </View>
            <Skeleton width={72} height={24} variant="pill" />
          </View>
          <Skeleton width="100%" height={12} style={{ marginTop: SPACING.md }} />
          <Skeleton width="80%" height={12} style={{ marginTop: SPACING.xs }} />
          <Skeleton width="50%" height={36} variant="pill" style={{ marginTop: SPACING.md }} />
        </SkeletonCard>
      ))}
    </View>
  );
}

const svcStyles = StyleSheet.create({
  list: { padding: SPACING.md, gap: SPACING.md },
  card: { minHeight: 140 },
  topRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  meta: { flex: 1, marginRight: SPACING.md },
});

// ─── BookingSkeletons ────────────────────────────────────────────────────────

export function BookingSkeletons({ count = 3 }: { count?: number }) {
  return (
    <View style={bkStyles.list}>
      {Array.from({ length: count }).map((_, i) => (
        <SkeletonCard key={i}>
          <View style={bkStyles.row}>
            <View style={bkStyles.flex}>
              <Skeleton width={100} height={11} />
              <Skeleton width="75%" height={18} style={{ marginTop: SPACING.sm }} />
            </View>
            <Skeleton width={70} height={24} variant="pill" />
          </View>
          <Skeleton width="100%" height={11} style={{ marginTop: SPACING.md }} />
          <Skeleton width="65%" height={11} style={{ marginTop: SPACING.sm }} />
        </SkeletonCard>
      ))}
    </View>
  );
}

const bkStyles = StyleSheet.create({
  list: { padding: SPACING.md, gap: SPACING.md },
  row: { flexDirection: 'row', alignItems: 'flex-start' },
  flex: { flex: 1 },
});

// ─── AppointmentSkeletons ────────────────────────────────────────────────────

export function AppointmentSkeletons({ count = 4 }: { count?: number }) {
  return (
    <View style={aptStyles.list}>
      {Array.from({ length: count }).map((_, i) => (
        <SkeletonCard key={i}>
          {/* Header row: customer + status badge */}
          <View style={aptStyles.headerRow}>
            <View style={aptStyles.flex}>
              <Skeleton width="55%" height={16} />
              <Skeleton width="40%" height={12} style={{ marginTop: SPACING.xs }} />
            </View>
            <Skeleton width={82} height={24} variant="pill" />
          </View>
          {/* Detail rows */}
          <View style={aptStyles.detailRow}>
            <Skeleton width={13} height={13} variant="circle" />
            <Skeleton width="35%" height={12} style={{ marginLeft: SPACING.sm }} />
            <Skeleton width="40%" height={12} style={{ marginLeft: 'auto' }} />
          </View>
          <View style={aptStyles.detailRow}>
            <Skeleton width={13} height={13} variant="circle" />
            <Skeleton width="30%" height={12} style={{ marginLeft: SPACING.sm }} />
            <Skeleton width="35%" height={12} style={{ marginLeft: 'auto' }} />
          </View>
          <View style={aptStyles.detailRow}>
            <Skeleton width={13} height={13} variant="circle" />
            <Skeleton width="40%" height={12} style={{ marginLeft: SPACING.sm }} />
            <Skeleton width={72} height={14} variant="pill" style={{ marginLeft: 'auto' }} />
          </View>
          {/* Action buttons placeholder */}
          <View style={aptStyles.actionsRow}>
            <Skeleton width="42%" height={36} variant="pill" />
            <Skeleton width="42%" height={36} variant="pill" />
          </View>
        </SkeletonCard>
      ))}
    </View>
  );
}

const aptStyles = StyleSheet.create({
  list: { padding: SPACING.md, gap: SPACING.md },
  headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: SPACING.md },
  flex: { flex: 1 },
  detailRow: { flexDirection: 'row', alignItems: 'center', marginBottom: SPACING.sm },
  actionsRow: { flexDirection: 'row', justifyContent: 'space-between', marginTop: SPACING.md },
});

// ─── ScheduleSkeletons ───────────────────────────────────────────────────────

export function ScheduleSkeletons() {
  return (
    <View style={schedStyles.wrapper}>
      {/* Weekly days card */}
      <SkeletonCard>
        {Array.from({ length: 7 }).map((_, i) => (
          <View key={i} style={schedStyles.dayRow}>
            <View style={schedStyles.dayInfo}>
              <Skeleton width={85} height={16} />
              <Skeleton width={110} height={12} style={{ marginTop: SPACING.xs }} />
            </View>
            <Skeleton width={42} height={24} variant="pill" />
          </View>
        ))}
      </SkeletonCard>

      {/* Blocked periods section */}
      <Skeleton width="55%" height={18} style={{ marginTop: SPACING.lg, marginBottom: SPACING.sm }} />
      <Skeleton width="80%" height={12} style={{ marginBottom: SPACING.md }} />
      <SkeletonCard>
        <View style={schedStyles.blockedRow}>
          <Skeleton width="60%" height={14} />
          <Skeleton width={50} height={14} variant="pill" />
        </View>
        <Skeleton width="70%" height={12} style={{ marginTop: SPACING.sm }} />
      </SkeletonCard>
    </View>
  );
}

const schedStyles = StyleSheet.create({
  wrapper: { padding: SPACING.md },
  dayRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  dayInfo: { flex: 1 },
  blockedRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
});

// ─── BookingFlowSkeleton ─────────────────────────────────────────────────────

export function BookingFlowSkeleton() {
  return (
    <View style={flowStyles.wrapper}>
      {/* Service header preview */}
      <SkeletonCard>
        <Skeleton width="65%" height={20} />
        <Skeleton width="50%" height={14} style={{ marginTop: SPACING.sm }} />
      </SkeletonCard>

      {/* Step 1: Location type */}
      <Skeleton width="60%" height={16} style={{ marginTop: SPACING.lg, marginBottom: SPACING.sm }} />
      <View style={flowStyles.typeRow}>
        <SkeletonCard style={flowStyles.typeCard}>
          <Skeleton width={28} height={28} variant="circle" />
          <Skeleton width="70%" height={14} style={{ marginTop: SPACING.sm }} />
          <Skeleton width="50%" height={12} style={{ marginTop: SPACING.xs }} />
        </SkeletonCard>
        <SkeletonCard style={flowStyles.typeCard}>
          <Skeleton width={28} height={28} variant="circle" />
          <Skeleton width="70%" height={14} style={{ marginTop: SPACING.sm }} />
          <Skeleton width="50%" height={12} style={{ marginTop: SPACING.xs }} />
        </SkeletonCard>
      </View>

      {/* Step 2: Date row */}
      <Skeleton width="50%" height={16} style={{ marginTop: SPACING.lg, marginBottom: SPACING.sm }} />
      <View style={flowStyles.dateRow}>
        {Array.from({ length: 5 }).map((_, i) => (
          <Skeleton key={i} width={64} height={72} variant="rect" style={{ borderRadius: RADIUS.md }} />
        ))}
      </View>

      {/* Step 3: Time slots */}
      <Skeleton width="55%" height={16} style={{ marginTop: SPACING.lg, marginBottom: SPACING.sm }} />
      <View style={flowStyles.slotGrid}>
        {Array.from({ length: 8 }).map((_, i) => (
          <Skeleton key={i} width="30%" height={38} variant="pill" />
        ))}
      </View>

      {/* Confirm button */}
      <Skeleton width="100%" height={48} variant="pill" style={{ marginTop: SPACING.xl }} />
    </View>
  );
}

const flowStyles = StyleSheet.create({
  wrapper: { padding: SPACING.md },
  typeRow: { flexDirection: 'row', gap: SPACING.md },
  typeCard: { flex: 1, alignItems: 'center', paddingVertical: SPACING.lg },
  dateRow: { flexDirection: 'row', gap: SPACING.sm },
  slotGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: SPACING.sm },
});

// ─── ProfileEditSkeleton ─────────────────────────────────────────────────────

export function ProfileEditSkeleton() {
  return (
    <View style={profStyles.wrapper}>
      {/* Avatar */}
      <View style={profStyles.avatarRow}>
        <Skeleton width={80} height={80} variant="circle" />
        <Skeleton width={100} height={14} variant="pill" style={{ marginTop: SPACING.sm }} />
      </View>

      {/* Form fields */}
      {Array.from({ length: 4 }).map((_, i) => (
        <View key={i} style={profStyles.fieldGroup}>
          <Skeleton width="30%" height={13} style={{ marginBottom: SPACING.xs }} />
          <Skeleton width="100%" height={44} style={{ borderRadius: RADIUS.md }} />
        </View>
      ))}

      {/* Save button */}
      <Skeleton width="100%" height={48} variant="pill" style={{ marginTop: SPACING.lg }} />
    </View>
  );
}

const profStyles = StyleSheet.create({
  wrapper: { padding: SPACING.md },
  avatarRow: { alignItems: 'center', marginBottom: SPACING.xl },
  fieldGroup: { marginBottom: SPACING.md },
});

// ─── NotificationSkeletons ───────────────────────────────────────────────────

export function NotificationSkeletons({ count = 5 }: { count?: number }) {
  return (
    <View style={notifStyles.wrapper}>
      {/* Section header */}
      <Skeleton width={80} height={14} style={{ marginBottom: SPACING.md }} />

      {Array.from({ length: count }).map((_, i) => (
        <View key={i} style={notifStyles.row}>
          <Skeleton width={40} height={40} variant="circle" />
          <View style={notifStyles.textCol}>
            <Skeleton width="80%" height={14} />
            <Skeleton width="55%" height={12} style={{ marginTop: SPACING.xs }} />
          </View>
          <Skeleton width={36} height={12} />
        </View>
      ))}
    </View>
  );
}

const notifStyles = StyleSheet.create({
  wrapper: { padding: SPACING.md },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    gap: SPACING.sm,
  },
  textCol: { flex: 1 },
});

// ─── QueueSkeletons ──────────────────────────────────────────────────────────

export function QueueSkeletons({ count = 3 }: { count?: number }) {
  return (
    <View style={qStyles.wrapper}>
      {/* Stats row placeholder */}
      <View style={qStyles.statsRow}>
        {Array.from({ length: 3 }).map((_, i) => (
          <SkeletonCard key={i} style={qStyles.statCard}>
            <Skeleton width={28} height={28} variant="circle" />
            <Skeleton width={32} height={22} style={{ marginTop: SPACING.xs }} />
            <Skeleton width={54} height={11} style={{ marginTop: SPACING.xs }} />
          </SkeletonCard>
        ))}
      </View>

      {/* Queue cards */}
      {Array.from({ length: count }).map((_, i) => (
        <SkeletonCard key={i} style={qStyles.queueCard}>
          <View style={qStyles.cardHeader}>
            <Skeleton width={36} height={36} variant="circle" />
            <View style={qStyles.headerText}>
              <Skeleton width="60%" height={16} />
              <Skeleton width="40%" height={12} style={{ marginTop: SPACING.xs }} />
            </View>
            <Skeleton width={70} height={24} variant="pill" />
          </View>
          <View style={qStyles.detailRow}>
            <Skeleton width="45%" height={12} />
            <Skeleton width="30%" height={12} />
          </View>
          {/* Action buttons */}
          <View style={qStyles.actionsRow}>
            <Skeleton width="48%" height={38} variant="pill" />
            <Skeleton width="48%" height={38} variant="pill" />
          </View>
        </SkeletonCard>
      ))}
    </View>
  );
}

const qStyles = StyleSheet.create({
  wrapper: { padding: SPACING.md },
  statsRow: { flexDirection: 'row', gap: SPACING.sm, marginBottom: SPACING.md },
  statCard: { flex: 1, alignItems: 'center', paddingVertical: SPACING.md },
  queueCard: { marginBottom: SPACING.md },
  cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: SPACING.md },
  headerText: { flex: 1, marginLeft: SPACING.sm },
  detailRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: SPACING.md },
  actionsRow: { flexDirection: 'row', justifyContent: 'space-between' },
});

// ─── WalkInSkeleton ──────────────────────────────────────────────────────────

export function WalkInSkeleton() {
  return (
    <View style={walkStyles.wrapper}>
      <SkeletonCard>
        {/* Field: Client Name */}
        <Skeleton width="35%" height={13} style={{ marginBottom: SPACING.xs }} />
        <Skeleton width="100%" height={44} style={{ borderRadius: RADIUS.md, marginBottom: SPACING.md }} />

        {/* Field: Phone */}
        <Skeleton width="45%" height={13} style={{ marginBottom: SPACING.xs }} />
        <Skeleton width="100%" height={44} style={{ borderRadius: RADIUS.md, marginBottom: SPACING.md }} />

        {/* Service grid */}
        <Skeleton width="35%" height={13} style={{ marginBottom: SPACING.sm }} />
        <View style={walkStyles.serviceGrid}>
          {Array.from({ length: 4 }).map((_, i) => (
            <Skeleton
              key={i}
              width="48%"
              height={56}
              style={{ borderRadius: RADIUS.md }}
            />
          ))}
        </View>

        {/* Payment row */}
        <Skeleton width="40%" height={13} style={{ marginTop: SPACING.md, marginBottom: SPACING.sm }} />
        <View style={walkStyles.payRow}>
          <Skeleton width="47%" height={40} variant="pill" />
          <Skeleton width="47%" height={40} variant="pill" />
        </View>

        {/* Notes */}
        <Skeleton width="45%" height={13} style={{ marginTop: SPACING.md, marginBottom: SPACING.xs }} />
        <Skeleton width="100%" height={60} style={{ borderRadius: RADIUS.md, marginBottom: SPACING.md }} />

        {/* Submit button */}
        <Skeleton width="100%" height={48} variant="pill" />
      </SkeletonCard>
    </View>
  );
}

const walkStyles = StyleSheet.create({
  wrapper: { padding: SPACING.md },
  serviceGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: SPACING.sm },
  payRow: { flexDirection: 'row', justifyContent: 'space-between' },
});

// ─── BarberCardSkeletons ─────────────────────────────────────────────────────

export function BarberCardSkeletons({ count = 2 }: { count?: number }) {
  return (
    <View style={barberStyles.row}>
      {Array.from({ length: count }).map((_, i) => (
        <SkeletonCard key={i} style={barberStyles.card}>
          <Skeleton width={56} height={56} variant="circle" style={{ alignSelf: 'center' }} />
          <Skeleton width="70%" height={14} style={{ marginTop: SPACING.sm, alignSelf: 'center' }} />
          <Skeleton width="50%" height={12} style={{ marginTop: SPACING.xs, alignSelf: 'center' }} />
        </SkeletonCard>
      ))}
    </View>
  );
}

const barberStyles = StyleSheet.create({
  row: { flexDirection: 'row', paddingHorizontal: SPACING.md, gap: SPACING.md },
  card: { width: 140, alignItems: 'center', paddingVertical: SPACING.md },
});

// ─── HomeAppointmentSkeleton ─────────────────────────────────────────────────

export function HomeAppointmentSkeleton() {
  return (
    <SkeletonCard style={homeAptStyles.card}>
      <View style={homeAptStyles.row}>
        <Skeleton width={42} height={42} variant="circle" />
        <View style={homeAptStyles.textCol}>
          <Skeleton width="65%" height={16} />
          <Skeleton width="45%" height={12} style={{ marginTop: SPACING.xs }} />
          <Skeleton width="55%" height={12} style={{ marginTop: SPACING.xs }} />
        </View>
        <Skeleton width={72} height={28} variant="pill" />
      </View>
    </SkeletonCard>
  );
}

const homeAptStyles = StyleSheet.create({
  card: { marginHorizontal: SPACING.md, marginBottom: SPACING.md },
  row: { flexDirection: 'row', alignItems: 'center' },
  textCol: { flex: 1, marginLeft: SPACING.sm },
});
