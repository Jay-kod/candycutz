import React, { useState } from 'react';
import {
  Alert,
  Linking,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { ConfirmDialog } from '../../src/components/common/ConfirmDialog';
import { CONFIG } from '../../src/constants/config';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';
import { ChairStatus } from '../../src/types';

const CHAIR_STATUSES: { label: string; value: ChairStatus }[] = [
  { label: 'Free (Ready)', value: 'free' },
  { label: 'In Service', value: 'busy' },
  { label: 'On Break', value: 'break' },
  { label: 'Offline', value: 'offline' },
];

export default function ProfileScreen() {
  const router = useRouter();
  const [logoutDialogVisible, setLogoutDialogVisible] = useState(false);
  const {
    user,
    barber,
    isAuthenticated,
    isBarber,
    viewMode,
    logout,
    setChairStatus,
    setViewMode,
  } = useAuthStore();

  const isStaffDesk = isBarber && viewMode === 'barber';

  const handleLogout = () => {
    setLogoutDialogVisible(true);
  };

  const confirmLogout = async () => {
    setLogoutDialogVisible(false);
    await logout();
    router.replace('/(tabs)');
  };

  const toggleViewMode = () => {
    if (viewMode === 'barber') {
      setViewMode('customer');
      Alert.alert('Client View Active', 'You are now viewing the app as a client would see it.');
    } else {
      setViewMode('barber');
      Alert.alert('Barber Staff Desk Active', 'Switched back to Barber Staff Desk.');
    }
    router.replace('/(tabs)');
  };

  const handleContactSupport = () => {
    Alert.alert(
      'Support & Concierge',
      `${CONFIG.BRANCH.NAME}\n${CONFIG.BRANCH.ADDRESS}\n\nPhone: ${CONFIG.BRANCH.PHONE}`,
      [
        {
          text: '📞 Call Saloon',
          onPress: () => Linking.openURL(`tel:${CONFIG.BRANCH.PHONE}`),
        },
        {
          text: '🗺 Open Location',
          onPress: () => Linking.openURL(CONFIG.BRANCH.MAPS_URL),
        },
        {
          text: 'Cancel',
          style: 'cancel',
        },
      ]
    );
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <ScrollView contentContainerStyle={styles.container}>
        <Text style={styles.headerTitle}>
          {isStaffDesk ? 'Staff Desk' : 'Profile'}
        </Text>

        {isAuthenticated && user ? (
          <>
            {/* Customer Header: [Avatar / Name / Email] */}
            {!isStaffDesk && (
              <Card style={styles.profileCard} elevated>
                <View style={styles.avatar}>
                  <Text style={styles.avatarText}>
                    {(user.real_name || user.name || 'C').charAt(0).toUpperCase()}
                  </Text>
                </View>
                <View style={styles.profileDetails}>
                  <Text style={styles.profileName}>{user.real_name || user.name}</Text>
                  <Text style={styles.profileEmail}>{user.email}</Text>
                  {user.phone && <Text style={styles.profilePhone}>{user.phone}</Text>}
                </View>
              </Card>
            )}

            {/* Barber Header: [Avatar / Name / Chair status toggle] */}
            {isStaffDesk && (
              <Card style={styles.barberHeaderCard} elevated>
                <View style={styles.barberProfileRow}>
                  <View style={styles.avatar}>
                    <Text style={styles.avatarText}>
                      {(barber?.name || user.real_name || user.name || 'B').charAt(0).toUpperCase()}
                    </Text>
                  </View>
                  <View style={styles.profileDetails}>
                    <Text style={styles.profileName}>{barber?.name || user.real_name || user.name}</Text>
                    <Text style={styles.barberRoleTag}>MASTER STYLIST</Text>
                    <Text style={styles.profileEmail}>{user.email || barber?.email}</Text>
                  </View>
                </View>

                {/* Chair Status Toggle */}
                <View style={styles.chairToggleSection}>
                  <Text style={styles.chairSectionLabel}>CHAIR STATUS</Text>
                  <View style={styles.chairChipsRow}>
                    {CHAIR_STATUSES.map((s) => {
                      const isSelected = barber?.chair_status === s.value;
                      return (
                        <TouchableOpacity
                          key={s.value}
                          style={[styles.chairChip, isSelected && styles.chairChipActive]}
                          onPress={() => setChairStatus(s.value)}
                        >
                          <Text style={[styles.chairChipText, isSelected && styles.chairChipTextActive]}>
                            {s.label}
                          </Text>
                        </TouchableOpacity>
                      );
                    })}
                  </View>
                </View>
              </Card>
            )}

            {/* Customer Navigation Menu */}
            {!isStaffDesk && (
              <>
                {/* Group 1: Bookings, Wishlist, Notifications, Reviews */}
                <Card style={styles.menuCard} elevated>
                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/(tabs)/bookings')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>📅</Text>
                      <Text style={styles.menuText}>My Bookings</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>

                  <View style={styles.divider} />

                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/wishlist')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>❤️</Text>
                      <Text style={styles.menuText}>Wishlist</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>

                  <View style={styles.divider} />

                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/notifications')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>🔔</Text>
                      <Text style={styles.menuText}>Notifications</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>

                  <View style={styles.divider} />

                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/reviews')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>★</Text>
                      <Text style={styles.menuText}>My Reviews</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>
                </Card>

                {/* Group 2: Settings */}
                <Card style={styles.menuCard} elevated>
                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/settings')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>⚙️</Text>
                      <Text style={styles.menuText}>Settings</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>
                </Card>

                {/* Group 3: Support / Contact Us & Sign Out */}
                <Card style={styles.menuCard} elevated>
                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={handleContactSupport}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>📞</Text>
                      <Text style={styles.menuText}>Support / Contact Us</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>

                  <View style={styles.divider} />

                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={handleLogout}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>🚪</Text>
                      <Text style={[styles.menuText, styles.dangerText]}>Sign Out</Text>
                    </View>
                    <Text style={[styles.menuArrow, styles.dangerText]}>&rarr;</Text>
                  </TouchableOpacity>
                </Card>
              </>
            )}

            {/* Barber Staff Desk Navigation Menu */}
            {isStaffDesk && (
              <>
                {/* Group 1: Services, Gallery, Blog, Analytics */}
                <Card style={styles.menuCard} elevated>
                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/services')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>✂️</Text>
                      <Text style={styles.menuText}>My Services</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>

                  <View style={styles.divider} />

                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/gallery')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>📸</Text>
                      <Text style={styles.menuText}>Gallery</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>

                  <View style={styles.divider} />

                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/blog')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>📖</Text>
                      <Text style={styles.menuText}>Blog Posts</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>

                  <View style={styles.divider} />

                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/analytics')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>📊</Text>
                      <Text style={styles.menuText}>Analytics</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>
                </Card>

                {/* Group 2: Settings (Shared with customer) */}
                <Card style={styles.menuCard} elevated>
                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/settings')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>⚙️</Text>
                      <Text style={styles.menuText}>Settings</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>
                </Card>

                {/* Group 3: Switch to Client View & Sign Out */}
                <Card style={styles.menuCard} elevated>
                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={toggleViewMode}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>👁</Text>
                      <Text style={styles.menuText}>Switch to Client View</Text>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>

                  <View style={styles.divider} />

                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={handleLogout}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>🚪</Text>
                      <Text style={[styles.menuText, styles.dangerText]}>Sign Out</Text>
                    </View>
                    <Text style={[styles.menuArrow, styles.dangerText]}>&rarr;</Text>
                  </TouchableOpacity>
                </Card>
              </>
            )}
          </>
        ) : (
          /* Guest State */
          <Card style={styles.guestCard} elevated>
            <Text style={styles.guestTitle}>Welcome to CandyCutz</Text>
            <Text style={styles.guestSubtitle}>
              Sign in or create an account to book your appointments, manage preferences, and view your loyalty perks.
            </Text>
            <View style={styles.authButtonsRow}>
              <Button
                title="Sign In"
                onPress={() => router.push('/auth/login')}
                style={styles.authBtn}
              />
              <Button
                title="Create Account"
                variant="outline"
                onPress={() => router.push('/auth/register')}
                style={styles.authBtn}
              />
            </View>
            <View style={styles.divider} />
            <TouchableOpacity
              style={styles.menuItem}
              onPress={handleContactSupport}
            >
              <View style={styles.menuItemLeft}>
                <Text style={styles.menuIcon}>📞</Text>
                <Text style={styles.menuText}>Support / Contact Us</Text>
              </View>
              <Text style={styles.menuArrow}>&rarr;</Text>
            </TouchableOpacity>
          </Card>
        )}
      </ScrollView>

      {/* Logout Confirmation Dialog */}
      <ConfirmDialog
        visible={logoutDialogVisible}
        title="Log out of CandyCutz?"
        message="You will need to sign in again to access your bookings and profile."
        confirmLabel="Log out"
        destructive
        onCancel={() => setLogoutDialogVisible(false)}
        onConfirm={confirmLogout}
      />
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
    gap: SPACING.md,
  },
  headerTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.hero - 8,
    fontWeight: '800',
    marginBottom: SPACING.xs,
  },
  profileCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: SPACING.md,
  },
  barberHeaderCard: {
    padding: SPACING.md,
    gap: SPACING.md,
  },
  barberProfileRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  avatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 2,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 16,
  },
  avatarText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xxl,
    fontWeight: '800',
  },
  profileDetails: {
    flex: 1,
  },
  profileName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '700',
  },
  barberRoleTag: {
    color: COLORS.primary,
    fontSize: 9,
    fontWeight: '800',
    letterSpacing: 1,
    marginTop: 2,
  },
  profileEmail: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    marginTop: 2,
  },
  profilePhone: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
    marginTop: 1,
  },
  chairToggleSection: {
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    paddingTop: 12,
  },
  chairSectionLabel: {
    color: COLORS.primary,
    fontSize: 9,
    fontWeight: '800',
    letterSpacing: 1,
    marginBottom: 8,
  },
  chairChipsRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
  },
  chairChip: {
    paddingVertical: 6,
    paddingHorizontal: 12,
    backgroundColor: COLORS.surfaceHighlight,
    borderRadius: RADIUS.full,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  chairChipActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  chairChipText: {
    color: COLORS.textSecondary,
    fontSize: 11,
    fontWeight: '700',
  },
  chairChipTextActive: {
    color: '#0A0A0C',
  },
  menuCard: {
    paddingVertical: 4,
    paddingHorizontal: SPACING.md,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 14,
  },
  menuItemLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  menuIcon: {
    fontSize: 18,
    width: 24,
    textAlign: 'center',
  },
  menuText: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '600',
  },
  menuArrow: {
    color: COLORS.textMuted,
    fontSize: 18,
  },
  dangerText: {
    color: '#EF4444',
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.border,
  },
  guestCard: {
    padding: SPACING.lg,
    gap: SPACING.md,
  },
  guestTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
  },
  guestSubtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 20,
  },
  authButtonsRow: {
    flexDirection: 'row',
    gap: SPACING.md,
  },
  authBtn: {
    flex: 1,
  },
});
