import React, { useState } from 'react';
import {
  ActivityIndicator,
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
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { authApi } from '../../src/api/client';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { GoogleLogo } from '../../src/components/common/GoogleLogo';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';

export default function ForgotPasswordScreen() {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const [email, setEmail] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [isSubmitted, setIsSubmitted] = useState(false);

  const handleSubmit = async () => {
    if (!email.trim()) {
      Alert.alert('Required', 'Please enter your registered email address.');
      return;
    }

    setIsLoading(true);
    try {
      await authApi.forgotPassword(email.trim());
      setIsSubmitted(true);
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Failed to send password reset link.';
      Alert.alert('Error', msg);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['top', 'bottom']}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={insets.top}
        style={styles.keyboardView}
      >
        <ScrollView
          contentContainerStyle={[styles.container, { paddingBottom: insets.bottom + SPACING.xl }]}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          <Card style={styles.card} elevated>
            <TouchableOpacity style={styles.closeButton} onPress={() => router.back()}>
              <Text style={styles.closeText}>✕</Text>
            </TouchableOpacity>

            <View style={styles.brandBlock}>
              <View style={styles.iconFrame}>
                <Image source={require('../../assets/icon.png')} style={styles.systemIcon} resizeMode="contain" />
              </View>
              <Text style={styles.brandName}>CandyCutz</Text>
              <Text style={styles.title}>Reset password</Text>
              <Text style={styles.subtitle}>We will help you get back into your account</Text>
            </View>

            {isSubmitted ? (
              <View style={styles.successBox}>
                <Text style={styles.successIcon}>✓</Text>
                <Text style={styles.successTitle}>Reset Email Sent</Text>
                <Text style={styles.successDesc}>
                  If an account exists for {email}, you will receive password reset instructions shortly. Please check your inbox.
                </Text>
                <Button
                  title="Return to Sign In"
                  onPress={() => router.back()}
                  style={{ width: '100%', marginTop: 20 }}
                />
              </View>
            ) : (
              <>
                <View style={styles.providerRow}>
                  <GoogleLogo size={18} />
                  <Text style={styles.providerText}>Secure account recovery</Text>
                </View>
                <View style={styles.inputGroup}>
                  <Text style={styles.inputLabel}>Email Address</Text>
                  <TextInput
                    style={styles.input}
                    placeholder="e.g. user@example.com"
                    placeholderTextColor={COLORS.textMuted}
                    value={email}
                    onChangeText={setEmail}
                    keyboardType="email-address"
                    autoCapitalize="none"
                    autoCorrect={false}
                  />
                </View>

                <Button
                  title={isLoading ? 'Sending Link...' : 'Send Reset Link'}
                  onPress={handleSubmit}
                  disabled={isLoading}
                  style={{ width: '100%', marginTop: 12 }}
                />
              </>
            )}
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
  keyboardView: {
    flex: 1,
  },
  container: {
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.lg,
    justifyContent: 'center',
    flexGrow: 1,
  },
  closeButton: {
    alignSelf: 'flex-end',
    padding: 4,
    marginBottom: 2,
  },
  closeText: {
    color: COLORS.textSecondary,
    fontSize: 20,
    fontWeight: '700',
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
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
    color: COLORS.textPrimary,
  },
  subtitle: {
    color: COLORS.textSecondary,
    fontSize: 11,
    textAlign: 'center',
    marginTop: 4,
    lineHeight: 18,
    paddingHorizontal: 8,
  },
  desc: {
    fontSize: FONTS.sizes.sm,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: SPACING.lg,
  },
  inputGroup: {
    marginBottom: SPACING.md,
  },
  inputLabel: {
    fontSize: FONTS.sizes.xs,
    color: COLORS.textSecondary,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  input: {
    backgroundColor: COLORS.surfaceHighlight,
    borderRadius: RADIUS.md,
    paddingHorizontal: SPACING.md,
    paddingVertical: 12,
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  providerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: SPACING.sm,
    marginBottom: SPACING.md,
  },
  providerText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
  },
  successBox: {
    alignItems: 'center',
    paddingVertical: SPACING.md,
  },
  successIcon: {
    fontSize: 40,
    color: COLORS.success,
    marginBottom: 12,
  },
  successTitle: {
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
    color: COLORS.textPrimary,
    marginBottom: 8,
  },
  successDesc: {
    fontSize: FONTS.sizes.sm,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
  },
});
