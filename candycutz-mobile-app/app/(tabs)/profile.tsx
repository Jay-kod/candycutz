import React, { useState } from 'react';
import {
  Image,
  Linking,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Fingerprint } from 'lucide-react-native';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { ConfirmDialog } from '../../src/components/common/ConfirmDialog';
import { ActionDialog } from '../../src/components/common/ActionDialog';
import { CONFIG, getStorageUrl } from '../../src/constants/config';
import { FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { UIcon } from '../../src/components/common/UIcon';
import { useAppTheme } from '../../src/hooks/useAppTheme';
import { useAuthStore } from '../../src/store/authStore';
import { useToastStore } from '../../src/store/toastStore';
import { ChairStatus } from '../../src/types';

const CHAIR_STATUSES: { label: string; value: ChairStatus }[] = [
  { label: 'Free (Ready)', value: 'free' },
  { label: 'In Service', value: 'busy' },
  { label: 'On Break', value: 'break' },
  { label: 'Offline', value: 'offline' },
];

export default function ProfileScreen() {
  const router = useRouter();
  const { colors } = useAppTheme();
  const styles = createStyles(colors);
  const [logoutDialogVisible, setLogoutDialogVisible] = useState(false);
  const [supportDialogVisible, setSupportDialogVisible] = useState(false);
  const showToast = useToastStore((s) => s.show);
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
  };

  const toggleViewMode = () => {
    if (viewMode === 'barber') {
      setViewMode('customer');
      showToast({
        variant: 'info',
        title: 'Client View Active',
        message: 'You are now viewing the app as a client would see it.',
      });
    } else {
      setViewMode('barber');
      showToast({
        variant: 'info',
        title: 'Barber Staff Desk Active',
        message: 'Switched back to Barber Staff Desk.',
      });
    }
    router.replace('/(tabs)');
  };

  const handleContactSupport = () => {
    setSupportDialogVisible(true);
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <ScrollView contentContainerStyle={styles.container}>
        <Text style={styles.headerTitle}>
          {isStaffDesk ? 'Staff Desk' : 'Profile'}
        </Text>

        {isAuthenticated && user ? (
          <>
            {/* Customer account overview */}
            {!isStaffDesk && (
              <View style={styles.customerProfileContent}>
                <Card style={[styles.customerHeroCard, { backgroundColor: colors.surfaceElevated, borderColor: colors.borderLight }]} elevated>
                  <View style={styles.customerHeroGlow} />
                  <View style={styles.customerHeroTopRow}>
                  <TouchableOpacity
                    style={styles.customerAvatarWrapper}
                    onPress={() => router.push('/profile/edit')}
                    activeOpacity={0.8}
                  >
                    {user.avatar ? (
                      <Image source={{ uri: getStorageUrl(user.avatar) }} style={styles.avatarImage} />
                    ) : (
                      <View style={styles.avatar}>
                        <Text style={[styles.avatarText, { color: colors.primary }]}>
                          {(user.real_name || user.name || 'C').charAt(0).toUpperCase()}
                        </Text>
                      </View>
                    )}
                    <View style={[styles.customerEditBadge, { backgroundColor: colors.primary }]}>
                      <UIcon name="settingsSliders" size={11} color={colors.background} />
                    </View>
                  </TouchableOpacity>
                  <View style={styles.customerIdentity}>
                    <Text style={[styles.customerEyebrow, { color: colors.primary }]}>CANDYCUTZ MEMBER</Text>
                    <Text style={[styles.customerHeroName, { color: colors.textPrimary }]} numberOfLines={1}>
                      {user.real_name || user.name}
                    </Text>
                    <Text style={[styles.profileEmail, { color: colors.textSecondary }]} numberOfLines={1}>{user.email}</Text>
                  </View>
                  <TouchableOpacity
                    style={[styles.customerEditButton, { borderColor: colors.borderLight }]}
                    onPress={() => router.push('/profile/edit')}
                    activeOpacity={0.8}
                  >
                    <UIcon name="settingsSliders" size={15} color={colors.primary} />
                  </TouchableOpacity>
                </View>
                <View style={[styles.customerMemberLine, { borderTopColor: colors.border }]}>
                  <UIcon name="star" size={14} color={colors.primary} />
                  <Text style={[styles.customerMemberText, { color: colors.textSecondary }]}>Your grooming profile, bookings, and preferences in one place.</Text>
                </View>
                </Card>

                <View style={styles.customerQuickGrid}>
                  <TouchableOpacity style={[styles.customerQuickTile, { backgroundColor: colors.surface, borderColor: colors.border }]} onPress={() => router.push('/(tabs)/bookings')}>
                    <UIcon name="calendarCheck" size={21} color={colors.primary} />
                    <Text style={[styles.customerQuickLabel, { color: colors.textPrimary }]}>Bookings</Text>
                    <Text style={[styles.customerQuickHint, { color: colors.textMuted }]}>Your visits</Text>
                  </TouchableOpacity>
                  <TouchableOpacity style={[styles.customerQuickTile, { backgroundColor: colors.surface, borderColor: colors.border }]} onPress={() => router.push('/profile/wishlist')}>
                    <UIcon name="star" size={21} color={colors.primary} />
                    <Text style={[styles.customerQuickLabel, { color: colors.textPrimary }]}>Wishlist</Text>
                    <Text style={[styles.customerQuickHint, { color: colors.textMuted }]}>Saved picks</Text>
                  </TouchableOpacity>
                </View>
              </View>
            )}

            {/* Barber Header: [Avatar / Name / Chair status toggle] */}
            {isStaffDesk && (
              <Card style={styles.barberHeaderCard} elevated>
                <View style={styles.barberProfileRow}>
                  <TouchableOpacity
                    style={styles.avatarWrapper}
                    onPress={() => router.push('/barber/profile-edit')}
                    activeOpacity={0.8}
                  >
                    {(barber?.avatar || user.avatar) ? (
                      <Image source={{ uri: getStorageUrl(barber?.avatar || user.avatar!) }} style={styles.avatarImage} />
                    ) : (
                      <View style={styles.avatar}>
                        <Text style={styles.avatarText}>
                          {(barber?.name || user.real_name || user.name || 'B').charAt(0).toUpperCase()}
                        </Text>
                      </View>
                    )}
                    <View style={styles.editBadge}>
                      <Text style={styles.editBadgeIcon}>✎</Text>
                    </View>
                  </TouchableOpacity>
                  <View style={styles.profileDetails}>
                    <Text style={styles.profileName}>{barber?.name || user.real_name || user.name}</Text>
                    <Text style={styles.barberRoleTag}>MASTER STYLIST</Text>
                    <Text style={styles.profileEmail}>{user.email || barber?.email}</Text>
                    {(user.phone || barber?.phone) && (
                      <Text style={styles.profilePhone}>{user.phone || barber?.phone}</Text>
                    )}
                  </View>
                  <TouchableOpacity
                    style={styles.editButton}
                    onPress={() => router.push('/barber/profile-edit')}
                    activeOpacity={0.8}
                  >
                    <Text style={styles.editButtonText}>Edit</Text>
                  </TouchableOpacity>
                </View>

                {(barber?.bio || user.bio) ? (
                  <View style={styles.bioBox}>
                    <Text style={styles.bioText} numberOfLines={3}>
                      "{barber?.bio || user.bio}"
                    </Text>
                  </View>
                ) : null}

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

            {/* Customer navigation */}
            {!isStaffDesk && (
              <>
                <Text style={[styles.customerSectionLabel, { color: colors.textMuted }]}>ACCOUNT</Text>
                <Card style={[styles.customerMenuCard, { backgroundColor: colors.surface, borderColor: colors.border }]} elevated>
                  <TouchableOpacity
                    style={styles.customerMenuItem}
                    onPress={() => router.push('/profile/notifications')}
                  >
                    <View style={[styles.customerMenuIcon, { backgroundColor: colors.primaryLight }]}><UIcon name="bell" size={17} color={colors.primary} /></View>
                    <View style={styles.customerMenuCopy}>
                      <Text style={[styles.customerMenuTitle, { color: colors.textPrimary }]}>Notifications</Text>
                      <Text style={[styles.customerMenuSubtitle, { color: colors.textMuted }]}>Stay current on your appointments</Text>
                    </View>
                    <Text style={[styles.customerMenuArrow, { color: colors.textMuted }]}>&rsaquo;</Text>
                  </TouchableOpacity>

                  <View style={[styles.customerDivider, { backgroundColor: colors.border }]} />

                  <TouchableOpacity
                    style={styles.customerMenuItem}
                    onPress={() => router.push('/profile/reviews')}
                  >
                    <View style={[styles.customerMenuIcon, { backgroundColor: colors.primaryLight }]}><UIcon name="star" size={17} color={colors.primary} /></View>
                    <View style={styles.customerMenuCopy}>
                      <Text style={[styles.customerMenuTitle, { color: colors.textPrimary }]}>Reviews</Text>
                      <Text style={[styles.customerMenuSubtitle, { color: colors.textMuted }]}>Your service feedback</Text>
                    </View>
                    <Text style={[styles.customerMenuArrow, { color: colors.textMuted }]}>&rsaquo;</Text>
                  </TouchableOpacity>

                  <View style={[styles.customerDivider, { backgroundColor: colors.border }]} />

                  <TouchableOpacity
                    style={styles.customerMenuItem}
                    onPress={() => router.push('/profile/settings')}
                  >
                    <View style={[styles.customerMenuIcon, { backgroundColor: colors.primaryLight }]}>
                      <Fingerprint size={17} color={colors.primary} />
                    </View>
                    <View style={styles.customerMenuCopy}>
                      <Text style={[styles.customerMenuTitle, { color: colors.textPrimary }]}>Fingerprint & Settings</Text>
                      <Text style={[styles.customerMenuSubtitle, { color: colors.textMuted }]}>Biometric sign-in, alerts & security</Text>
                    </View>
                    <Text style={[styles.customerMenuArrow, { color: colors.textMuted }]}>&rsaquo;</Text>
                  </TouchableOpacity>
                </Card>

                <Text style={[styles.customerSectionLabel, { color: colors.textMuted }]}>HELP & ACCESS</Text>
                <Card style={[styles.customerMenuCard, { backgroundColor: colors.surface, borderColor: colors.border }]} elevated>
                  <TouchableOpacity
                    style={styles.customerMenuItem}
                    onPress={handleContactSupport}
                  >
                    <View style={[styles.customerMenuIcon, { backgroundColor: colors.infoLight }]}><UIcon name="phoneCall" size={17} color={colors.info} /></View>
                    <View style={styles.customerMenuCopy}>
                      <Text style={[styles.customerMenuTitle, { color: colors.textPrimary }]}>Support & Concierge</Text>
                      <Text style={[styles.customerMenuSubtitle, { color: colors.textMuted }]}>Reach the Keffi lounge team</Text>
                    </View>
                    <Text style={[styles.customerMenuArrow, { color: colors.textMuted }]}>&rsaquo;</Text>
                  </TouchableOpacity>

                  <View style={[styles.customerDivider, { backgroundColor: colors.border }]} />

                  <TouchableOpacity
                    style={styles.customerMenuItem}
                    onPress={handleLogout}
                  >
                    <View style={[styles.customerMenuIcon, { backgroundColor: colors.errorLight }]}><UIcon name="cross" size={17} color={colors.error} /></View>
                    <View style={styles.customerMenuCopy}>
                      <Text style={[styles.customerMenuTitle, { color: colors.error }]}>Sign out</Text>
                      <Text style={[styles.customerMenuSubtitle, { color: colors.textMuted }]}>End this session on the device</Text>
                    </View>
                    <Text style={[styles.customerMenuArrow, { color: colors.error }]}>&rsaquo;</Text>
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

                {/* Group 2: Settings & Fingerprint Biometrics */}
                <Card style={styles.menuCard} elevated>
                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/settings')}
                  >
                    <View style={styles.menuItemLeft}>
                      <View style={{ width: 24, alignItems: 'center' }}>
                        <Fingerprint size={20} color={colors.primary} />
                      </View>
                      <View style={{ marginLeft: 8 }}>
                        <Text style={styles.menuText}>Fingerprint Login</Text>
                        <Text style={[styles.menuSubtitle, { color: colors.textMuted }]}>Barber staff biometrics</Text>
                      </View>
                    </View>
                    <Text style={styles.menuArrow}>&rarr;</Text>
                  </TouchableOpacity>
                  <View style={[styles.customerDivider, { backgroundColor: colors.border }]} />
                  <TouchableOpacity
                    style={styles.menuItem}
                    onPress={() => router.push('/profile/settings')}
                  >
                    <View style={styles.menuItemLeft}>
                      <Text style={styles.menuIcon}>⚙️</Text>
                      <Text style={styles.menuText}>Account Settings</Text>
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

      {/* Support & Concierge Action Dialog */}
      <ActionDialog
        visible={supportDialogVisible}
        title="Support & Concierge"
        message={`${CONFIG.BRANCH.NAME}\n${CONFIG.BRANCH.ADDRESS}\n\nPhone: ${CONFIG.BRANCH.PHONE}`}
        variant="primary"
        actions={[
          {
            label: '📞 Call Saloon',
            onPress: () => Linking.openURL(`tel:${CONFIG.BRANCH.PHONE}`),
          },
          {
            label: '🗺 Open Location',
            onPress: () => Linking.openURL(CONFIG.BRANCH.MAPS_URL),
          },
        ]}
        dismissLabel="Cancel"
        onDismiss={() => setSupportDialogVisible(false)}
      />
    </SafeAreaView>
  );
}

function createStyles(COLORS: ReturnType<typeof useAppTheme>['colors']) {
  return StyleSheet.create({
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
    padding: SPACING.md,
  },
  customerProfileRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  avatarWrapper: {
    position: 'relative',
    marginRight: 16,
  },
  avatarImage: {
    width: 60,
    height: 60,
    borderRadius: 30,
    borderWidth: 2,
    borderColor: COLORS.primary,
  },
  editBadge: {
    position: 'absolute',
    bottom: -2,
    right: -2,
    width: 20,
    height: 20,
    borderRadius: 10,
    backgroundColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1.5,
    borderColor: COLORS.background,
  },
  editBadgeIcon: {
    color: '#0A0A0C',
    fontSize: 10,
    fontWeight: '900',
  },
  editButton: {
    paddingVertical: 6,
    paddingHorizontal: 14,
    borderRadius: RADIUS.full,
    borderWidth: 1,
    borderColor: COLORS.primary,
    backgroundColor: 'rgba(212, 175, 55, 0.1)',
  },
  editButtonText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  bioBox: {
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  bioText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontStyle: 'italic',
    lineHeight: 18,
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
  menuSubtitle: {
    fontSize: FONTS.sizes.xs,
    marginTop: 1,
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
  customerProfileContent: {
    gap: SPACING.md,
  },
  customerHeroCard: {
    minHeight: 152,
    padding: SPACING.md,
    overflow: 'hidden',
  },
  customerHeroGlow: {
    position: 'absolute',
    width: 150,
    height: 150,
    borderRadius: 75,
    backgroundColor: 'rgba(212, 175, 55, 0.08)',
    right: -50,
    top: -70,
  },
  customerHeroTopRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  customerAvatarWrapper: {
    position: 'relative',
    marginRight: SPACING.md,
  },
  customerIdentity: {
    flex: 1,
  },
  customerEyebrow: {
    fontSize: 9,
    fontWeight: '800',
    letterSpacing: 1.2,
    marginBottom: 4,
  },
  customerHeroName: {
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
  },
  customerEditBadge: {
    position: 'absolute',
    bottom: -2,
    right: -2,
    width: 21,
    height: 21,
    borderRadius: 11,
    alignItems: 'center',
    justifyContent: 'center',
  },
  customerEditButton: {
    width: 36,
    height: 36,
    borderRadius: RADIUS.full,
    borderWidth: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  customerMemberLine: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
    borderTopWidth: 1,
    paddingTop: SPACING.sm,
    marginTop: SPACING.md,
  },
  customerMemberText: {
    flex: 1,
    fontSize: FONTS.sizes.xs,
    lineHeight: 17,
  },
  customerQuickGrid: {
    flexDirection: 'row',
    gap: SPACING.sm,
  },
  customerQuickTile: {
    flex: 1,
    minHeight: 92,
    borderRadius: RADIUS.md,
    borderWidth: 1,
    padding: SPACING.md,
    justifyContent: 'space-between',
  },
  customerQuickLabel: {
    fontSize: FONTS.sizes.sm,
    fontWeight: '800',
    marginTop: SPACING.sm,
  },
  customerQuickHint: {
    fontSize: FONTS.sizes.xs,
    marginTop: 2,
  },
  customerSectionLabel: {
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1.2,
    marginTop: SPACING.sm,
    marginBottom: -SPACING.xs,
  },
  customerMenuCard: {
    paddingVertical: 2,
    paddingHorizontal: SPACING.sm,
  },
  customerMenuItem: {
    minHeight: 70,
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: SPACING.xs,
    paddingVertical: SPACING.sm,
  },
  customerMenuIcon: {
    width: 36,
    height: 36,
    borderRadius: RADIUS.sm,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: SPACING.sm,
  },
  customerMenuCopy: {
    flex: 1,
    paddingRight: SPACING.sm,
  },
  customerMenuTitle: {
    fontSize: FONTS.sizes.sm,
    fontWeight: '800',
  },
  customerMenuSubtitle: {
    fontSize: FONTS.sizes.xs,
    marginTop: 3,
  },
  customerMenuArrow: {
    fontSize: 27,
    fontWeight: '300',
    lineHeight: 28,
  },
  customerDivider: {
    height: 1,
    marginLeft: 52,
  },
  });
}
