import React, { useCallback, useMemo } from 'react';
import {
  FlatList,
  Pressable,
  RefreshControl,
  SafeAreaView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { NotificationSkeletons } from '../src/components/common/Skeleton';
import { FONTS, RADIUS, SPACING, ThemeColors } from '../src/constants/theme';
import { useAppTheme } from '../src/hooks/useAppTheme';
import { useNotifications } from '../src/hooks/useNotifications';
import { Notification } from '../src/api/types';

function getNotificationIcon(type?: string): string {
  switch (type) {
    case 'booking_created':
      return '✂️';
    case 'booking_approved':
    case 'booking_confirmed':
      return '✅';
    case 'booking_cancelled':
      return '❌';
    case 'booking_completed':
      return '💈';
    case 'booking_no_show':
      return '⚠️';
    case 'payment_received':
    case 'payment_verified':
      return '💳';
    case 'system_update':
      return '📢';
    default:
      return '🔔';
  }
}

function getRelativeTime(dateStr?: string): string {
  if (!dateStr) return '';
  const now = new Date();
  const date = new Date(dateStr);
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return date.toLocaleDateString();
}

function groupByDate(items: Notification[]): { title: string; data: Notification[] }[] {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  const groups: Record<string, Notification[]> = {
    Today: [],
    Yesterday: [],
    Earlier: [],
  };

  items.forEach((item) => {
    const d = new Date(item.created_at ?? '');
    d.setHours(0, 0, 0, 0);
    if (d.getTime() >= today.getTime()) {
      groups.Today.push(item);
    } else if (d.getTime() >= yesterday.getTime()) {
      groups.Yesterday.push(item);
    } else {
      groups.Earlier.push(item);
    }
  });

  return Object.entries(groups)
    .filter(([, data]) => data.length > 0)
    .map(([title, data]) => ({ title, data }));
}

function NotificationItem({
  item,
  onPress,
}: {
  item: Notification;
  onPress: () => void;
}) {
  const { colors } = useAppTheme();
  const styles = createStyles(colors);

  return (
    <Pressable
      onPress={onPress}
      style={({ pressed }) => [
        styles.notifItem,
        !item.is_read && styles.notifItemUnread,
        pressed && styles.notifItemPressed,
      ]}
    >
      <View style={styles.notifIcon}>
        <Text style={styles.notifIconText}>{getNotificationIcon(item.type)}</Text>
      </View>
      <View style={styles.notifContent}>
        <View style={styles.notifHeader}>
          <Text
            style={[styles.notifTitle, !item.is_read && styles.notifTitleUnread]}
            numberOfLines={1}
          >
            {item.title}
          </Text>
          {!item.is_read && <View style={styles.unreadDot} />}
        </View>
        <Text style={styles.notifMessage} numberOfLines={2}>
          {item.message}
        </Text>
        <Text style={styles.notifTime}>{getRelativeTime(item.created_at)}</Text>
      </View>
    </Pressable>
  );
}

export default function NotificationsScreen() {
  const router = useRouter();
  const { colors } = useAppTheme();
  const styles = createStyles(colors);
  const {
    notifications,
    unreadCount,
    isLoading,
    refetch,
    markAsRead,
    markAllRead,
  } = useNotifications();

  const sections = useMemo(() => groupByDate(notifications), [notifications]);

  const handlePress = useCallback(
    (item: Notification) => {
      if (!item.is_read) {
        markAsRead(item.id);
      }
    },
    [markAsRead]
  );

  const renderSectionData = useCallback(() => {
    if (isLoading) {
      return <NotificationSkeletons count={6} />;
    }

    if (sections.length === 0) {
      return (
        <View style={styles.emptyState}>
          <Text style={styles.emptyIcon}>🔔</Text>
          <Text style={styles.emptyTitle}>No notifications yet</Text>
          <Text style={styles.emptySubtitle}>
            You'll receive updates about your bookings, payments, and more here.
          </Text>
        </View>
      );
    }

    return sections.map((section) => (
      <View key={section.title}>
        <Text style={styles.sectionTitle}>{section.title}</Text>
        {section.data.map((item) => (
          <NotificationItem key={item.id} item={item} onPress={() => handlePress(item)} />
        ))}
      </View>
    ));
  }, [sections, isLoading, handlePress]);

  return (
    <SafeAreaView style={styles.safeArea}>
      {/* Header */}
      <View style={styles.header}>
        <View style={styles.headerLeft}>
          <Pressable onPress={() => router.back()} style={styles.backButton}>
            <Text style={styles.backButtonText}>←</Text>
          </Pressable>
          <View>
            <Text style={styles.headerTitle}>Notifications</Text>
            {unreadCount > 0 && (
              <Text style={styles.headerSubtitle}>
                {unreadCount} unread
              </Text>
            )}
          </View>
        </View>
        {unreadCount > 0 && (
          <Pressable onPress={markAllRead} style={styles.markAllButton}>
            <Text style={styles.markAllText}>Mark all read</Text>
          </Pressable>
        )}
      </View>

      {/* Notification List */}
      <FlatList
        data={[{ key: 'content' }]}
        renderItem={() => <View>{renderSectionData()}</View>}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl
            refreshing={isLoading}
            onRefresh={refetch}
            tintColor={colors.primary}
            colors={[colors.primary]}
          />
        }
        showsVerticalScrollIndicator={false}
      />
    </SafeAreaView>
  );
}

const createStyles = (colors: ThemeColors) => StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: colors.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.md,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
    backgroundColor: colors.surface,
  },
  headerLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.md,
  },
  backButton: {
    width: 36,
    height: 36,
    borderRadius: RADIUS.sm,
    backgroundColor: colors.surfaceHighlight,
    alignItems: 'center',
    justifyContent: 'center',
  },
  backButtonText: {
    fontSize: FONTS.sizes.xl,
    color: colors.textPrimary,
  },
  headerTitle: {
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
    color: colors.textPrimary,
  },
  headerSubtitle: {
    fontSize: FONTS.sizes.xs,
    color: colors.primary,
    fontWeight: '600',
    marginTop: 2,
  },
  markAllButton: {
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.sm,
    borderRadius: RADIUS.sm,
    backgroundColor: colors.primaryLight,
  },
  markAllText: {
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
    color: colors.primary,
  },
  listContent: {
    paddingBottom: SPACING.xxl,
  },
  sectionTitle: {
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
    color: colors.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 1.5,
    paddingHorizontal: SPACING.lg,
    paddingTop: SPACING.lg,
    paddingBottom: SPACING.sm,
  },
  notifItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.md,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
    gap: SPACING.md,
  },
  notifItemUnread: {
    backgroundColor: colors.primaryLight,
  },
  notifItemPressed: {
    backgroundColor: colors.surfaceHighlight,
  },
  notifIcon: {
    width: 42,
    height: 42,
    borderRadius: RADIUS.md,
    backgroundColor: colors.surfaceElevated,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: colors.border,
  },
  notifIconText: {
    fontSize: 20,
  },
  notifContent: {
    flex: 1,
  },
  notifHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  notifTitle: {
    fontSize: FONTS.sizes.md,
    fontWeight: '600',
    color: colors.textSecondary,
    flex: 1,
  },
  notifTitleUnread: {
    color: colors.textPrimary,
    fontWeight: '700',
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: colors.primary,
    marginLeft: SPACING.sm,
  },
  notifMessage: {
    fontSize: FONTS.sizes.sm,
    color: colors.textMuted,
    marginTop: SPACING.xs,
    lineHeight: 20,
  },
  notifTime: {
    fontSize: FONTS.sizes.xs,
    color: colors.textMuted,
    marginTop: SPACING.xs,
    opacity: 0.7,
  },
  emptyState: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: SPACING.xxl * 2,
    paddingHorizontal: SPACING.xl,
  },
  emptyIcon: {
    fontSize: 56,
    marginBottom: SPACING.lg,
  },
  emptyTitle: {
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
    color: colors.textPrimary,
    textAlign: 'center',
  },
  emptySubtitle: {
    fontSize: FONTS.sizes.md,
    color: colors.textMuted,
    textAlign: 'center',
    marginTop: SPACING.sm,
    lineHeight: 22,
  },
});
