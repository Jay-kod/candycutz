import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { notificationsApi } from '../api/client';
import { Notification } from '../api/types';

const NOTIFICATIONS_KEY = ['notifications'];
const UNREAD_COUNT_KEY = ['notifications', 'unread-count'];

/**
 * Hook providing reactive notification state with auto-refresh.
 *
 * - `notifications` — full list of notifications
 * - `unreadCount` — badge count (polled every 30s via lightweight endpoint)
 * - `markAsRead(id)` — mark a single notification as read
 * - `markAllRead()` — mark all notifications as read
 * - `refetch()` — force immediate refresh (call after mutations)
 */
export function useNotifications(enabled = true) {
  const queryClient = useQueryClient();

  // Full notification list — refreshed every 60s
  const {
    data: notifications = [],
    isLoading,
    isError,
    refetch,
  } = useQuery<Notification[]>({
    queryKey: NOTIFICATIONS_KEY,
    queryFn: () => notificationsApi.getAll(),
    enabled,
    refetchInterval: 60_000,
    staleTime: 30_000,
  });

  // Lightweight unread count — polled every 30s
  const { data: unreadCount = 0 } = useQuery<number>({
    queryKey: UNREAD_COUNT_KEY,
    queryFn: () => notificationsApi.getUnreadCount(),
    enabled,
    refetchInterval: 30_000,
    staleTime: 15_000,
  });

  // Mark single notification as read
  const markAsReadMutation = useMutation({
    mutationFn: (id: number) => notificationsApi.markAsRead(id),
    onMutate: async (id: number) => {
      // Optimistic update
      await queryClient.cancelQueries({ queryKey: NOTIFICATIONS_KEY });
      const previous = queryClient.getQueryData<Notification[]>(NOTIFICATIONS_KEY);
      queryClient.setQueryData<Notification[]>(NOTIFICATIONS_KEY, (old) =>
        old?.map((n) => (n.id === id ? { ...n, is_read: true } : n)) ?? []
      );
      queryClient.setQueryData<{ count: number }>(UNREAD_COUNT_KEY, (old) => ({
        count: Math.max(0, (old?.count ?? 0) - 1),
      }));
      return { previous };
    },
    onError: (_err, _id, context) => {
      if (context?.previous) {
        queryClient.setQueryData(NOTIFICATIONS_KEY, context.previous);
      }
    },
    onSettled: () => {
      queryClient.invalidateQueries({ queryKey: UNREAD_COUNT_KEY });
    },
  });

  // Mark all as read
  const markAllReadMutation = useMutation({
    mutationFn: () => notificationsApi.markAllRead(),
    onMutate: async () => {
      await queryClient.cancelQueries({ queryKey: NOTIFICATIONS_KEY });
      const previous = queryClient.getQueryData<Notification[]>(NOTIFICATIONS_KEY);
      queryClient.setQueryData<Notification[]>(NOTIFICATIONS_KEY, (old) =>
        old?.map((n) => ({ ...n, is_read: true })) ?? []
      );
      queryClient.setQueryData<{ count: number }>(UNREAD_COUNT_KEY, { count: 0 });
      return { previous };
    },
    onError: (_err, _vars, context) => {
      if (context?.previous) {
        queryClient.setQueryData(NOTIFICATIONS_KEY, context.previous);
      }
    },
    onSettled: () => {
      queryClient.invalidateQueries({ queryKey: UNREAD_COUNT_KEY });
    },
  });

  return {
    notifications,
    unreadCount,
    isLoading,
    isError,
    refetch,
    markAsRead: (id: number) => markAsReadMutation.mutate(id),
    markAllRead: () => markAllReadMutation.mutate(),
  };
}
