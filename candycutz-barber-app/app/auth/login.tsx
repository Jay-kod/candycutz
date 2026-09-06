import React, { useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useBarberAuthStore } from '../../src/store/barberAuthStore';

export default function BarberLoginScreen() {
  const router = useRouter();
  const { login, isLoading, error } = useBarberAuthStore();

  const [identity, setIdentity] = useState('');
  const [password, setPassword] = useState('');

  const handleLogin = async () => {
    if (!identity.trim() || !password) return;
    const success = await login(identity.trim(), password);
    if (success) {
      router.back();
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={{ flex: 1 }}
      >
        <ScrollView contentContainerStyle={styles.container}>
          <View style={styles.header}>
            <Text style={styles.logo}>CANDYCUTZ</Text>
            <Text style={styles.title}>Barber & Staff Portal</Text>
            <Text style={styles.subtitle}>
              Sign in to manage your chair, queue, schedule, and walk-in clients at the Keffi Flagship Saloon.
            </Text>
          </View>

          <Card style={styles.card} elevated>
            {error && (
              <View style={styles.errorBox}>
                <Text style={styles.errorText}>{error}</Text>
              </View>
            )}

            <View style={styles.fieldGroup}>
              <Text style={styles.label}>Staff Username or Email</Text>
              <TextInput
                style={styles.input}
                placeholder="e.g. master_barber or staff@candycutz.ng"
                placeholderTextColor={COLORS.textMuted}
                autoCapitalize="none"
                autoCorrect={false}
                value={identity}
                onChangeText={setIdentity}
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

            <Button
              title="Sign In to Staff Desk"
              onPress={handleLogin}
              loading={isLoading}
              disabled={!identity || !password}
              style={styles.loginBtn}
            />
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
    padding: SPACING.lg,
    justifyContent: 'center',
    flexGrow: 1,
  },
  header: {
    alignItems: 'center',
    marginBottom: SPACING.xl,
  },
  logo: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.hero,
    fontWeight: '900',
    letterSpacing: 2,
  },
  title: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
    marginTop: 8,
  },
  subtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    textAlign: 'center',
    marginTop: 4,
    lineHeight: 20,
    paddingHorizontal: 20,
  },
  card: {
    padding: SPACING.lg,
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
  fieldGroup: {
    marginBottom: 16,
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
    padding: 14,
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
  },
  loginBtn: {
    marginTop: 8,
  },
});
