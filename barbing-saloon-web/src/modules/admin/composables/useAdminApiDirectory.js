import { ref, computed } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useAdminApiDirectory() {
  const toast = useToast();

  const search = ref('');
  const activeGroup = ref('All');
  const activeMethod = ref('All');
  const activeAuth = ref('All'); // 'All', 'public', 'protected'

  const healthState = ref('checking'); // 'online', 'checking', 'offline'
  const latencyMs = ref(null);
  const copiedPath = ref(null);

  const endpointGroups = [
    {
      id: 'catalogue',
      name: 'Public Catalogue',
      description: 'Unauthenticated public discovery for services, barbers, gallery, availability, and articles.',
      endpoints: [
        { method: 'GET', path: '/api/v1/health', auth: 'public', description: 'System health probe & runtime gateway status' },
        { method: 'GET', path: '/api/v1/services', auth: 'public', description: 'Public services catalog with pricing & durations' },
        { method: 'GET', path: '/api/v1/services/{id}', auth: 'public', description: 'Service details and media assets' },
        { method: 'GET', path: '/api/v1/service-categories', auth: 'public', description: 'Active service categorization hierarchy' },
        { method: 'GET', path: '/api/v1/barbers', auth: 'public', description: 'Active barbers roster and styling specialties' },
        { method: 'GET', path: '/api/v1/availability', auth: 'public', description: 'Real-time bookable appointment time slots' },
        { method: 'GET', path: '/api/v1/gallery', auth: 'public', description: 'Curated gallery portfolio items' },
        { method: 'GET', path: '/api/v1/testimonials', auth: 'public', description: 'Approved client reviews and testimonials' },
        { method: 'GET', path: '/api/v1/blog', auth: 'public', description: 'Published styling articles and grooming advice' },
        { method: 'GET', path: '/api/v1/blog/{slug}', auth: 'public', description: 'Single article full content & reaction metrics' },
        { method: 'GET', path: '/api/v1/settings', auth: 'public', description: 'Public brand parameters and salon hours' },
        { method: 'GET', path: '/api/v1/working-hours', auth: 'public', description: 'Overview of barber working schedules' },
        { method: 'GET', path: '/api/v1/feature-flags', auth: 'public', description: 'Active client rollout flags for Web & Mobile' },
      ],
    },
    {
      id: 'auth',
      name: 'Authentication & Identity',
      description: 'Sanctum token issuance, registration, social OAuth, and credential lifecycles.',
      endpoints: [
        { method: 'POST', path: '/api/v1/auth/login', auth: 'public', description: 'Email, username, or phone credential authentication' },
        { method: 'POST', path: '/api/v1/auth/register', auth: 'public', description: 'New customer account registration' },
        { method: 'POST', path: '/api/v1/auth/social-login', auth: 'public', description: 'Verified Google or Apple OAuth token exchange' },
        { method: 'GET', path: '/api/v1/auth/me', auth: 'protected', description: 'Current authenticated user profile & roles' },
        { method: 'POST', path: '/api/v1/auth/logout', auth: 'protected', description: 'Revoke active Sanctum Bearer access token' },
        { method: 'POST', path: '/api/v1/auth/forgot-password', auth: 'public', description: 'Send password-reset instructions via Brevo SMTP' },
        { method: 'POST', path: '/api/v1/auth/reset-password', auth: 'public', description: 'Reset password with secure verification token' },
      ],
    },
    {
      id: 'booking',
      name: 'Reservations & Operations',
      description: 'Customer bookings, conflict prevention, barber walk-ins, and post-service reviews.',
      endpoints: [
        { method: 'GET', path: '/api/v1/appointments', auth: 'protected', description: 'List appointments (policy role-scoped)' },
        { method: 'POST', path: '/api/v1/appointments', auth: 'protected', description: 'Create locked appointment with slot locking' },
        { method: 'GET', path: '/api/v1/appointments/{id}', auth: 'protected', description: 'Appointment details, status, and receipt' },
        { method: 'PATCH', path: '/api/v1/appointments/{id}/status', auth: 'protected', description: 'Transition reservation status' },
        { method: 'POST', path: '/api/v1/appointments/{id}/cancel', auth: 'protected', description: 'Cancel reservation with automated policy refund' },
        { method: 'POST', path: '/api/v1/appointments/walk-in', auth: 'protected', description: 'Create immediate barber chair walk-in' },
        { method: 'POST', path: '/api/v1/reviews', auth: 'protected', description: 'Submit verified customer star rating and review' },
      ],
    },
    {
      id: 'payments',
      name: 'Payments & Webhooks',
      description: 'Checkout sessions, verified payment webhooks, and receipts.',
      endpoints: [
        { method: 'POST', path: '/api/v1/payments/checkout', auth: 'protected', description: 'Initialize gateway checkout intent (Stripe / Paystack)' },
        { method: 'GET', path: '/api/v1/payments/verify', auth: 'protected', description: 'Verify payment completion and unlock appointment' },
        { method: 'GET', path: '/api/v1/payments/receipts/{id}', auth: 'protected', description: 'Download authenticated booking payment receipt' },
        { method: 'POST', path: '/api/v1/payments/webhook', auth: 'public', description: 'HMAC SHA-256 verified payment callback listener' },
      ],
    },
    {
      id: 'account',
      name: 'Accounts & Staff Schedules',
      description: 'Customer profiles, notifications, barber shift schedules, and chair status.',
      endpoints: [
        { method: 'GET', path: '/api/v1/account/profile', auth: 'protected', description: 'Customer profile details and preferences' },
        { method: 'PUT', path: '/api/v1/account/profile', auth: 'protected', description: 'Update customer contact information' },
        { method: 'GET', path: '/api/v1/notifications', auth: 'protected', description: 'Authenticated notifications inbox' },
        { method: 'PATCH', path: '/api/v1/notifications/{id}/read', auth: 'protected', description: 'Mark notification as read' },
        { method: 'POST', path: '/api/v1/customer/blog/{id}/react', auth: 'protected', description: 'Submit article reaction (love/dislike)' },
        { method: 'GET', path: '/api/v1/barber/schedule', auth: 'protected', description: 'Barber working hours & shift breakdown' },
        { method: 'PUT', path: '/api/v1/barber/schedule', auth: 'protected', description: 'Update barber operating hours' },
        { method: 'PATCH', path: '/api/v1/barber/chair-status', auth: 'protected', description: 'Toggle chair live state (available/busy)' },
      ],
    },
    {
      id: 'admin',
      name: 'Administration & Platform Control',
      description: 'Privileged operations, feature flags, staff hours, feedback inbox, and audit logs.',
      endpoints: [
        { method: 'GET', path: '/api/v1/admin/dashboard', auth: 'protected', description: 'Executive salon KPIs and analytics overview' },
        { method: 'GET', path: '/api/v1/admin/customers', auth: 'protected', description: 'Customer roster with lifetime spend' },
        { method: 'GET', path: '/api/v1/admin/feature-flags', auth: 'protected', description: 'Manage runtime feature flags' },
        { method: 'POST', path: '/api/v1/admin/feature-flags', auth: 'protected', description: 'Create runtime feature flag' },
        { method: 'PATCH', path: '/api/v1/admin/feature-flags/{id}/toggle', auth: 'protected', description: 'Toggle feature flag state' },
        { method: 'GET', path: '/api/v1/admin/working-hours', auth: 'protected', description: 'View staff operating schedules' },
        { method: 'PUT', path: '/api/v1/admin/working-hours/{barberId}', auth: 'protected', description: 'Update barber weekly shifts' },
        { method: 'GET', path: '/api/v1/admin/blog', auth: 'protected', description: 'Editorial blog management with draft queue' },
        { method: 'POST', path: '/api/v1/admin/blog', auth: 'protected', description: 'Publish new article or draft' },
        { method: 'GET', path: '/api/v1/admin/feedback', auth: 'protected', description: 'Customer feedback and feature requests inbox' },
        { method: 'GET', path: '/api/v1/admin/settings', auth: 'protected', description: 'Salon settings, Brevo SMTP & OAuth keys' },
        { method: 'POST', path: '/api/v1/admin/settings', auth: 'protected', description: 'Persist system settings and credentials' },
        { method: 'POST', path: '/api/v1/admin/test-email', auth: 'protected', description: 'Dispatch test transactional email' },
        { method: 'GET', path: '/api/v1/admin/logs', auth: 'protected', description: 'Audit log stream with actor IDs and IP addresses' },
      ],
    },
  ];

  // Calculated Stats
  const stats = computed(() => {
    let totalEndpoints = 0;
    let publicCount = 0;
    let protectedCount = 0;

    endpointGroups.forEach(group => {
      group.endpoints.forEach(ep => {
        totalEndpoints++;
        if (ep.auth === 'public') publicCount++;
        if (ep.auth === 'protected') protectedCount++;
      });
    });

    return {
      totalEndpoints,
      publicCount,
      protectedCount,
      groupCount: endpointGroups.length,
      healthState: healthState.value,
      latencyMs: latencyMs.value,
    };
  });

  // Filtered Groups
  const visibleGroups = computed(() => {
    const q = search.value.trim().toLowerCase();

    return endpointGroups
      .filter(group => activeGroup.value === 'All' || group.name === activeGroup.value || group.id === activeGroup.value)
      .map(group => {
        const filteredEndpoints = group.endpoints.filter(ep => {
          // Method filter
          if (activeMethod.value !== 'All' && ep.method !== activeMethod.value) {
            return false;
          }
          // Auth scope filter
          if (activeAuth.value !== 'All' && ep.auth !== activeAuth.value) {
            return false;
          }
          // Query search filter
          if (q) {
            const matchStr = `${ep.method} ${ep.path} ${ep.description} ${group.name}`.toLowerCase();
            return matchStr.includes(q);
          }
          return true;
        });

        return {
          ...group,
          endpoints: filteredEndpoints,
        };
      })
      .filter(group => group.endpoints.length > 0);
  });

  // Health probe against /health
  const checkHealth = async () => {
    healthState.value = 'checking';
    const start = performance.now();
    try {
      await client.get('/health', { timeout: 5000 });
      const elapsed = Math.round(performance.now() - start);
      latencyMs.value = elapsed;
      healthState.value = 'online';
    } catch (err) {
      console.warn('[AdminApiDirectory] Health check probe failed:', err);
      healthState.value = 'offline';
      latencyMs.value = null;
    }
  };

  // Copy Path Helper
  const copyPath = async (path) => {
    try {
      await navigator.clipboard.writeText(path);
      copiedPath.value = path;
      toast.success(`Copied: ${path}`);
      setTimeout(() => {
        if (copiedPath.value === path) copiedPath.value = null;
      }, 2000);
    } catch {
      toast.error('Failed to copy path');
    }
  };

  const resetFilters = () => {
    search.value = '';
    activeGroup.value = 'All';
    activeMethod.value = 'All';
    activeAuth.value = 'All';
  };

  return {
    search,
    activeGroup,
    activeMethod,
    activeAuth,
    healthState,
    latencyMs,
    copiedPath,
    endpointGroups,
    stats,
    visibleGroups,
    checkHealth,
    copyPath,
    resetFilters,
  };
}
