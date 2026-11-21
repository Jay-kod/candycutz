import { beforeEach, describe, expect, it, vi } from 'vitest';
import { registerRouteGuards } from '../routeGuards';

const authMocks = vi.hoisted(() => ({
  store: null,
}));

vi.mock('@/modules/auth/store/auth.store', () => ({
  useAuthStore: () => authMocks.store,
}));

vi.mock('../../composables/useTheme', () => ({
  syncThemeForRoute: vi.fn(),
}));

describe('route authentication guard', () => {
  let guard;
  let router;

  beforeEach(() => {
    guard = null;
    router = {
      beforeEach: vi.fn((callback) => {
        guard = callback;
      }),
    };
  });

  it('waits for saved-session bootstrap before deciding whether a protected route needs login', async () => {
    let finishBootstrap;
    const bootstrap = vi.fn(
      () =>
        new Promise((resolve) => {
          finishBootstrap = () => {
            authMocks.store.status = 'authenticated';
            authMocks.store.user = { role: 'admin' };
            resolve();
          };
        })
    );
    authMocks.store = {
      status: 'booting',
      user: null,
      bootstrap,
      get isAuthenticated() {
        return this.status === 'authenticated';
      },
      setIntendedRoute: vi.fn(),
    };
    registerRouteGuards(router);

    const navigation = guard({
      meta: { requiresAuth: true, roles: ['admin'] },
      path: '/admin/dashboard',
      fullPath: '/admin/dashboard',
    });

    expect(bootstrap).toHaveBeenCalledOnce();
    expect(authMocks.store.setIntendedRoute).not.toHaveBeenCalled();

    finishBootstrap();

    await expect(navigation).resolves.toBe(true);
    expect(authMocks.store.setIntendedRoute).not.toHaveBeenCalled();
  });

  it('redirects to login when bootstrap confirms there is no authenticated session', async () => {
    authMocks.store = {
      status: 'booting',
      user: null,
      bootstrap: vi.fn(async () => {
        authMocks.store.status = 'guest';
      }),
      isAuthenticated: false,
      setIntendedRoute: vi.fn(),
    };
    registerRouteGuards(router);

    await expect(
      guard({
        meta: { requiresAuth: true, roles: ['admin'] },
        path: '/admin/dashboard',
        fullPath: '/admin/dashboard',
      })
    ).resolves.toBe('/admin/login');
    expect(authMocks.store.setIntendedRoute).toHaveBeenCalledWith('/admin/dashboard');
  });
});
