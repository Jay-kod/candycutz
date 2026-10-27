import React, { useEffect, useState } from 'react';
import {
  Image,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { ActionDialog, ActionDialogVariant } from '../../src/components/common/ActionDialog';
import { ProfileEditSkeleton } from '../../src/components/common/Skeleton';
import { accountApi } from '../../src/api/client';
import { FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAppTheme } from '../../src/hooks/useAppTheme';
import { getStorageUrl } from '../../src/constants/config';
import { useAuthStore } from '../../src/store/authStore';
import { useToastStore } from '../../src/store/toastStore';

export default function CustomerProfileEditScreen() {
  const router = useRouter();
  const { colors } = useAppTheme();
  const { user, refreshProfile } = useAuthStore();
  const showToast = useToastStore((state) => state.show);

  const [name, setName] = useState(user?.real_name || user?.name || '');
  const [username, setUsername] = useState(user?.username || '');
  const [email, setEmail] = useState(user?.email || '');
  const [phone, setPhone] = useState(user?.phone || '');
  const [bio, setBio] = useState((user as any)?.bio || '');
  const [avatarUri, setAvatarUri] = useState<string | null>(user?.avatar ? getStorageUrl(user.avatar) : null);
  const [selectedAvatar, setSelectedAvatar] = useState<string>();
  const [isSaving, setIsSaving] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [feedback, setFeedback] = useState<{
    variant: ActionDialogVariant;
    title: string;
    message: string;
  } | null>(null);

  // 60-day cooldown calculation
  const getUsernameCooldown = () => {
    if (!user?.last_username_change_at) {
      return { isLocked: false, daysRemaining: 0 };
    }
    const lastChange = new Date(user.last_username_change_at).getTime();
    if (isNaN(lastChange)) {
      return { isLocked: false, daysRemaining: 0 };
    }
    const diffDays = (Date.now() - lastChange) / (1000 * 60 * 60 * 24);
    if (diffDays < 60) {
      return {
        isLocked: true,
        daysRemaining: Math.ceil(60 - diffDays),
      };
    }
    return { isLocked: false, daysRemaining: 0 };
  };

  const { isLocked: isUsernameLocked, daysRemaining: usernameCooldownDays } = getUsernameCooldown();

  useEffect(() => {
    if (!user) return;
    setName(user.real_name || user.name || '');
    setUsername(user.username || '');
    setEmail(user.email || '');
    setPhone(user.phone || '');
    setBio((user as any)?.bio || '');
    setAvatarUri(user.avatar ? getStorageUrl(user.avatar) : null);
    setFieldErrors({});
  }, [user]);

  if (!user) {
    return (
      <SafeAreaView style={[styles.safeArea, { backgroundColor: colors.background }]}>
        <ProfileEditSkeleton />
      </SafeAreaView>
    );
  }

  const pickAvatar = async () => {
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permission.granted) {
      showToast({
        variant: 'warning',
        title: 'Permission needed',
        message: 'Allow photo library access to choose your profile picture.',
      });
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [1, 1],
      quality: 0.85,
    });

    if (result.canceled || !result.assets[0]?.uri) return;
    const uri = result.assets[0].uri;
    setAvatarUri(uri);
    setSelectedAvatar(uri);
  };

  const saveProfile = async () => {
    setFieldErrors({});

    const clientErrors: Record<string, string> = {};
    if (!name.trim()) {
      clientErrors.name = 'Full name is required.';
    }

    if (!email.trim()) {
      clientErrors.email = 'Email address is required.';
    }

    const cleanUsername = username.trim().replace(/^@/, '');
    if (!isUsernameLocked && cleanUsername && cleanUsername !== (user?.username || '')) {
      if (!/^[a-zA-Z0-9_-]{3,30}$/.test(cleanUsername)) {
        clientErrors.username = 'Username must be 3-30 characters (letters, numbers, _, -).';
      }
    }

    if (Object.keys(clientErrors).length > 0) {
      setFieldErrors(clientErrors);
      showToast({
        variant: 'warning',
        title: 'Check your inputs',
        message: Object.values(clientErrors)[0],
      });
      return;
    }

    setIsSaving(true);
    try {
      await accountApi.updateProfile({
        name: name.trim(),
        username: !isUsernameLocked && cleanUsername ? cleanUsername : undefined,
        email: email.trim(),
        phone: phone.trim(),
        bio: bio.trim(),
        avatarUri: selectedAvatar,
      });

      await refreshProfile();
      setFeedback({
        variant: 'success',
        title: 'Profile saved',
        message: 'Your updated profile details are now live.',
      });
    } catch (error: any) {
      const responseData = error.response?.data;
      const errorsObj = responseData?.errors || responseData?.error?.details;
      const extractedFieldErrors: Record<string, string> = {};
      let firstErrorMessage: string | null = null;

      if (errorsObj && typeof errorsObj === 'object') {
        for (const [key, val] of Object.entries(errorsObj)) {
          if (Array.isArray(val) && val.length > 0) {
            extractedFieldErrors[key] = String(val[0]);
            if (!firstErrorMessage) firstErrorMessage = String(val[0]);
          } else if (typeof val === 'string') {
            extractedFieldErrors[key] = val;
            if (!firstErrorMessage) firstErrorMessage = val;
          }
        }
      }

      setFieldErrors(extractedFieldErrors);

      const generalMsg = responseData?.message;
      const displayMessage = firstErrorMessage || (generalMsg && generalMsg !== 'The given data was invalid.' ? generalMsg : 'Please correct the highlighted fields and try again.');

      setFeedback({
        variant: 'danger',
        title: 'Could not save profile',
        message: displayMessage,
      });
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: colors.background }]} edges={['bottom']}>
      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <ScrollView contentContainerStyle={styles.container} keyboardShouldPersistTaps="handled">
          <View style={styles.headerRow}>
            <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
              <Text style={[styles.backArrow, { color: colors.textPrimary }]}>←</Text>
            </TouchableOpacity>
            <Text style={[styles.title, { color: colors.textPrimary }]}>Edit Profile</Text>
            <View style={styles.headerRight} />
          </View>

          <Card style={styles.card} elevated>
            {/* Avatar Section */}
            <View style={styles.avatarSection}>
              <TouchableOpacity style={styles.avatarPicker} onPress={pickAvatar} activeOpacity={0.8}>
                {avatarUri ? (
                  <Image source={{ uri: getStorageUrl(avatarUri) }} style={styles.avatarImage} />
                ) : (
                  <Text style={[styles.avatarInitial, { color: colors.primary }]}>
                    {(name || 'C').charAt(0).toUpperCase()}
                  </Text>
                )}
                <View style={styles.cameraOverlay}>
                  <Text style={styles.cameraIcon}>📷</Text>
                </View>
              </TouchableOpacity>
              <Text style={[styles.avatarHint, { color: colors.textMuted }]}>Tap to change photo (JPG, PNG or WebP)</Text>
            </View>

            {/* Input Fields */}
            <Field
              label="Full Name"
              value={name}
              onChangeText={(text) => {
                setName(text);
                if (fieldErrors.name) setFieldErrors((prev) => ({ ...prev, name: '' }));
              }}
              placeholder="Your full name"
              error={fieldErrors.name}
            />

            {/* Username Field with 60-day Cooldown */}
            <Field
              label="Username"
              value={username}
              onChangeText={(text) => {
                setUsername(text.replace(/^@/, ''));
                if (fieldErrors.username) setFieldErrors((prev) => ({ ...prev, username: '' }));
              }}
              placeholder="e.g. freshcuts_99"
              prefix="@"
              autoCapitalize="none"
              autoCorrect={false}
              editable={!isUsernameLocked}
              error={fieldErrors.username}
              badge={
                isUsernameLocked
                  ? { text: `Locked: ${usernameCooldownDays}d remaining`, variant: 'warning' }
                  : user?.last_username_change_at
                  ? { text: 'Can edit (60d passed)', variant: 'info' }
                  : undefined
              }
              hint={
                isUsernameLocked
                  ? `Usernames can only be edited once every 60 days. Next edit available in ${usernameCooldownDays} days.`
                  : 'Can be changed once every 60 days. Letters, numbers, hyphens and underscores only.'
              }
            />

            <Field
              label="Email Address"
              value={email}
              onChangeText={(text) => {
                setEmail(text);
                if (fieldErrors.email) setFieldErrors((prev) => ({ ...prev, email: '' }));
              }}
              placeholder="your.email@example.com"
              keyboardType="email-address"
              autoCapitalize="none"
              error={fieldErrors.email}
            />

            <Field
              label="Phone Number"
              value={phone}
              onChangeText={(text) => {
                setPhone(text);
                if (fieldErrors.phone) setFieldErrors((prev) => ({ ...prev, phone: '' }));
              }}
              placeholder="+234..."
              keyboardType="phone-pad"
              error={fieldErrors.phone}
            />

            <Field
              label="Bio & Grooming Preferences"
              value={bio}
              onChangeText={(text) => {
                setBio(text);
                if (fieldErrors.bio) setFieldErrors((prev) => ({ ...prev, bio: '' }));
              }}
              placeholder="e.g. Skin sensitivity notes, preferred fade style, or clipper allergies..."
              multiline
              hint="Help your stylist personalize your grooming experience"
              error={fieldErrors.bio}
            />

            <Button
              title="Save Profile"
              onPress={saveProfile}
              loading={isSaving}
              loadingTitle="Saving..."
              style={styles.saveButton}
            />
          </Card>
        </ScrollView>
      </KeyboardAvoidingView>
      <ActionDialog
        visible={feedback !== null}
        variant={feedback?.variant}
        title={feedback?.title || ''}
        message={feedback?.message || ''}
        actions={[
          {
            label: feedback?.variant === 'success' ? 'Done' : 'Try again',
            onPress: () => {
              const shouldGoBack = feedback?.variant === 'success';
              setFeedback(null);
              if (shouldGoBack) router.back();
            },
          },
        ]}
        onDismiss={() => setFeedback(null)}
      />
    </SafeAreaView>
  );
}

function Field({
  label,
  hint,
  error,
  multiline,
  prefix,
  badge,
  editable = true,
  ...props
}: {
  label: string;
  hint?: string;
  error?: string;
  multiline?: boolean;
  prefix?: string;
  badge?: { text: string; variant?: 'warning' | 'info' };
  editable?: boolean;
} & React.ComponentProps<typeof TextInput>) {
  const { colors } = useAppTheme();

  return (
    <View style={styles.field}>
      <View style={styles.labelRow}>
        <Text style={[styles.label, { color: colors.textSecondary }]}>{label}</Text>
        {badge && (
          <View style={[styles.badge, badge.variant === 'warning' ? styles.badgeWarning : styles.badgeInfo, { backgroundColor: badge.variant === 'warning' ? colors.warningLight : colors.infoLight, borderColor: badge.variant === 'warning' ? colors.warning : colors.info }]}>
            <Text style={[styles.badgeText, { color: badge.variant === 'warning' ? colors.warning : colors.info }]}>
              {badge.text}
            </Text>
          </View>
        )}
      </View>
      {hint && <Text style={[styles.hint, { color: colors.textMuted }]}>{hint}</Text>}
      <View style={[styles.inputWrapper, error ? styles.inputError : null, { backgroundColor: colors.surfaceHighlight, borderColor: error ? colors.danger : colors.border }, !editable && styles.inputDisabled, !editable && { backgroundColor: colors.surface }]}>
        {prefix && <Text style={[styles.prefixText, { color: colors.primary }]}>{prefix}</Text>}
        <TextInput
          {...props}
          editable={editable}
          multiline={multiline}
          style={[styles.input, { color: colors.textPrimary }, multiline && styles.multiline, prefix ? styles.inputWithPrefix : null]}
          placeholderTextColor={colors.textMuted}
        />
      </View>
      {error ? <Text style={[styles.errorText, { color: colors.danger }]}>{error}</Text> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  flex: { flex: 1 },
  container: { padding: SPACING.md, gap: SPACING.md, paddingBottom: SPACING.xl },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: SPACING.xs,
  },
  backBtn: { padding: SPACING.xs },
  backArrow: { fontSize: 22, fontWeight: '700' },
  title: { fontSize: FONTS.sizes.xl, fontWeight: '800' },
  headerRight: { width: 32 },
  card: { padding: SPACING.md },
  avatarSection: {
    alignItems: 'center',
    marginBottom: SPACING.lg,
  },
  avatarPicker: {
    width: 100,
    height: 100,
    borderRadius: 50,
    overflow: 'hidden',
    borderWidth: 2,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  avatarImage: { width: '100%', height: '100%' },
  avatarInitial: {
    fontSize: FONTS.sizes.hero,
    fontWeight: '800',
  },
  cameraOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    paddingVertical: 4,
    backgroundColor: 'rgba(0,0,0,0.65)',
    alignItems: 'center',
  },
  cameraIcon: {
    fontSize: 12,
  },
  avatarHint: {
    fontSize: FONTS.sizes.xs,
    marginTop: 8,
  },
  field: { marginBottom: SPACING.md },
  labelRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  label: { fontSize: FONTS.sizes.xs, fontWeight: '700' },
  badge: {
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: RADIUS.sm,
  },
  badgeWarning: {
    borderWidth: 1,
  },
  badgeInfo: {
    borderWidth: 1,
  },
  badgeText: {
    fontSize: 10,
    fontWeight: '700',
  },
  hint: { fontSize: 11, lineHeight: 15, marginBottom: 6 },
  inputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderRadius: RADIUS.md,
  },
  inputDisabled: {
    opacity: 0.65,
  },
  inputError: {
    borderWidth: 1,
  },
  prefixText: {
    fontWeight: '700',
    fontSize: FONTS.sizes.sm,
    paddingLeft: 12,
  },
  input: {
    flex: 1,
    padding: 12,
    fontSize: FONTS.sizes.sm,
  },
  inputWithPrefix: {
    paddingLeft: 6,
  },
  multiline: { minHeight: 90, textAlignVertical: 'top' },
  errorText: {
    fontSize: FONTS.sizes.xs,
    marginTop: 4,
    fontWeight: '600',
  },
  saveButton: { marginTop: SPACING.sm },
});
