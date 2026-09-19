import React, { useEffect, useState } from 'react';
import {
  Alert,
  ImageBackground,
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
import { LinearGradient } from 'expo-linear-gradient';
import { Eye, EyeOff } from 'lucide-react-native';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { GoogleLogo } from '../../src/components/common/GoogleLogo';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { getStorageUrl } from '../../src/constants/config';
import { mobileCmsStorage } from '../../src/utils/mobileCmsStorage';
import { useAuthStore } from '../../src/store/authStore';

export default function LoginScreen() {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const { login, isLoading, error } = useAuthStore();

  const [identity, setIdentity] = useState('');
  const [password, setPassword] = useState('');
  const [isPasswordVisible, setIsPasswordVisible] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  const [demoRole, setDemoRole] = useState<'customer' | 'barber' | null>(null);
  const [loginBg, setLoginBg] = useState<string | null>(null);

  useEffect(() => {
    mobileCmsStorage.getStoredCms().then((cms) => {
      if (cms.loginBg) {
        setLoginBg(cms.loginBg);
      }
    });
  }, []);

  const handleLogin = async () => {
    if (!identity.trim() || !password) return;
    const success = await login(identity.trim(), password, rememberMe);
    if (success) {
      if (router.canGoBack()) {
        router.back();
      } else {
        router.replace('/(tabs)');
      }
    }
  };

  const handleDemoLogin = (role: 'customer' | 'barber') => {
    setDemoRole(role);
    setIdentity(role === 'customer' ? 'jay@candycutz.com' : 'obo@candycutz.com');
    setPassword(role === 'customer' ? 'customer123' : 'barber123');
  };

  const handleGoogleLogin = () => {
    Alert.alert('Google Sign-In', 'Google sign-in will be available after the Google client is configured.');
  };

  return (
    <View style={styles.rootContainer}>
      <ImageBackground
        source={loginBg ? { uri: getStorageUrl(loginBg) } : require('../../assets/images/splash-bg.jpg')}
        style={StyleSheet.absoluteFill}
        resizeMode="cover"
      >
        <LinearGradient
          colors={['rgba(10, 10, 12, 0.78)', 'rgba(10, 10, 12, 0.94)']}
          style={StyleSheet.absoluteFill}
        />
      </ImageBackground>

      <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        keyboardVerticalOffset={insets.top}
        style={{ flex: 1 }}
      >
        <ScrollView
          contentContainerStyle={[styles.container, { paddingBottom: insets.bottom + SPACING.sm }]}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          <Card style={styles.card} elevated>
            <View style={styles.brandBlock}>
              <View style={styles.iconFrame}>
                <Image source={require('../../assets/icon.png')} style={styles.systemIcon} resizeMode="contain" />
              </View>
              <Text style={styles.title}>Login</Text>
              <Text style={styles.subtitle}>Sign in to your account to continue</Text>
            </View>

            {error && (
              <View style={styles.errorBox}>
                <Text style={styles.errorText}>{error}</Text>
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
                placeholder="you@example.com"
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

            <TouchableOpacity
              accessibilityRole="checkbox"
              accessibilityState={{ checked: rememberMe }}
              onPress={() => setRememberMe((checked) => !checked)}
              style={styles.rememberMeRow}
            >
              <View style={[styles.checkbox, rememberMe && styles.checkboxChecked]}>
                {rememberMe && <Text style={styles.checkboxMark}>✓</Text>}
              </View>
              <Text style={styles.rememberMeText}>Remember me</Text>
            </TouchableOpacity>
              </View>
            </View>

            <Button
              title="Login"
              onPress={handleLogin}
              loading={isLoading}
              loadingTitle="Authenticating..."
              disabled={!identity || !password}
              style={styles.loginBtn}
            />

            <View style={styles.demoRow}>
              <Text style={styles.demoPrompt}>Need a quick look around?</Text>
              <TouchableOpacity
                onPress={() => handleDemoLogin('customer')}
                disabled={isLoading}
                accessibilityRole="button"
                accessibilityLabel="Demo customer"
              >
                <Text style={styles.demoLink}>Demo customer</Text>
              </TouchableOpacity>
              <Text style={styles.demoSeparator}>or</Text>
              <TouchableOpacity
                onPress={() => handleDemoLogin('barber')}
                disabled={isLoading}
                accessibilityRole="button"
                accessibilityLabel="Demo barber"
              >
                <Text style={styles.demoLink}>Demo barber</Text>
              </TouchableOpacity>
            </View>
            {demoRole && (
              <View style={styles.demoDetails}>
                <Text style={styles.demoDetailsLabel}>
                  {demoRole === 'customer' ? 'Customer demo' : 'Barber demo'}
                </Text>
                <Text style={styles.demoDetailsText}>
                  Email: {demoRole === 'customer' ? 'jay@candycutz.com' : 'obo@candycutz.com'}
                </Text>
                <Text style={styles.demoDetailsText}>
                  Password: {demoRole === 'customer' ? 'customer123' : 'barber123'}
                </Text>
              </View>
            )}

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
  </View>
  );
}

const styles = StyleSheet.create({
  rootContainer: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  safeArea: {
    flex: 1,
    backgroundColor: 'transparent',
  },
  container: {
    paddingHorizontal: SPACING.md,
    paddingTop: SPACING.xl,
    paddingBottom: SPACING.sm,
    justifyContent: 'flex-end',
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
  demoRow: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: SPACING.md,
  },
  demoPrompt: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
  },
  demoSeparator: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
    marginHorizontal: 5,
  },
  demoLink: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
    marginLeft: 4,
  },
  demoDetails: {
    marginTop: SPACING.sm,
    padding: SPACING.sm,
    borderRadius: RADIUS.sm,
    backgroundColor: COLORS.primaryLight,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  demoDetailsText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    textAlign: 'center',
    lineHeight: 18,
  },
  demoDetailsLabel: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
    marginBottom: 2,
    textAlign: 'center',
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
  rememberMeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    paddingVertical: 4,
    marginBottom: 4,
  },
  checkbox: {
    width: 20,
    height: 20,
    borderRadius: 4,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 8,
  },
  checkboxChecked: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  checkboxMark: {
    color: COLORS.background,
    fontSize: 14,
    fontWeight: '800',
    lineHeight: 16,
  },
  rememberMeText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
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
