import React, { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { authApi } from '../../src/api/client';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';

export default function ForgotPasswordScreen() {
  const router = useRouter();
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
        style={styles.keyboardView}
      >
        <View style={styles.container}>
          <TouchableOpacity style={styles.closeButton} onPress={() => router.back()}>
            <Text style={styles.closeText}>✕</Text>
          </TouchableOpacity>

          <Card style={styles.card} elevated>
            <Text style={styles.title}>Reset Password</Text>
            <Text style={styles.desc}>
              Enter your registered email address and we will send you a secure link to reset your account password.
            </Text>

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
        </View>
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
    flex: 1,
    padding: SPACING.md,
    justifyContent: 'center',
  },
  closeButton: {
    alignSelf: 'flex-end',
    padding: 8,
    marginBottom: SPACING.sm,
  },
  closeText: {
    color: COLORS.textSecondary,
    fontSize: 20,
    fontWeight: '700',
  },
  card: {
    padding: SPACING.lg,
  },
  title: {
    fontSize: FONTS.sizes.xxl,
    fontWeight: '800',
    color: COLORS.textPrimary,
    marginBottom: 8,
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
