import React from 'react';
import { StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { useRouter } from 'expo-router';
import { FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useAppTheme } from '../../hooks/useAppTheme';
import { useAuthStore } from '../../store/authStore';
import { useNotifications } from '../../hooks/useNotifications';
import { UIcon } from './UIcon';

interface HeaderProps {
  title?: string;
  showLocationBadge?: boolean;
}

export const Header: React.FC<HeaderProps> = ({
  title,
  showLocationBadge = true,
}) => {
  const router = useRouter();
  const { colors } = useAppTheme();
  const { user, isAuthenticated } = useAuthStore();
  const { unreadCount } = useNotifications(isAuthenticated);

  return (
    <View style={[styles.container, { backgroundColor: colors.background, borderBottomColor: colors.border }]}>
      <View style={styles.topRow}>
        <View>
          <Text style={[styles.logo, { color: colors.primary }]}>CANDYCUTZ</Text>
          <Text style={[styles.tagline, { color: colors.textMuted }]}>LUXURY GROOMING</Text>
        </View>

        <View style={styles.headerActions}>
          {isAuthenticated && (
            <TouchableOpacity
              activeOpacity={0.8}
              onPress={() => router.push('/notifications')}
              style={[
                styles.bellButton,
                { backgroundColor: colors.surfaceElevated, borderColor: colors.border },
              ]}
              accessibilityRole="button"
              accessibilityLabel="Notifications"
            >
              <UIcon name="bell" size={20} color={colors.textPrimary} />
              {unreadCount > 0 && (
                <View style={[styles.badge, { backgroundColor: colors.primary }]}>
                  <Text style={[styles.badgeText, { color: '#0A0A0C' }]}>
                    {unreadCount > 99 ? '99+' : unreadCount}
                  </Text>
                </View>
              )}
            </TouchableOpacity>
          )}

          <TouchableOpacity
            activeOpacity={0.8}
            onPress={() => (isAuthenticated ? router.push('/(tabs)/profile') : router.push('/auth/login'))}
            style={styles.avatarButton}
          >
            {isAuthenticated && user ? (
              <View
                style={[
                  styles.avatarContainer,
                  { backgroundColor: colors.surfaceElevated, borderColor: colors.primary },
                ]}
              >
                <Text style={[styles.avatarText, { color: colors.primary }]}>
                  {(user.real_name || user.name || 'U').charAt(0).toUpperCase()}
                </Text>
              </View>
            ) : (
              <View
                style={[
                  styles.loginPill,
                  { backgroundColor: colors.primaryLight, borderColor: colors.primary },
                ]}
              >
                <Text style={[styles.loginPillText, { color: colors.primary }]}>Sign In</Text>
              </View>
            )}
          </TouchableOpacity>
        </View>
      </View>

      {showLocationBadge && (
        <View
          style={[
            styles.locationBanner,
            { backgroundColor: colors.surfaceElevated, borderColor: colors.border },
          ]}
        >
          <View style={[styles.statusDot, { backgroundColor: colors.success }]} />
          <Text style={[styles.locationText, { color: colors.textSecondary }]}>
            Open • Angwan Kare, BCG, Keffi Branch
          </Text>
        </View>
      )}

      {title && <Text style={[styles.title, { color: colors.textPrimary }]}>{title}</Text>}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: SPACING.md,
    paddingTop: SPACING.sm,
    paddingBottom: SPACING.md,
    borderBottomWidth: 1,
  },
  topRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  logo: {
    fontSize: FONTS.sizes.xl,
    fontWeight: '900',
    letterSpacing: 2,
  },
  tagline: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 1.5,
    marginTop: -2,
  },
  headerActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  bellButton: {
    width: 36,
    height: 36,
    borderRadius: 18,
    borderWidth: 1,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  badge: {
    position: 'absolute',
    top: -4,
    right: -4,
    borderRadius: 9,
    minWidth: 18,
    height: 18,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 4,
  },
  badgeText: {
    fontSize: 10,
    fontWeight: '800',
  },
  avatarButton: {
    padding: 4,
  },
  avatarContainer: {
    width: 36,
    height: 36,
    borderRadius: 18,
    borderWidth: 1.5,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    fontWeight: '800',
    fontSize: FONTS.sizes.md,
  },
  loginPill: {
    borderWidth: 1,
    borderRadius: RADIUS.full,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  loginPillText: {
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  locationBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 10,
    paddingVertical: 5,
    paddingHorizontal: 10,
    borderRadius: RADIUS.full,
    alignSelf: 'flex-start',
    borderWidth: 1,
  },
  statusDot: {
    width: 7,
    height: 7,
    borderRadius: 3.5,
    marginRight: 6,
  },
  locationText: {
    fontSize: FONTS.sizes.xs,
    fontWeight: '500',
  },
  title: {
    fontSize: FONTS.sizes.xxl,
    fontWeight: '800',
    marginTop: SPACING.md,
  },
});
