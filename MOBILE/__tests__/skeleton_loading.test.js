const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('Skeleton Loading System Verification Suite', async (t) => {
  await t.test('Skeleton.tsx exports base primitive with pulse, shimmer, and all composite skeletons', () => {
    const skeletonPath = path.resolve(__dirname, '../src/components/common/Skeleton.tsx');
    assert.ok(fs.existsSync(skeletonPath), 'Skeleton.tsx must exist');

    const content = fs.readFileSync(skeletonPath, 'utf8');
    assert.ok(content.includes('export function Skeleton'), 'Must export base Skeleton');
    assert.ok(content.includes('shimmerAnim'), 'Must implement shimmer animation');
    assert.ok(content.includes('pulseAnim'), 'Must implement pulse opacity animation');
    assert.ok(content.includes('export function ServiceSkeletons'), 'Must export ServiceSkeletons');
    assert.ok(content.includes('export function BookingSkeletons'), 'Must export BookingSkeletons');
    assert.ok(content.includes('export function AppointmentSkeletons'), 'Must export AppointmentSkeletons');
    assert.ok(content.includes('export function ScheduleSkeletons'), 'Must export ScheduleSkeletons');
    assert.ok(content.includes('export function BookingFlowSkeleton'), 'Must export BookingFlowSkeleton');
    assert.ok(content.includes('export function ProfileEditSkeleton'), 'Must export ProfileEditSkeleton');
    assert.ok(content.includes('export function NotificationSkeletons'), 'Must export NotificationSkeletons');
    assert.ok(content.includes('export function QueueSkeletons'), 'Must export QueueSkeletons');
    assert.ok(content.includes('export function WalkInSkeleton'), 'Must export WalkInSkeleton');
    assert.ok(content.includes('export function BarberCardSkeletons'), 'Must export BarberCardSkeletons');
    assert.ok(content.includes('export function HomeAppointmentSkeleton'), 'Must export HomeAppointmentSkeleton');
  });

  await t.test('useSkeletonTransition hook exists and provides smooth content fade-in', () => {
    const hookPath = path.resolve(__dirname, '../src/hooks/useSkeletonTransition.ts');
    assert.ok(fs.existsSync(hookPath), 'useSkeletonTransition.ts must exist');

    const content = fs.readFileSync(hookPath, 'utf8');
    assert.ok(content.includes('export function useSkeletonTransition'), 'Must export useSkeletonTransition');
    assert.ok(content.includes('Animated.timing'), 'Must animate opacity');
  });

  await t.test('Tab screens use content-aware skeletons without blocking spinners', () => {
    const servicesPath = path.resolve(__dirname, '../app/(tabs)/services.tsx');
    const services = fs.readFileSync(servicesPath, 'utf8');
    assert.ok(services.includes('ServiceSkeletons'), 'services.tsx must use ServiceSkeletons');
    assert.ok(!services.includes('<LoadingState'), 'services.tsx must not use full-screen LoadingState');

    const appointmentsPath = path.resolve(__dirname, '../app/(tabs)/appointments.tsx');
    const appointments = fs.readFileSync(appointmentsPath, 'utf8');
    assert.ok(appointments.includes('AppointmentSkeletons'), 'appointments.tsx must use AppointmentSkeletons');
    assert.ok(!appointments.includes('<LoadingState'), 'appointments.tsx must not use full-screen LoadingState');

    const schedulePath = path.resolve(__dirname, '../app/(tabs)/schedule.tsx');
    const schedule = fs.readFileSync(schedulePath, 'utf8');
    assert.ok(schedule.includes('ScheduleSkeletons'), 'schedule.tsx must use ScheduleSkeletons');

    const bookingsPath = path.resolve(__dirname, '../app/(tabs)/bookings.tsx');
    const bookings = fs.readFileSync(bookingsPath, 'utf8');
    assert.ok(bookings.includes('BookingSkeletons'), 'bookings.tsx must use BookingSkeletons');
  });

  await t.test('Feature screens use content-aware skeletons', () => {
    const bookPath = path.resolve(__dirname, '../app/book/[serviceId].tsx');
    const book = fs.readFileSync(bookPath, 'utf8');
    assert.ok(book.includes('BookingFlowSkeleton'), 'book/[serviceId].tsx must use BookingFlowSkeleton');
    assert.ok(!book.includes('<LoadingState'), 'book/[serviceId].tsx must not use LoadingState');

    const walkinPath = path.resolve(__dirname, '../app/walkin.tsx');
    const walkin = fs.readFileSync(walkinPath, 'utf8');
    assert.ok(walkin.includes('WalkInSkeleton'), 'walkin.tsx must use WalkInSkeleton');
    assert.ok(!walkin.includes('<LoadingState'), 'walkin.tsx must not use LoadingState');

    const profileEditPath = path.resolve(__dirname, '../app/profile/edit.tsx');
    const profileEdit = fs.readFileSync(profileEditPath, 'utf8');
    assert.ok(profileEdit.includes('ProfileEditSkeleton'), 'profile/edit.tsx must use ProfileEditSkeleton');
    assert.ok(!profileEdit.includes('<LoadingState'), 'profile/edit.tsx must not use LoadingState');

    const barberProfileEditPath = path.resolve(__dirname, '../app/barber/profile-edit.tsx');
    const barberProfileEdit = fs.readFileSync(barberProfileEditPath, 'utf8');
    assert.ok(barberProfileEdit.includes('ProfileEditSkeleton'), 'barber/profile-edit.tsx must use ProfileEditSkeleton');
    assert.ok(!barberProfileEdit.includes('<LoadingState'), 'barber/profile-edit.tsx must not use LoadingState');
  });

  await t.test('Notifications, Barber Queue, and Customer Home use skeletons for instant perception', () => {
    const notifPath = path.resolve(__dirname, '../app/notifications.tsx');
    const notif = fs.readFileSync(notifPath, 'utf8');
    assert.ok(notif.includes('NotificationSkeletons'), 'notifications.tsx must use NotificationSkeletons');

    const queuePath = path.resolve(__dirname, '../src/components/barber/BarberQueueView.tsx');
    const queue = fs.readFileSync(queuePath, 'utf8');
    assert.ok(queue.includes('QueueSkeletons'), 'BarberQueueView.tsx must use QueueSkeletons');

    const homePath = path.resolve(__dirname, '../src/components/customer/CustomerHomeView.tsx');
    const home = fs.readFileSync(homePath, 'utf8');
    assert.ok(home.includes('HomeAppointmentSkeleton'), 'CustomerHomeView.tsx must use HomeAppointmentSkeleton');
    assert.ok(home.includes('BarberCardSkeletons'), 'CustomerHomeView.tsx must use BarberCardSkeletons');
    assert.ok(home.includes('ServiceSkeletons'), 'CustomerHomeView.tsx must use ServiceSkeletons');
  });
});
