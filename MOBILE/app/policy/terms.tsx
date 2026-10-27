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

const DEFAULT_TERMS_SECTIONS: PolicySection[] = [
  {
    heading: '1. Agreement to Terms',
    body:
      'By accessing or using the CandyCutz mobile application, booking services, or web platform, you agree to be bound by these Terms of Service. If you disagree with any part of these terms, you must discontinue using our services.',
  },
  {
    heading: '2. Booking and Appointments',
    body:
      'We strive to provide premium grooming experiences and respect every client\'s schedule:\n\n' +
      '• Advance Booking: Appointments can be scheduled up to 30 days in advance.\n' +
      '• Cancellation & Rescheduling: We require at least 24 hours notice for any cancellation or rescheduling request.\n' +
      '• Punctuality: Please arrive 5 minutes before your scheduled appointment. Late arrivals exceeding 15 minutes may result in an abbreviated service or cancellation.\n' +
      '• No-Shows: Unexcused no-shows may incur a fee of up to 50% of the scheduled service cost or forfeit required deposits.',
  },
  {
    heading: '3. Payment and Pricing',
    body:
      'All service pricing is displayed in Nigerian Naira (NGN) and is inclusive of statutory taxes where applicable. We accept in-app card payments (powered by Paystack), direct bank transfer, and on-site POS/cash. Full payment is due upon completion of services, unless an advance deposit was required during booking.',
  },
  {
    heading: '4. User Accounts & Username Policy',
    body:
      'You are responsible for maintaining the confidentiality of your account credentials:\n\n' +
      '• Usernames: Each user may select an official @username handle (3–30 characters, alphanumeric with hyphens and underscores).\n' +
      '• 60-Day Cooldown: To prevent handle squatting and protect client identities, usernames may only be modified once every 60 days.\n' +
      '• Reserved Handles: System reserved handles (such as admin, support, barber, etc.) cannot be claimed by general users.',
  },
  {
    heading: '5. Barber Rights & Service Refusal',
    body:
      'CandyCutz barbers and partner salons maintain a professional, respectful environment. We reserve the right to decline or terminate services for any individual exhibiting abusive, discriminatory, or hazardous behavior, or presenting contagious scalp/skin conditions.',
  },
  {
    heading: '6. Intellectual Property',
    body:
      'The CandyCutz brand, mobile software, original imagery, logo emblem, and styling catalogues are the exclusive intellectual property of CandyCutz. Unauthorized replication or distribution is strictly prohibited.',
  },
  {
    heading: '7. Contact & Dispute Resolution',
    body:
      'If you have questions, feedback, or legal inquiries regarding these Terms, please contact our support desk:\n\n' +
      '• Email: legal@candycutz.com\n' +
      '• General Support: support@candycutz.com\n' +
      '• Location: CandyCutz Grooming Studio, Keffi, Nigeria',
  },
];

export default function TermsOfServiceScreen() {
  const router = useRouter();
  const { colors } = useAppTheme();
  const styles = createStyles(colors);
  const [title, setTitle] = useState('Terms of Service');
  const [sections, setSections] = useState<PolicySection[]>(DEFAULT_TERMS_SECTIONS);
  const [isLoading, setIsLoading] = useState(true);
  const [isError, setIsError] = useState(false);
  const [expandedIndices, setExpandedIndices] = useState<Record<number, boolean>>({
    0: true,
    1: true,
  });

  const loadTerms = async () => {
    setIsLoading(true);
    setIsError(false);
    try {
      const settings = await cmsApi.getSettings();
      const legal = settings.legal || settings.general || settings;

      if (legal?.terms_title) {
        setTitle(legal.terms_title);
      }

      if (legal?.terms_sections) {
        try {
          const parsed = typeof legal.terms_sections === 'string'
            ? JSON.parse(legal.terms_sections)
            : legal.terms_sections;
          if (Array.isArray(parsed) && parsed.length > 0) {
            setSections(parsed);
          }
        } catch {
          // Keep defaults if parse fails
        }
      }
    } catch {
      // If network fails, use default sections with graceful fallback
      setIsError(false); // Graceful fallback to default terms
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadTerms();
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
        title="Could not load Terms"
        message="Please check your connection to load the latest Terms of Service."
        onRetry={loadTerms}
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
              <Text style={styles.legalBadgeText}>Official Policy</Text>
            </View>
            <Text style={styles.updatedDate}>Version 2.0</Text>
          </View>
          <Text style={styles.heroTitle}>{title}</Text>
          <Text style={styles.heroSubtitle}>
            Please review the rules, rights, and policies governing your CandyCutz appointment bookings and app usage.
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
            <Text style={styles.loadingText}>Syncing latest terms...</Text>
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
            Questions about these terms? Reach us at{' '}
            <Text style={styles.footerNoteLink}>legal@candycutz.com</Text>
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
