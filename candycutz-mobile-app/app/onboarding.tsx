import React, { useRef, useState } from 'react';
import {
  Animated,
  Dimensions,
  FlatList,
  ImageBackground,
  NativeScrollEvent,
  NativeSyntheticEvent,
  Platform,
  Pressable,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { ArrowRight } from 'lucide-react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS, FONTS, RADIUS, SPACING } from '../src/constants/theme';
import { onboardingStorage } from '../src/utils/onboardingStorage';
import { mobileCmsStorage } from '../src/utils/mobileCmsStorage';
import { getStorageUrl } from '../src/constants/config';
import { useAuthStore } from '../src/store/authStore';

const { width: SCREEN_WIDTH, height: SCREEN_HEIGHT } = Dimensions.get('window');

interface SlideItem {
  id: string;
  image: any;
  title: string;
  subtitle: string;
}

const SLIDES: SlideItem[] = [
  {
    id: '1',
    image: require('../assets/images/onboarding-1.jpg'),
    title: 'Master Barbers at Your Service',
    subtitle: 'Experience world-class precision cuts, beard sculpting, and luxury grooming crafted to your personal style.',
  },
  {
    id: '2',
    image: require('../assets/images/onboarding-2.jpg'),
    title: 'Effortless Real-Time Booking',
    subtitle: 'Browse our full service menu, view real-time barber availability, and secure your appointment in seconds.',
  },
  {
    id: '3',
    image: require('../assets/images/onboarding-3.png'),
    title: 'A Sharper Standard, Every Day',
    subtitle: 'Elevate your confidence with CandyCutz. Step in for a fresh cut and walk out feeling your absolute best.',
  },
];

export default function OnboardingScreen() {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [customOnboardingBg, setCustomOnboardingBg] = useState<string | null>(null);
  const flatListRef = useRef<FlatList>(null);
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);

  React.useEffect(() => {
    mobileCmsStorage.getStoredCms().then((cms) => {
      if (cms.onboardingBg) {
        setCustomOnboardingBg(cms.onboardingBg);
      }
    });
  }, []);

  const handleFinishOnboarding = async (destination: 'login' | 'register' = 'login') => {
    await onboardingStorage.setHasSeenOnboarding(true);
    if (isAuthenticated) {
      router.replace('/(tabs)');
    } else {
      router.replace(`/auth/${destination}`);
    }
  };

  const handleNext = async () => {
    if (currentIndex < SLIDES.length - 1) {
      const nextIndex = currentIndex + 1;
      flatListRef.current?.scrollToIndex({ index: nextIndex, animated: true });
      setCurrentIndex(nextIndex);
    } else {
      await handleFinishOnboarding();
    }
  };

  const handleScroll = (event: NativeSyntheticEvent<NativeScrollEvent>) => {
    const offsetX = event.nativeEvent.contentOffset.x;
    const index = Math.round(offsetX / SCREEN_WIDTH);
    if (index !== currentIndex && index >= 0 && index < SLIDES.length) {
      setCurrentIndex(index);
    }
  };

  return (
    <View style={styles.container}>
      <FlatList
        ref={flatListRef}
        data={SLIDES}
        keyExtractor={(item) => item.id}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        bounces={false}
        onMomentumScrollEnd={handleScroll}
        renderItem={({ item }) => (
          <View style={[styles.slideContainer, { width: SCREEN_WIDTH, height: SCREEN_HEIGHT }]}>
            <ImageBackground
              source={customOnboardingBg ? { uri: getStorageUrl(customOnboardingBg) } : item.image}
              style={styles.slideImage}
              resizeMode="cover"
            >
              {/* Bottom dark gradient overlay */}
              <LinearGradient
                colors={[
                  'rgba(10, 10, 12, 0.0)',
                  'rgba(10, 10, 12, 0.4)',
                  'rgba(10, 10, 12, 0.88)',
                  'rgba(10, 10, 12, 0.98)',
                ]}
                locations={[0, 0.45, 0.75, 1]}
                style={styles.gradientOverlay}
              />

              {/* Text content */}
              <View style={[styles.contentContainer, { paddingBottom: insets.bottom + 100 }]}>
                <Text style={styles.title}>{item.title}</Text>
                <Text style={styles.subtitle}>{item.subtitle}</Text>
              </View>
            </ImageBackground>
          </View>
        )}
      />

      {/* Top Skip Button */}
      <View style={[styles.topBar, { top: insets.top + (Platform.OS === 'ios' ? 12 : 16) }]}>
        <Pressable
          onPress={() => handleFinishOnboarding()}
          style={({ pressed }) => [styles.skipButton, pressed && styles.skipButtonPressed]}
          accessibilityRole="button"
          accessibilityLabel="Skip onboarding"
        >
          <Text style={styles.skipText}>Skip</Text>
        </Pressable>
      </View>

      {/* Bottom Controls (Pagination & Action Buttons) */}
      <View style={[styles.bottomControls, { bottom: insets.bottom + 28 }]}>
        {/* Pagination Dots */}
        <View style={styles.paginationRow}>
          {SLIDES.map((_, index) => {
            const isActive = index === currentIndex;
            return (
              <View
                key={index}
                style={[
                  styles.dot,
                  isActive ? styles.dotActive : styles.dotInactive,
                ]}
              />
            );
          })}
        </View>

        {currentIndex === SLIDES.length - 1 ? (
          <View style={styles.authActions}>
            <Pressable
              onPress={() => handleFinishOnboarding('login')}
              style={({ pressed }) => [styles.authButton, styles.loginButton, pressed && styles.authButtonPressed]}
              accessibilityRole="button"
              accessibilityLabel="Sign in"
            >
              <Text style={styles.loginButtonText}>Sign in</Text>
            </Pressable>
            <Pressable
              onPress={() => handleFinishOnboarding('register')}
              style={({ pressed }) => [styles.authButton, styles.registerButton, pressed && styles.authButtonPressed]}
              accessibilityRole="button"
              accessibilityLabel="Create account"
            >
              <Text style={styles.registerButtonText}>Create account</Text>
            </Pressable>
          </View>
        ) : (
          <Pressable
            onPress={handleNext}
            style={({ pressed }) => [styles.actionButton, pressed && styles.actionButtonPressed]}
            accessibilityRole="button"
            accessibilityLabel="Next slide"
          >
            <ArrowRight size={22} color="#121217" strokeWidth={2.5} />
          </Pressable>
        )}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  slideContainer: {
    overflow: 'hidden',
  },
  slideImage: {
    flex: 1,
    justifyContent: 'flex-end',
  },
  gradientOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
  },
  contentContainer: {
    paddingHorizontal: 28,
    alignItems: 'center',
  },
  title: {
    color: COLORS.textPrimary,
    fontSize: 26,
    fontWeight: '700',
    textAlign: 'center',
    lineHeight: 34,
    marginBottom: 14,
    letterSpacing: 0.2,
  },
  subtitle: {
    color: COLORS.textSecondary,
    fontSize: 14,
    lineHeight: 22,
    textAlign: 'center',
    paddingHorizontal: 10,
    opacity: 0.85,
  },
  topBar: {
    position: 'absolute',
    right: 20,
    zIndex: 10,
  },
  skipButton: {
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 20,
    backgroundColor: 'rgba(26, 26, 34, 0.55)',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
  },
  skipButtonPressed: {
    backgroundColor: 'rgba(212, 175, 55, 0.2)',
    borderColor: COLORS.primary,
  },
  skipText: {
    color: '#D1D5DB',
    fontSize: 13,
    fontWeight: '600',
    letterSpacing: 0.5,
  },
  bottomControls: {
    position: 'absolute',
    left: 28,
    right: 28,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    zIndex: 10,
  },
  paginationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  dot: {
    height: 4,
    borderRadius: 2,
  },
  dotActive: {
    width: 24,
    backgroundColor: '#E5BA73',
  },
  dotInactive: {
    width: 6,
    borderRadius: 3,
    backgroundColor: 'rgba(255, 255, 255, 0.25)',
  },
  actionButton: {
    width: 58,
    height: 58,
    borderRadius: 29,
    backgroundColor: '#E5BA73',
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#E5BA73',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.35,
    shadowRadius: 8,
    elevation: 6,
  },
  actionButtonPressed: {
    transform: [{ scale: 0.94 }],
    backgroundColor: '#D4AF37',
  },
  authActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  authButton: {
    minWidth: 126,
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
    borderRadius: RADIUS.md,
  },
  loginButton: {
    backgroundColor: '#E5BA73',
  },
  registerButton: {
    borderWidth: 1,
    borderColor: '#E5BA73',
    backgroundColor: 'rgba(26, 26, 34, 0.78)',
  },
  authButtonPressed: {
    opacity: 0.78,
    transform: [{ scale: 0.97 }],
  },
  loginButtonText: {
    color: '#121217',
    fontSize: 13,
    fontWeight: '800',
  },
  registerButtonText: {
    color: '#E5BA73',
    fontSize: 13,
    fontWeight: '800',
  },
});
