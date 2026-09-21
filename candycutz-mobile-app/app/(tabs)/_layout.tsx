import React from 'react';
import { Tabs } from 'expo-router';
import { StyleSheet, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { FONTS } from '../../src/constants/theme';
import { useAppTheme } from '../../src/hooks/useAppTheme';
import { useAuthStore } from '../../src/store/authStore';
import { UIcon, UIconName } from '../../src/components/common/UIcon';

function TabIcon({
  name,
  focused,
  activeColor,
  inactiveColor,
}: {
  name: UIconName;
  focused: boolean;
  activeColor: string;
  inactiveColor: string;
}) {
  return (
    <View style={styles.iconContainer}>
      <UIcon
        name={name}
        size={22}
        color={focused ? activeColor : inactiveColor}
      />
    </View>
  );
}

export default function TabLayout() {
  const { user, isAuthenticated, viewMode } = useAuthStore();
  const { colors } = useAppTheme();
  const insets = useSafeAreaInsets();
  const isBarber = isAuthenticated && user?.role === 'barber' && viewMode === 'barber';
  const tabBarContentHeight = 56;

  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarStyle: {
          backgroundColor: colors.surface,
          borderTopColor: colors.border,
          borderTopWidth: 1,
          height: tabBarContentHeight + insets.bottom,
          paddingBottom: Math.max(insets.bottom, 8),
          paddingTop: 8,
        },
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: colors.textMuted,
        tabBarLabelStyle: {
          fontSize: FONTS.sizes.xs,
          fontWeight: '700',
        },
      }}
    >
      {/* Root Tab 1: Home (Customer) OR Chair Queue (Barber) */}
      <Tabs.Screen
        name="index"
        options={{
          title: isBarber ? 'Chair Queue' : 'Home',
          tabBarIcon: ({ focused }) => (
            <TabIcon
              name={isBarber ? 'chair' : 'home'}
              focused={focused}
              activeColor={colors.primary}
              inactiveColor={colors.textMuted}
            />
          ),
        }}
      />

      {/* Customer Tab 2: Services Catalog (hidden for Barbers) */}
      <Tabs.Screen
        name="services"
        options={{
          title: 'Services',
          href: isBarber ? null : '/services',
          tabBarIcon: ({ focused }) => (
            <TabIcon
              name="scissors"
              focused={focused}
              activeColor={colors.primary}
              inactiveColor={colors.textMuted}
            />
          ),
        }}
      />

      {/* Customer Tab 3: My Bookings (hidden for Barbers) */}
      <Tabs.Screen
        name="bookings"
        options={{
          title: 'My Bookings',
          href: isBarber ? null : '/bookings',
          tabBarIcon: ({ focused }) => (
            <TabIcon
              name="calendar"
              focused={focused}
              activeColor={colors.primary}
              inactiveColor={colors.textMuted}
            />
          ),
        }}
      />

      {/* Barber Tab 2: All Appointments (hidden for Customers) */}
      <Tabs.Screen
        name="appointments"
        options={{
          title: 'All Bookings',
          href: isBarber ? '/appointments' : null,
          tabBarIcon: ({ focused }) => (
            <TabIcon
              name="clipboardList"
              focused={focused}
              activeColor={colors.primary}
              inactiveColor={colors.textMuted}
            />
          ),
        }}
      />

      {/* Barber Tab 3: Schedule & Blackout (hidden for Customers) */}
      <Tabs.Screen
        name="schedule"
        options={{
          title: 'Schedule',
          href: isBarber ? '/schedule' : null,
          tabBarIcon: ({ focused }) => (
            <TabIcon
              name="calendarClock"
              focused={focused}
              activeColor={colors.primary}
              inactiveColor={colors.textMuted}
            />
          ),
        }}
      />

      {/* Tab 4: Profile (Customer OR Staff) */}
      <Tabs.Screen
        name="profile"
        options={{
          title: isBarber ? 'Staff Desk' : 'Profile',
          tabBarIcon: ({ focused }) => (
            <TabIcon
              name="user"
              focused={focused}
              activeColor={colors.primary}
              inactiveColor={colors.textMuted}
            />
          ),
        }}
      />
    </Tabs>
  );
}

const styles = StyleSheet.create({
  iconContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
});
