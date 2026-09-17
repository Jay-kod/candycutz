import React, { useState } from 'react';
import {
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Image,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Eye, EyeOff } from 'lucide-react-native';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { GoogleLogo } from '../../src/components/common/GoogleLogo';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';

export default function LoginScreen() {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const { login, isLoading, error } = useAuthStore();

  const [activeRoleTab, setActiveRoleTab] = useState<'customer' | 'barber'>('customer');
  const [identity, setIdentity] = useState('');
  const [password, setPassword] = useState('');
  const [isPasswordVisible, setIsPasswordVisible] = useState(false);

  const handleLogin = async () => {
    if (!identity.trim() || !password) return;
    const success = await login(identity.trim(), password);
    if (success) {
      if (router.canGoBack()) {
        router.back();
      } else {
        router.replace('/(tabs)');
      }
    }
  };

  const handleDemoLogin = async (demoIdentity: string, demoPass: string) => {
    setIdentity(demoIdentity);
    setPassword(demoPass);
    const success = await login(demoIdentity, demoPass);
    if (success) {
      if (router.canGoBack()) {
        router.back();
      } else {
        router.replace('/(tabs)');
      }
    }
  };

  const handleGoogleLogin = () => {
    Alert.alert('Google Sign-In', 'Google sign-in will be available after the Google client is configured.');
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        keyboardVerticalOffset={insets.top}
        style={{ flex: 1 }}
      >
        <ScrollView
          contentContainerStyle={[styles.container, { paddingBottom: insets.bottom + SPACING.xl }]}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          <Card style={styles.card} elevated>
            <View style={styles.brandBlock}>
              <View style={styles.iconFrame}>
                <Image source={require('../../assets/icon.png')} style={styles.systemIcon} resizeMode="contain" />
              </View>
              <Text style={styles.brandName}>CandyCutz</Text>
              <Text style={styles.title}>Login</Text>
              <Text style={styles.subtitle}>Sign in to your account to continue</Text>
            </View>

            <View style={styles.roleTabsContainer}>
              <TouchableOpacity
                style={[styles.roleTab, activeRoleTab === 'customer' && styles.roleTabActive]}
                onPress={() => setActiveRoleTab('customer')}
              >
                <Text style={[styles.roleTabText, activeRoleTab === 'customer' && styles.roleTabTextActive]}>
                  Client / Customer
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.roleTab, activeRoleTab === 'barber' && styles.roleTabActive]}
                onPress={() => setActiveRoleTab('barber')}
              >
                <Text style={[styles.roleTabText, activeRoleTab === 'barber' && styles.roleTabTextActive]}>
                  Barber Staff Desk
                </Text>
              </TouchableOpacity>
            </View>

            {error && (
              <View style={styles.errorBox}>
                <Text style={styles.errorText}>{error}</Text>
              </View>
            )}

            {activeRoleTab === 'customer' ? (
              <View style={styles.demoBox}>
                <View style={styles.demoBadgeRow}>
                  <View style={styles.demoBadge}>
                    <Text style={styles.demoBadgeText}>CLIENT DEMO</Text>
                  </View>
                  <Text style={styles.demoSubtitle}>Test booking catalog & wallet</Text>
                </View>
                <Text style={styles.demoCredText}>
                  <Text style={styles.demoCredLabel}>Account: </Text>jay@candycutz.com
                </Text>
                <Text style={styles.demoCredText}>
                  <Text style={styles.demoCredLabel}>Password: </Text>customer123
                </Text>
                <Button
                  title="⚡ 1-Tap Customer Demo Login"
                  variant="outline"
                  onPress={() => handleDemoLogin('jay@candycutz.com', 'customer123')}
                  loading={isLoading}
                  style={styles.demoBtn}
                />
              </View>
            ) : (
              <View style={[styles.demoBox, styles.barberDemoBox]}>
                <View style={styles.demoBadgeRow}>
                  <View style={[styles.demoBadge, styles.barberDemoBadge]}>
                    <Text style={[styles.demoBadgeText, styles.barberDemoBadgeText]}>STAFF DEMO</Text>
                  </View>
                  <Text style={styles.demoSubtitle}>Test chair queue, timer & walk-ins</Text>
                </View>
                <Text style={styles.demoCredText}>
                  <Text style={styles.demoCredLabel}>Account: </Text>obo@candycutz.com
                </Text>
                <Text style={styles.demoCredText}>
                  <Text style={styles.demoCredLabel}>Password: </Text>barber123
                </Text>
                <Button
                  title="✂ 1-Tap Barber Staff Demo Login"
                  variant="outline"
                  onPress={() => handleDemoLogin('obo@candycutz.com', 'barber123')}
                  loading={isLoading}
                  style={styles.demoBtn}
                />
              </View>
            )}

            <View style={styles.dividerRow}>
              <View style={styles.dividerLine} />
              <Text style={styles.dividerText}>OR SIGN IN MANUALLY</Text>
              <View style={styles.dividerLine} />
            </View>

            <View style={styles.fieldGroup}>
              <Text style={styles.label}>Username or Email</Text>
              <TextInput
                style={styles.input}
                placeholder={activeRoleTab === 'customer' ? 'customer@candycutz.com' : 'marcus or staff@candycutz.com'}
                placeholderTextColor={COLORS.textMuted}
                autoCapitalize="none"
                autoCorrect={false}
                value={identity}
                onChangeText={setIdentity}
              />
            </View>

            <View style={styles.fieldGroup}>
              <View style={styles.passwordLabelRow}>
                <Text style={styles.label}>Password</Text>
                <TouchableOpacity onPress={() => router.push('/auth/forgot-password')}>
                  <Text style={styles.forgotPasswordLink}>Forgot?</Text>
                </TouchableOpacity>
              </View>
              <View style={styles.passwordInputRow}>
                <TextInput
                  style={[styles.input, styles.passwordInput]}
                  placeholder="••••••••"
                  placeholderTextColor={COLORS.textMuted}
                  secureTextEntry={!isPasswordVisible}
                  value={password}
                  onChangeText={setPassword}
                />
                <TouchableOpacity
                  accessibilityLabel={isPasswordVisible ? 'Hide password' : 'Show password'}
                  onPress={() => setIsPasswordVisible((visible) => !visible)}
                  style={styles.passwordToggle}
                >
                  {isPasswordVisible ? <EyeOff size={20} color={COLORS.textMuted} /> : <Eye size={20} color={COLORS.textMuted} />}
                </TouchableOpacity>
              </View>
            </View>

            <Button
              title={activeRoleTab === 'customer' ? 'Sign In as Customer' : 'Sign In to Staff Desk'}
              onPress={handleLogin}
              loading={isLoading}
              disabled={!identity || !password}
              style={styles.loginBtn}
            />

            <View style={styles.socialSection}>
              <View style={styles.dividerRow}>
                <View style={styles.dividerLine} />
                <Text style={styles.dividerText}>OR CONTINUE WITH</Text>
                <View style={styles.dividerLine} />
              </View>
              <TouchableOpacity
                accessibilityLabel="Continue with Google"
                onPress={handleGoogleLogin}
                style={styles.googleButton}
              >
                <View style={styles.googleLogoFrame}>
                  <GoogleLogo size={20} />
                </View>
                <Text style={styles.googleButtonText}>Continue with Google</Text>
              </TouchableOpacity>
            </View>

            <View style={styles.footerRow}>
              <Text style={styles.footerText}>Don't have an account? </Text>
              <TouchableOpacity onPress={() => router.replace('/auth/register')}>
                <Text style={styles.registerLink}>Sign Up</Text>
              </TouchableOpacity>
            </View>
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
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.lg,
    justifyContent: 'center',
    flexGrow: 1,
  },
  card: {
    width: '100%',
    maxWidth: 390,
    alignSelf: 'center',
    padding: SPACING.lg,
    borderRadius: RADIUS.lg,
  },
  brandBlock: {
    alignItems: 'center',
    marginBottom: SPACING.md,
  },
  iconFrame: {
    width: 64,
    height: 64,
    borderRadius: RADIUS.full,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    backgroundColor: COLORS.surface,
    marginBottom: SPACING.sm,
  },
  systemIcon: {
    width: 46,
    height: 46,
    borderRadius: RADIUS.full,
  },
  brandName: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xxl,
    fontWeight: '900',
    letterSpacing: 0.5,
    marginBottom: 2,
  },
  title: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
  },
  subtitle: {
    color: COLORS.textSecondary,
    fontSize: 11,
    textAlign: 'center',
    marginTop: 4,
    lineHeight: 18,
    paddingHorizontal: 8,
  },
  roleTabsContainer: {
    flexDirection: 'row',
    backgroundColor: COLORS.surfaceElevated,
    borderRadius: RADIUS.lg,
    padding: 4,
    marginBottom: SPACING.md,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  roleTab: {
    flex: 1,
    paddingVertical: 10,
    alignItems: 'center',
    borderRadius: RADIUS.md,
  },
  roleTabActive: {
    backgroundColor: COLORS.primary,
  },
  roleTabText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  roleTabTextActive: {
    color: '#0A0A0C',
    fontWeight: '800',
  },
  errorBox: {
    backgroundColor: COLORS.errorLight,
    borderColor: COLORS.error,
    borderWidth: 1,
    borderRadius: RADIUS.md,
    padding: 12,
    marginBottom: 16,
  },
  errorText: {
    color: COLORS.error,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
  },
  demoBox: {
    backgroundColor: 'rgba(212, 175, 55, 0.08)',
    borderColor: 'rgba(212, 175, 55, 0.35)',
    borderWidth: 1,
    borderRadius: RADIUS.md,
    padding: 12,
    marginBottom: 14,
  },
  barberDemoBox: {
    backgroundColor: 'rgba(235, 185, 90, 0.07)',
    borderColor: 'rgba(235, 185, 90, 0.45)',
  },
  demoBadgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 6,
    gap: 8,
  },
  demoBadge: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: RADIUS.full,
  },
  barberDemoBadge: {
    backgroundColor: COLORS.accent,
  },
  demoBadgeText: {
    color: COLORS.background,
    fontSize: 9,
    fontWeight: '900',
    letterSpacing: 0.5,
  },
  barberDemoBadgeText: {
    color: '#0A0A0C',
  },
  demoSubtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    flex: 1,
  },
  demoCredText: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xs,
    marginBottom: 4,
    fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace',
  },
  demoCredLabel: {
    color: COLORS.textMuted,
    fontWeight: '600',
  },
  demoBtn: {
    marginTop: 10,
  },
  dividerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 14,
  },
  dividerLine: {
    flex: 1,
    height: 1,
    backgroundColor: COLORS.border,
  },
  dividerText: {
    color: COLORS.textMuted,
    fontSize: 10,
    fontWeight: '700',
    letterSpacing: 1,
    paddingHorizontal: 12,
  },
  fieldGroup: {
    marginBottom: 12,
  },
  label: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 6,
  },
  passwordLabelRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  forgotPasswordLink: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
    marginBottom: 6,
  },
  input: {
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    padding: 13,
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
  },
  passwordInputRow: {
    position: 'relative',
    justifyContent: 'center',
  },
  passwordInput: {
    paddingRight: 48,
  },
  passwordToggle: {
    position: 'absolute',
    right: 12,
    padding: 6,
  },
  loginBtn: {
    marginTop: 8,
  },
  socialSection: {
    marginTop: SPACING.md,
  },
  googleButton: {
    minHeight: 48,
    borderRadius: RADIUS.md,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    backgroundColor: COLORS.surface,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: SPACING.md,
  },
  googleLogoFrame: {
    width: 26,
    height: 26,
    borderRadius: RADIUS.full,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFFFFF',
    marginRight: SPACING.sm,
  },
  googleButtonText: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  footerRow: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 20,
  },
  footerText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
  },
  registerLink: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
});
