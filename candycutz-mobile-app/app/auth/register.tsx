import React, { useEffect, useState } from 'react';
import {
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
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { getStorageUrl } from '../../src/constants/config';
import { mobileCmsStorage } from '../../src/utils/mobileCmsStorage';
import { useAuthStore } from '../../src/store/authStore';

export default function RegisterScreen() {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const { register, isLoading, error } = useAuthStore();

  const [name, setName] = useState('');
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loginBg, setLoginBg] = useState<string | null>(null);

  useEffect(() => {
    mobileCmsStorage.getStoredCms().then((cms) => {
      if (cms.loginBg) {
        setLoginBg(cms.loginBg);
      }
    });
  }, []);

  const handleRegister = async () => {
    if (!name.trim() || !username.trim() || !email.trim() || !password) return;
    const success = await register({
      name: name.trim(),
      username: username.trim().toLowerCase(),
      email: email.trim(),
      phone: phone.trim(),
      password,
      password_confirmation: passwordConfirmation || password,
    });
    if (success) {
      router.replace('/(tabs)');
    }
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
              <Text style={styles.title}>Create account</Text>
              <Text style={styles.subtitle}>Create your account to continue</Text>
            </View>

            {error && (
              <View style={styles.errorBox}>
                <Text style={styles.errorText}>{error}</Text>
              </View>
            )}

            <View style={styles.fieldGroup}>
              <Text style={styles.label}>Full Name</Text>
              <TextInput
                style={styles.input}
                placeholder="Joshua Adeleke"
                placeholderTextColor={COLORS.textMuted}
                value={name}
                onChangeText={setName}
              />
            </View>

            <View style={styles.fieldGroup}>
              <Text style={styles.label}>Username</Text>
              <TextInput
                style={styles.input}
                placeholder="joshadeleke"
                placeholderTextColor={COLORS.textMuted}
                autoCapitalize="none"
                autoCorrect={false}
                value={username}
                onChangeText={(val) => setUsername(val.replace(/\s+/g, '').toLowerCase())}
              />
            </View>

            <View style={styles.fieldGroup}>
              <Text style={styles.label}>Email Address</Text>
              <TextInput
                style={styles.input}
                placeholder="josh@example.com"
                placeholderTextColor={COLORS.textMuted}
                autoCapitalize="none"
                keyboardType="email-address"
                value={email}
                onChangeText={setEmail}
              />
            </View>

            <View style={styles.fieldGroup}>
              <Text style={styles.label}>Phone Number (Nigeria)</Text>
              <TextInput
                style={styles.input}
                placeholder="0812 345 6789"
                placeholderTextColor={COLORS.textMuted}
                keyboardType="phone-pad"
                value={phone}
                onChangeText={setPhone}
              />
            </View>

            <View style={styles.fieldGroup}>
              <Text style={styles.label}>Password</Text>
              <TextInput
                style={styles.input}
                placeholder="••••••••"
                placeholderTextColor={COLORS.textMuted}
                secureTextEntry
                value={password}
                onChangeText={setPassword}
              />
            </View>

            <View style={styles.fieldGroup}>
              <Text style={styles.label}>Confirm Password</Text>
              <TextInput
                style={styles.input}
                placeholder="••••••••"
                placeholderTextColor={COLORS.textMuted}
                secureTextEntry
                value={passwordConfirmation}
                onChangeText={setPasswordConfirmation}
              />
            </View>

            <Button
              title="Create VIP Account"
              onPress={handleRegister}
              loading={isLoading}
              disabled={!name || !username || !email || !password}
              style={styles.registerBtn}
            />

            <View style={styles.footerRow}>
              <Text style={styles.footerText}>Already have an account? </Text>
              <TouchableOpacity onPress={() => router.replace('/auth/login')}>
                <Text style={styles.loginLink}>Sign In</Text>
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
    lineHeight: 20,
    paddingHorizontal: 8,
  },
  errorBox: {
    backgroundColor: COLORS.errorLight,
    borderColor: COLORS.error,
    borderWidth: 1,
    borderRadius: RADIUS.md,
    padding: 12,
    marginBottom: 14,
  },
  errorText: {
    color: COLORS.error,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
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
  input: {
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    padding: 13,
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
  },
  registerBtn: {
    marginTop: 10,
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
  loginLink: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
});
