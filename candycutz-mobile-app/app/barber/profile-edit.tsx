import React, { useEffect, useState } from 'react';
import { Alert, Image, KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { LoadingState } from '../../src/components/common/LoadingState';
import { barbersApi } from '../../src/api/client';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';

export default function BarberProfileEditScreen() {
  const router = useRouter();
  const { user, barber, refreshProfile } = useAuthStore();
  const [name, setName] = useState(user?.name || '');
  const [username, setUsername] = useState(user?.username || '');
  const [phone, setPhone] = useState(user?.phone || barber?.phone || '');
  const [experience, setExperience] = useState(String(barber?.experience_years || ''));
  const [bio, setBio] = useState(barber?.bio || '');
  const [specialties, setSpecialties] = useState((barber?.specialties || []).join(', '));
  const [instagramUrl, setInstagramUrl] = useState(barber?.instagram_url || '');
  const [profileImage, setProfileImage] = useState(barber?.avatar_url || barber?.avatar || null);
  const [coverImage, setCoverImage] = useState(barber?.cover_image_url || barber?.cover_image || null);
  const [selectedProfileImage, setSelectedProfileImage] = useState<string>();
  const [selectedCoverImage, setSelectedCoverImage] = useState<string>();
  const [isSaving, setIsSaving] = useState(false);
  const [isChangingUsername, setIsChangingUsername] = useState(false);

  useEffect(() => {
    if (!user || !barber) return;
    setName(user.name || '');
    setUsername(user.username || '');
    setPhone(user.phone || barber.phone || '');
    setExperience(String(barber.experience_years || ''));
    setBio(barber.bio || '');
    setSpecialties((barber.specialties || []).join(', '));
    setInstagramUrl(barber.instagram_url || '');
    setProfileImage(barber.avatar_url || barber.avatar || null);
    setCoverImage(barber.cover_image_url || barber.cover_image || null);
  }, [barber, user]);

  if (!user || !barber) return <LoadingState message="Loading your barber profile" />;

  const pickImage = async (kind: 'profile' | 'cover') => {
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permission.granted) {
      Alert.alert('Permission needed', 'Allow photo access to choose an image for your profile.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: kind === 'cover' ? [16, 7] : [1, 1],
      quality: 0.9,
    });

    if (result.canceled || !result.assets[0]?.uri) return;
    const uri = result.assets[0].uri;
    if (kind === 'profile') {
      setProfileImage(uri);
      setSelectedProfileImage(uri);
    } else {
      setCoverImage(uri);
      setSelectedCoverImage(uri);
    }
  };

  const saveProfile = async () => {
    const experienceYears = Number(experience);
    if (!Number.isInteger(experienceYears) || experienceYears < 0 || experienceYears > 80) {
      Alert.alert('Check experience', 'Enter a whole number between 0 and 80 years.');
      return;
    }

    setIsSaving(true);
    try {
      const payload = {
        name: name.trim(),
        phone: phone.trim(),
        bio: bio.trim() || null,
        experience_years: experienceYears,
        specialties: specialties.split(',').map((item) => item.trim()).filter(Boolean),
        instagram_url: instagramUrl.trim() || null,
      };
      if (selectedProfileImage || selectedCoverImage) {
        await barbersApi.updateAccountWithImages({ ...payload, avatarUri: selectedProfileImage, coverImageUri: selectedCoverImage });
      } else {
        await barbersApi.updateAccount(payload);
      }
      await refreshProfile();
      Alert.alert('Profile updated', 'Your barber profile has been saved.');
    } catch (error: any) {
      Alert.alert('Could not save profile', error.response?.data?.message || 'Please try again.');
    } finally {
      setIsSaving(false);
    }
  };

  const changeUsername = async () => {
    setIsChangingUsername(true);
    try {
      await barbersApi.updateUsername(username.trim());
      await refreshProfile();
      Alert.alert('Username updated', 'Your username has been changed.');
    } catch (error: any) {
      Alert.alert('Could not change username', error.response?.data?.message || 'Username changes are limited to once every 30 days.');
    } finally {
      setIsChangingUsername(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['bottom']}>
      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <ScrollView contentContainerStyle={styles.container} keyboardShouldPersistTaps="handled">
          <Text style={styles.title}>Edit barber profile</Text>
          <Text style={styles.subtitle}>Keep your public profile accurate for customers.</Text>

          <Card style={styles.card} elevated>
            <Text style={styles.sectionTitle}>Profile images</Text>
            <TouchableOpacity style={styles.coverPicker} onPress={() => pickImage('cover')} activeOpacity={0.8}>
              {coverImage ? <Image source={{ uri: coverImage }} style={styles.coverImage} /> : <Text style={styles.imagePlaceholder}>Add cover image</Text>}
              <View style={styles.imageOverlay}><Text style={styles.imageOverlayText}>Change cover</Text></View>
            </TouchableOpacity>
            <TouchableOpacity style={styles.avatarPicker} onPress={() => pickImage('profile')} activeOpacity={0.8}>
              {profileImage ? <Image source={{ uri: profileImage }} style={styles.avatarImage} /> : <Text style={styles.imagePlaceholder}>Add photo</Text>}
              <View style={styles.imageOverlay}><Text style={styles.imageOverlayText}>Change photo</Text></View>
            </TouchableOpacity>
            <Text style={styles.imageHint}>JPG, PNG, or WEBP up to 5MB.</Text>
            <Text style={styles.sectionTitle}>About you</Text>
            <Field label="Display name" value={name} onChangeText={setName} />
            <Field label="Mobile number" value={phone} onChangeText={setPhone} keyboardType="phone-pad" />
            <Field label="Years of experience" value={experience} onChangeText={setExperience} keyboardType="number-pad" />
            <Field label="Specialties" hint="Separate specialties with commas" value={specialties} onChangeText={setSpecialties} />
            <Field label="Bio" value={bio} onChangeText={setBio} multiline />
            <Field label="Instagram URL" value={instagramUrl} onChangeText={setInstagramUrl} autoCapitalize="none" />
            <Button title="Save profile" onPress={saveProfile} loading={isSaving} loadingTitle="Saving..." style={styles.saveButton} />
          </Card>

          <Card style={styles.card}>
            <Text style={styles.sectionTitle}>Username</Text>
            <Text style={styles.helper}>You can change your @username once every 30 days.</Text>
            <Field label="@username" value={username} onChangeText={setUsername} autoCapitalize="none" />
            <Button title="Update username" variant="outline" onPress={changeUsername} loading={isChangingUsername} loadingTitle="Updating..." />
          </Card>

          <Button title="Done" variant="ghost" onPress={() => router.back()} />
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

function Field({ label, hint, multiline, ...props }: { label: string; hint?: string; multiline?: boolean } & React.ComponentProps<typeof TextInput>) {
  return (
    <View style={styles.field}>
      <Text style={styles.label}>{label}</Text>
      {hint && <Text style={styles.hint}>{hint}</Text>}
      <TextInput {...props} multiline={multiline} style={[styles.input, multiline && styles.multiline]} placeholderTextColor={COLORS.textMuted} />
    </View>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: COLORS.background },
  flex: { flex: 1 },
  container: { padding: SPACING.md, gap: SPACING.md, paddingBottom: SPACING.xl },
  title: { color: COLORS.textPrimary, fontSize: FONTS.sizes.xxl, fontWeight: '800' },
  subtitle: { color: COLORS.textSecondary, fontSize: FONTS.sizes.sm, marginTop: -8 },
  card: { padding: SPACING.md },
  sectionTitle: { color: COLORS.textPrimary, fontSize: FONTS.sizes.lg, fontWeight: '800', marginBottom: SPACING.md },
  coverPicker: { height: 150, borderRadius: RADIUS.md, overflow: 'hidden', backgroundColor: COLORS.surfaceHighlight, borderWidth: 1, borderColor: COLORS.border, alignItems: 'center', justifyContent: 'center', marginBottom: SPACING.md },
  coverImage: { width: '100%', height: '100%' },
  avatarPicker: { width: 112, height: 112, borderRadius: 56, overflow: 'hidden', backgroundColor: COLORS.surfaceHighlight, borderWidth: 2, borderColor: COLORS.primary, alignItems: 'center', justifyContent: 'center', marginBottom: SPACING.sm },
  avatarImage: { width: '100%', height: '100%' },
  imagePlaceholder: { color: COLORS.textMuted, fontSize: FONTS.sizes.sm },
  imageOverlay: { position: 'absolute', bottom: 0, left: 0, right: 0, paddingVertical: 7, backgroundColor: 'rgba(0,0,0,0.65)', alignItems: 'center' },
  imageOverlayText: { color: COLORS.textPrimary, fontSize: FONTS.sizes.xs, fontWeight: '700' },
  imageHint: { color: COLORS.textMuted, fontSize: FONTS.sizes.xs, marginBottom: SPACING.md },
  helper: { color: COLORS.textMuted, fontSize: FONTS.sizes.xs, lineHeight: 18, marginTop: -8, marginBottom: SPACING.md },
  field: { marginBottom: SPACING.md },
  label: { color: COLORS.textSecondary, fontSize: FONTS.sizes.xs, fontWeight: '700', marginBottom: 6 },
  hint: { color: COLORS.textMuted, fontSize: 10, marginTop: -4, marginBottom: 5 },
  input: { backgroundColor: COLORS.surfaceHighlight, borderWidth: 1, borderColor: COLORS.border, borderRadius: RADIUS.md, padding: 12, color: COLORS.textPrimary, fontSize: FONTS.sizes.sm },
  multiline: { minHeight: 94, textAlignVertical: 'top' },
  saveButton: { marginTop: SPACING.sm },
});