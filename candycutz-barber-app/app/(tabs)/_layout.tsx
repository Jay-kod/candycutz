import React from 'react';
import { Tabs } from 'expo-router';
import { Text, View, StyleSheet } from 'react-native';
import { COLORS, FONTS } from '../../src/constants/theme';

function BarberTabIcon({ name, focused }: { name: string; focused: boolean }) {
  const getSymbol = () => {
    switch (name) {
      case 'queue':
        return '✂';
      case 'schedule':
        return '🕒';
      case 'appointments':
        return '📋';
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

export default function BarberTabLayout() {
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
      <Tabs.Screen
        name="index"
        options={{
          title: 'Chair & Queue',
          tabBarIcon: ({ focused }) => <BarberTabIcon name="queue" focused={focused} />,
        }}
      />
      <Tabs.Screen
        name="schedule"
        options={{
          title: 'Schedule',
          tabBarIcon: ({ focused }) => <BarberTabIcon name="schedule" focused={focused} />,
        }}
      />
      <Tabs.Screen
        name="appointments"
        options={{
          title: 'Appointments',
          tabBarIcon: ({ focused }) => <BarberTabIcon name="appointments" focused={focused} />,
        }}
      />
      <Tabs.Screen
        name="profile"
        options={{
          title: 'Earnings & Info',
          tabBarIcon: ({ focused }) => <BarberTabIcon name="profile" focused={focused} />,
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
