import React from 'react';
import { useAuthStore } from '../../src/store/authStore';
import { BarberQueueView } from '../../src/components/barber/BarberQueueView';
import { CustomerHomeView } from '../../src/components/customer/CustomerHomeView';

/**
 * Candycutz Unified Entry Screen
 * Intelligently presents either the Stylist/Barber Chair & Live Queue
 * or the Customer Flagship Booking Experience based on active role and view mode.
 */
export default function HomeScreen() {
  const { isBarber, viewMode } = useAuthStore();

  if (isBarber && viewMode === 'barber') {
    return <BarberQueueView />;
  }

  return <CustomerHomeView />;
}

