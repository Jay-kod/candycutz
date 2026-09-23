import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  LayoutAnimation,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  UIManager,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card } from '../../src/components/common/Card';
import { ErrorScreen } from '../../src/components/common/ErrorScreen';
import { cmsApi } from '../../src/api/client';
import { FONTS, RADIUS, SPACING, ThemeColors } from '../../src/constants/theme';
import { useAppTheme } from '../../src/hooks/useAppTheme';

if (Platform.OS === 'android' && UIManager.setLayoutAnimationEnabledExperimental) {
  UIManager.setLayoutAnimationEnabledExperimental(true);
}

interface PolicySection {
  heading: string;
  body: string;
}

const DEFAULT_PRIVACY_SECTIONS: PolicySection[] = [
  {
    heading: '1. Introduction & Overview',
    body:
      'Welcome to CandyCutz. We respect your privacy and are committed to safeguarding your personal data. This privacy policy explains how we collect, process, and protect your information when you use our mobile application and salon booking services in Nigeria.',
  },
  {
    heading: '2. Personal Data We Collect',
    body:
      'We may collect and process the following categories of data:\n\n' +
      '• Identity Data: Full name, chosen @username, profile photo, and biography/grooming preferences.\n' +
      '• Contact Data: Phone number and email address for appointment confirmations and security verification.\n' +
      '• Booking & Service Records: Appointment dates, selected barbers, hairstyle packages, and booking history.\n' +
      '• Transaction Metadata: Transaction reference IDs, kobo amounts, and payment method indicators. Note: Full payment card details are tokenized securely by our PCI-DSS certified gateway (Paystack) and are never stored on CandyCutz servers.\n' +
      '• Device & Notification Data: Push notification tokens (Expo/FCM/APNs) to send booking reminders and status updates.',
  },
  {
    heading: '3. How We Use Your Information',
    body:
      'We strictly utilize your data to provide and improve your grooming experience:\n\n' +
      '• Facilitating seamless salon reservations and real-time walk-in queue management.\n' +
      '• Sending transactional SMS, email confirmations, and push notifications for upcoming bookings.\n' +
      '• Enabling personalized grooming preferences so your stylist knows your preferred clipper lengths and skin sensitivities.\n' +
      '• Preventing fraudulent transactions, double bookings, and unauthorized account access.',
  },
  {
    heading: '4. Data Security & Storage',
    body:
      'We employ enterprise-grade security controls including TLS/HTTPS encryption in transit, bcrypt hashed passwords, tokenized API sessions (Laravel Sanctum), and isolated relational databases. Access to personal client records is strictly restricted to authorized salon management personnel.',
  },
  {
    heading: '5. Account Deactivation & Data Erasure (App Store & Play Store Compliance)',
    body:
      'In accordance with Apple Guideline 5.1.1(v) and Google Play Data Safety policies, users have full autonomy over their account data:\n\n' +
      '• In-App Deactivation: You can initiate immediate account deactivation anytime within the app under Profile > Settings > Account Security > Deactivate Account.\n' +
      '• Data Deletion Request: You may also email privacy@candycutz.com to request permanent deletion of your profile, grooming history, and uploaded media.\n' +
      '• Statutory Retention: Note that certain financial audit records are retained strictly for the duration mandated by applicable Nigerian taxation regulations before being permanently expunged.',
  },
  {
    heading: '6. Push Notifications & Communication Choices',
    body:
      'You can customize your notification preferences at any time in Profile > Settings > Notifications. You can enable or disable push alerts for appointment reminders, promotional offers, and barber announcements.',
  },
  {
    heading: '7. Contacting Us About Privacy',
    body:
      'If you have questions about how your data is handled or wish to exercise your data subject rights, please reach out:\n\n' +
      '• Privacy Email: privacy@candycutz.com\n' +
      '• Data Protection Officer: CandyCutz Studio, Keffi, Nasarawa State, Nigeria',
  },
];

export default function PrivacyPolicyScreen() {
  const router = useRouter();
  const { colors } = useAppTheme();
  const styles = createStyles(colors);
  const [title, setTitle] = useState('Privacy Policy');
  const [sections, setSections] = useState<PolicySection[]>(DEFAULT_PRIVACY_SECTIONS);
  const [isLoading, setIsLoading] = useState(true);
  const [isError, setIsError] = useState(false);
  const [expandedIndices, setExpandedIndices] = useState<Record<number, boolean>>({
    0: true,
    1: true,
  });

  const loadPrivacy = async () => {
    setIsLoading(true);
    setIsError(false);
    try {
      const settings = await cmsApi.getSettings();
      const legal = settings.legal || settings.general || settings;

      if (legal?.privacy_title) {
        setTitle(legal.privacy_title);
      }

      if (legal?.privacy_sections) {
        try {
          const parsed = typeof legal.privacy_sections === 'string'
            ? JSON.parse(legal.privacy_sections)
            : legal.privacy_sections;
          if (Array.isArray(parsed) && parsed.length > 0) {
            setSections(parsed);
          }
        } catch {
          // Keep defaults if parse fails
        }
      }
    } catch {
      // Graceful fallback to default policy sections
      setIsError(false);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadPrivacy();
  }, []);

  const toggleSection = (index: number) => {
    LayoutAnimation.configureNext(LayoutAnimation.Presets.easeInEaseOut);
    setExpandedIndices((prev) => ({
      ...prev,
      [index]: !prev[index],
    }));
  };

  const expandAll = () => {
    LayoutAnimation.configureNext(LayoutAnimation.Presets.easeInEaseOut);
    const all: Record<number, boolean> = {};
    sections.forEach((_, i) => (all[i] = true));
    setExpandedIndices(all);
  };

  const collapseAll = () => {
    LayoutAnimation.configureNext(LayoutAnimation.Presets.easeInEaseOut);
    setExpandedIndices({});
  };

  if (isError) {
    return (
      <ErrorScreen
        variant="network"
        title="Could not load Privacy Policy"
        message="Please check your connection to load the latest Privacy Policy."
        onRetry={loadPrivacy}
        onGoBack={() => router.back()}
      />
    );
  }

  return (
    <SafeAreaView style={styles.safeArea} edges={['top', 'bottom']}>
      {/* Header Bar */}
      <View style={styles.headerBar}>
        <TouchableOpacity
          onPress={() => router.back()}
          style={styles.backButton}
          activeOpacity={0.7}
        >
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle} numberOfLines={1}>
          Legal & Policies
        </Text>
        <View style={styles.headerRight} />
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Hero Section */}
        <View style={styles.heroCard}>
          <View style={styles.badgeRow}>
            <View style={styles.legalBadge}>
              <Text style={styles.legalBadgeText}>Data Protection</Text>
            </View>
            <Text style={styles.updatedDate}>Version 2.0</Text>
          </View>
          <Text style={styles.heroTitle}>{title}</Text>
          <Text style={styles.heroSubtitle}>
            Your privacy is paramount. Discover how CandyCutz handles, encrypts, and respects your personal information.
          </Text>

          <View style={styles.toggleRow}>
            <TouchableOpacity onPress={expandAll} style={styles.toggleButton} activeOpacity={0.7}>
              <Text style={styles.toggleButtonText}>Expand All</Text>
            </TouchableOpacity>
            <Text style={styles.toggleDivider}>•</Text>
            <TouchableOpacity onPress={collapseAll} style={styles.toggleButton} activeOpacity={0.7}>
              <Text style={styles.toggleButtonText}>Collapse All</Text>
            </TouchableOpacity>
          </View>
        </View>

        {isLoading ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color={colors.primary} />
            <Text style={styles.loadingText}>Syncing privacy terms...</Text>
          </View>
        ) : (
          /* Accordion Sections */
          <View style={styles.sectionsContainer}>
            {sections.map((section, idx) => {
              const isExpanded = !!expandedIndices[idx];
              return (
                <Card key={idx} style={styles.sectionCard} elevated>
                  <TouchableOpacity
                    onPress={() => toggleSection(idx)}
                    style={styles.sectionHeader}
                    activeOpacity={0.8}
                  >
                    <Text style={styles.sectionHeading}>{section.heading}</Text>
                    <View style={[styles.chevronBadge, isExpanded && styles.chevronBadgeExpanded]}>
                      <Text style={styles.chevronText}>{isExpanded ? '▲' : '▼'}</Text>
                    </View>
                  </TouchableOpacity>

                  {isExpanded && (
                    <View style={styles.sectionBody}>
                      <Text style={styles.bodyText}>{section.body}</Text>
                    </View>
                  )}
                </Card>
              );
            })}
          </View>
        )}

        {/* Footer Note */}
        <View style={styles.footerNote}>
          <Text style={styles.footerNoteText}>
            Questions about our privacy practices? Contact us at{' '}
            <Text style={styles.footerNoteLink}>privacy@candycutz.com</Text>
          </Text>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const createStyles = (colors: ThemeColors) => StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: colors.background,
  },
  headerBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.xs,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  backButton: {
    padding: SPACING.xs,
  },
  backArrow: {
    color: colors.textPrimary,
    fontSize: 22,
    fontWeight: '700',
  },
  headerTitle: {
    color: colors.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '800',
  },
  headerRight: {
    width: 32,
  },
  scrollContent: {
    padding: SPACING.md,
    paddingBottom: SPACING.xxl,
  },
  heroCard: {
    backgroundColor: colors.surfaceHighlight,
    borderWidth: 1,
    borderColor: colors.primaryGlow,
    borderRadius: RADIUS.xl,
    padding: SPACING.lg,
    marginBottom: SPACING.md,
  },
  badgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: SPACING.xs,
  },
  legalBadge: {
    backgroundColor: colors.primaryLight,
    borderWidth: 1,
    borderColor: colors.primary,
    paddingHorizontal: 10,
    paddingVertical: 3,
    borderRadius: RADIUS.pill,
  },
  legalBadgeText: {
    color: colors.primary,
    fontSize: 10,
    fontWeight: '800',
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },
  updatedDate: {
    color: colors.textMuted,
    fontSize: FONTS.sizes.xs,
  },
  heroTitle: {
    fontSize: FONTS.sizes.xxl,
    fontWeight: '800',
    color: colors.textPrimary,
    marginBottom: SPACING.xs,
    letterSpacing: -0.5,
  },
  heroSubtitle: {
    fontSize: FONTS.sizes.sm,
    color: colors.textMuted,
    lineHeight: 20,
    marginBottom: SPACING.md,
  },
  toggleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderTopWidth: 1,
    borderTopColor: colors.border,
    paddingTop: SPACING.sm,
  },
  toggleButton: {
    paddingVertical: 2,
  },
  toggleButtonText: {
    color: colors.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  toggleDivider: {
    color: colors.textMuted,
    fontSize: 10,
  },
  loadingContainer: {
    paddingVertical: SPACING.xxl,
    alignItems: 'center',
    gap: SPACING.sm,
  },
  loadingText: {
    color: colors.textMuted,
    fontSize: FONTS.sizes.sm,
  },
  sectionsContainer: {
    gap: SPACING.sm,
  },
  sectionCard: {
    padding: 0,
    overflow: 'hidden',
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.md,
  },
  sectionHeading: {
    flex: 1,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
    color: colors.textPrimary,
    paddingRight: SPACING.sm,
  },
  chevronBadge: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: colors.surfaceHighlight,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  chevronBadgeExpanded: {
    backgroundColor: colors.primaryLight,
    borderColor: colors.primary,
  },
  chevronText: {
    fontSize: 10,
    color: colors.textSecondary,
    fontWeight: '700',
  },
  sectionBody: {
    paddingHorizontal: SPACING.md,
    paddingBottom: SPACING.md,
    paddingTop: 0,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  bodyText: {
    color: colors.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 22,
    marginTop: SPACING.sm,
  },
  footerNote: {
    marginTop: SPACING.lg,
    paddingHorizontal: SPACING.sm,
    alignItems: 'center',
  },
  footerNoteText: {
    color: colors.textMuted,
    fontSize: FONTS.sizes.xs,
    textAlign: 'center',
    lineHeight: 18,
  },
  footerNoteLink: {
    color: colors.primary,
    fontWeight: '700',
  },
});
