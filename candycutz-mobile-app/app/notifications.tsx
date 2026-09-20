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
import { COLORS, FONTS, RADIUS, SPACING } from '../src/constants/theme';
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
    if (sections.length === 0 && !isLoading) {
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
            tintColor={COLORS.primary}
            colors={[COLORS.primary]}
          />
        }
        showsVerticalScrollIndicator={false}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.md,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    backgroundColor: COLORS.surface,
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
    backgroundColor: COLORS.surfaceHighlight,
    alignItems: 'center',
    justifyContent: 'center',
  },
  backButtonText: {
    fontSize: FONTS.sizes.xl,
    color: COLORS.textPrimary,
  },
  headerTitle: {
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
    color: COLORS.textPrimary,
  },
  headerSubtitle: {
    fontSize: FONTS.sizes.xs,
    color: COLORS.primary,
    fontWeight: '600',
    marginTop: 2,
  },
  markAllButton: {
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.sm,
    borderRadius: RADIUS.sm,
    backgroundColor: COLORS.primaryLight,
  },
  markAllText: {
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
    color: COLORS.primary,
  },
  listContent: {
    paddingBottom: SPACING.xxl,
  },
  sectionTitle: {
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
    color: COLORS.textMuted,
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
    borderBottomColor: COLORS.border,
    gap: SPACING.md,
  },
  notifItemUnread: {
    backgroundColor: 'rgba(212, 175, 55, 0.04)',
  },
  notifItemPressed: {
    backgroundColor: COLORS.surfaceHighlight,
  },
  notifIcon: {
    width: 42,
    height: 42,
    borderRadius: RADIUS.md,
    backgroundColor: COLORS.surfaceElevated,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
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
    color: COLORS.textSecondary,
    flex: 1,
  },
  notifTitleUnread: {
    color: COLORS.textPrimary,
    fontWeight: '700',
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.primary,
    marginLeft: SPACING.sm,
  },
  notifMessage: {
    fontSize: FONTS.sizes.sm,
    color: COLORS.textMuted,
    marginTop: SPACING.xs,
    lineHeight: 20,
  },
  notifTime: {
    fontSize: FONTS.sizes.xs,
    color: COLORS.textMuted,
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
    color: COLORS.textPrimary,
    textAlign: 'center',
  },
  emptySubtitle: {
    fontSize: FONTS.sizes.md,
    color: COLORS.textMuted,
    textAlign: 'center',
    marginTop: SPACING.sm,
    lineHeight: 22,
  },
});
