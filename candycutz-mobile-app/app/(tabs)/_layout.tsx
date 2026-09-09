import React from 'react';
import { Tabs } from 'expo-router';
import { StyleSheet, Text, View } from 'react-native';
import { COLORS, FONTS } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';

function TabIcon({ name, focused }: { name: string; focused: boolean }) {
  const getSymbol = () => {
    switch (name) {
      case 'home':
        return '✂';
      case 'services':
        return '★';
      case 'bookings':
        return '📅';
      case 'queue':
        return '💺';
      case 'all-appointments':
        return '📋';
      case 'schedule':
        return '🕒';
      case 'profile':
        return '👤';
      default:
        return '•';
    }
  };

  return (
    <View style={styles.iconContainer}>
      <Text style={[styles.iconText, { color: focused ? COLORS.primary : COLORS.textMuted }]}>
        {getSymbol()}
      </Text>
    </View>
  );
}

export default function TabLayout() {
  const { user, isAuthenticated, viewMode } = useAuthStore();
  const isBarber = isAuthenticated && user?.role === 'barber' && viewMode === 'barber';

  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarStyle: {
          backgroundColor: COLORS.surface,
          borderTopColor: COLORS.border,
          borderTopWidth: 1,
          height: 64,
          paddingBottom: 10,
          paddingTop: 8,
        },
        tabBarActiveTintColor: COLORS.primary,
        tabBarInactiveTintColor: COLORS.textMuted,
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
            <TabIcon name={isBarber ? 'queue' : 'home'} focused={focused} />
          ),
        }}
      />

      {/* Customer Tab 2: Services Catalog (hidden for Barbers) */}
      <Tabs.Screen
        name="services"
        options={{
          title: 'Services',
          href: isBarber ? null : '/services',
          tabBarIcon: ({ focused }) => <TabIcon name="services" focused={focused} />,
        }}
      />

      {/* Customer Tab 3: My Bookings (hidden for Barbers) */}
      <Tabs.Screen
        name="bookings"
        options={{
          title: 'My Bookings',
          href: isBarber ? null : '/bookings',
          tabBarIcon: ({ focused }) => <TabIcon name="bookings" focused={focused} />,
        }}
      />

      {/* Barber Tab 2: All Appointments (hidden for Customers) */}
      <Tabs.Screen
        name="appointments"
        options={{
          title: 'All Bookings',
          href: isBarber ? '/appointments' : null,
          tabBarIcon: ({ focused }) => <TabIcon name="all-appointments" focused={focused} />,
        }}
      />

      {/* Barber Tab 3: Schedule & Blackout (hidden for Customers) */}
      <Tabs.Screen
        name="schedule"
        options={{
          title: 'Schedule',
          href: isBarber ? '/schedule' : null,
          tabBarIcon: ({ focused }) => <TabIcon name="schedule" focused={focused} />,
        }}
      />

      {/* Tab 4: Profile (Customer OR Staff) */}
      <Tabs.Screen
        name="profile"
        options={{
          title: isBarber ? 'Staff Desk' : 'Profile',
          tabBarIcon: ({ focused }) => <TabIcon name="profile" focused={focused} />,
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
  iconText: {
    fontSize: 20,
  },
});
