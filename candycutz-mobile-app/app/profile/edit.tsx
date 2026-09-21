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
import { ProfileEditSkeleton } from '../../src/components/common/Skeleton';
import { accountApi } from '../../src/api/client';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';
import { useToastStore } from '../../src/store/toastStore';

export default function CustomerProfileEditScreen() {
  const router = useRouter();
  const { user, refreshProfile } = useAuthStore();
  const showToast = useToastStore((state) => state.show);

  const [name, setName] = useState(user?.real_name || user?.name || '');
  const [email, setEmail] = useState(user?.email || '');
  const [phone, setPhone] = useState(user?.phone || '');
  const [bio, setBio] = useState((user as any)?.bio || '');
  const [avatarUri, setAvatarUri] = useState<string | null>(user?.avatar || null);
  const [selectedAvatar, setSelectedAvatar] = useState<string>();
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    if (!user) return;
    setName(user.real_name || user.name || '');
    setEmail(user.email || '');
    setPhone(user.phone || '');
    setBio((user as any)?.bio || '');
    setAvatarUri(user.avatar || null);
  }, [user]);

  if (!user) {
    return (
      <SafeAreaView style={styles.safeArea}>
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
    if (!name.trim()) {
      showToast({
        variant: 'warning',
        title: 'Name required',
        message: 'Please enter your full name.',
      });
      return;
    }

    if (!email.trim()) {
      showToast({
        variant: 'warning',
        title: 'Email required',
        message: 'Please enter a valid email address.',
      });
      return;
    }

    setIsSaving(true);
    try {
      await accountApi.updateProfile({
        name: name.trim(),
        email: email.trim(),
        phone: phone.trim(),
        bio: bio.trim(),
        avatarUri: selectedAvatar,
      });

      await refreshProfile();
      showToast({
        variant: 'success',
        title: 'Profile Updated',
        message: 'Your profile details have been successfully saved.',
      });
      router.back();
    } catch (error: any) {
      showToast({
        variant: 'error',
        title: 'Could not save profile',
        message: error.response?.data?.message || 'Please check your connection and try again.',
      });
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['bottom']}>
      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <ScrollView contentContainerStyle={styles.container} keyboardShouldPersistTaps="handled">
          <View style={styles.headerRow}>
            <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
              <Text style={styles.backArrow}>←</Text>
            </TouchableOpacity>
            <Text style={styles.title}>Edit Profile</Text>
            <View style={styles.headerRight} />
          </View>

          <Card style={styles.card} elevated>
            {/* Avatar Section */}
            <View style={styles.avatarSection}>
              <TouchableOpacity style={styles.avatarPicker} onPress={pickAvatar} activeOpacity={0.8}>
                {avatarUri ? (
                  <Image source={{ uri: avatarUri }} style={styles.avatarImage} />
                ) : (
                  <Text style={styles.avatarInitial}>
                    {(name || 'C').charAt(0).toUpperCase()}
                  </Text>
                )}
                <View style={styles.cameraOverlay}>
                  <Text style={styles.cameraIcon}>📷</Text>
                </View>
              </TouchableOpacity>
              <Text style={styles.avatarHint}>Tap to change photo (JPG, PNG or WebP)</Text>
            </View>

            {/* Input Fields */}
            <Field label="Full Name" value={name} onChangeText={setName} placeholder="Your full name" />
            <Field
              label="Email Address"
              value={email}
              onChangeText={setEmail}
              placeholder="your.email@example.com"
              keyboardType="email-address"
              autoCapitalize="none"
            />
            <Field
              label="Phone Number"
              value={phone}
              onChangeText={setPhone}
              placeholder="+234..."
              keyboardType="phone-pad"
            />
            <Field
              label="Bio & Grooming Preferences"
              value={bio}
              onChangeText={setBio}
              placeholder="e.g. Skin sensitivity notes, preferred fade style, or clipper allergies..."
              multiline
              hint="Help your stylist personalize your grooming experience"
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
    </SafeAreaView>
  );
}

function Field({
  label,
  hint,
  multiline,
  ...props
}: { label: string; hint?: string; multiline?: boolean } & React.ComponentProps<typeof TextInput>) {
  return (
    <View style={styles.field}>
      <Text style={styles.label}>{label}</Text>
      {hint && <Text style={styles.hint}>{hint}</Text>}
      <TextInput
        {...props}
        multiline={multiline}
        style={[styles.input, multiline && styles.multiline]}
        placeholderTextColor={COLORS.textMuted}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: COLORS.background },
  flex: { flex: 1 },
  container: { padding: SPACING.md, gap: SPACING.md, paddingBottom: SPACING.xl },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: SPACING.xs,
  },
  backBtn: { padding: SPACING.xs },
  backArrow: { color: COLORS.textPrimary, fontSize: 22, fontWeight: '700' },
  title: { color: COLORS.textPrimary, fontSize: FONTS.sizes.xl, fontWeight: '800' },
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
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 2,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  avatarImage: { width: '100%', height: '100%' },
  avatarInitial: {
    color: COLORS.primary,
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
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
    marginTop: 8,
  },
  field: { marginBottom: SPACING.md },
  label: { color: COLORS.textSecondary, fontSize: FONTS.sizes.xs, fontWeight: '700', marginBottom: 6 },
  hint: { color: COLORS.textMuted, fontSize: 10, marginTop: -4, marginBottom: 5 },
  input: {
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    padding: 12,
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
  },
  multiline: { minHeight: 90, textAlignVertical: 'top' },
  saveButton: { marginTop: SPACING.sm },
});
