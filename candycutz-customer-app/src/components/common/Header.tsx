import React from 'react';
import { StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { useRouter } from 'expo-router';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useAuthStore } from '../../store/authStore';

interface HeaderProps {
  title?: string;
  showLocationBadge?: boolean;
}

export const Header: React.FC<HeaderProps> = ({
  title,
  showLocationBadge = true,
}) => {
  const router = useRouter();
  const { user, isAuthenticated } = useAuthStore();

  return (
    <View style={styles.container}>
      <View style={styles.topRow}>
        <View>
          <Text style={styles.logo}>CANDYCUTZ</Text>
          <Text style={styles.tagline}>LUXURY GROOMING</Text>
        </View>

        <TouchableOpacity
          activeOpacity={0.8}
          onPress={() => (isAuthenticated ? router.push('/(tabs)/profile') : router.push('/auth/login'))}
          style={styles.avatarButton}
        >
          {isAuthenticated && user ? (
            <View style={styles.avatarContainer}>
              <Text style={styles.avatarText}>
                {(user.real_name || user.name || 'U').charAt(0).toUpperCase()}
              </Text>
            </View>
          ) : (
            <View style={styles.loginPill}>
              <Text style={styles.loginPillText}>Sign In</Text>
            </View>
          )}
        </TouchableOpacity>
      </View>

      {showLocationBadge && (
        <View style={styles.locationBanner}>
          <View style={styles.statusDot} />
          <Text style={styles.locationText}>
            Open • Angwan Kare, BCG, Keffi Branch
          </Text>
        </View>
      )}

      {title && <Text style={styles.title}>{title}</Text>}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: SPACING.md,
    paddingTop: SPACING.sm,
    paddingBottom: SPACING.md,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    backgroundColor: COLORS.background,
  },
  topRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  logo: {
    fontSize: FONTS.sizes.xl,
    fontWeight: '900',
    color: COLORS.primary,
    letterSpacing: 2,
  },
  tagline: {
    fontSize: 9,
    fontWeight: '700',
    color: COLORS.textMuted,
    letterSpacing: 1.5,
    marginTop: -2,
  },
  avatarButton: {
    padding: 4,
  },
  avatarContainer: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: COLORS.surfaceElevated,
    borderWidth: 1.5,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    color: COLORS.primary,
    fontWeight: '800',
    fontSize: FONTS.sizes.md,
  },
  loginPill: {
    backgroundColor: COLORS.primaryLight,
    borderWidth: 1,
    borderColor: COLORS.primary,
    borderRadius: RADIUS.full,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  loginPillText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  locationBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 10,
    backgroundColor: COLORS.surfaceElevated,
    paddingVertical: 5,
    paddingHorizontal: 10,
    borderRadius: RADIUS.full,
    alignSelf: 'flex-start',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  statusDot: {
    width: 7,
    height: 7,
    borderRadius: 3.5,
    backgroundColor: COLORS.success,
    marginRight: 6,
  },
  locationText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '500',
  },
  title: {
    fontSize: FONTS.sizes.xxl,
    fontWeight: '800',
    color: COLORS.textPrimary,
    marginTop: SPACING.md,
  },
});
