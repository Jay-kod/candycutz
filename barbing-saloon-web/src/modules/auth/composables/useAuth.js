import { storeToRefs } from 'pinia';
import { useAuthStore } from '../store/auth.store';

export function useAuth() {
  const auth = useAuthStore();
  const { user, isAuthenticated, token } = storeToRefs(auth);

  return {
    auth,
    user,
    token,
    isAuthenticated,
    login: auth.login,
    register: auth.register,
    logout: auth.logout,
    fetchUser: auth.fetchUser,
    setIntendedRoute: auth.setIntendedRoute,
    redirectAfterLogin: auth.redirectAfterLogin,
    isCustomer: auth.isCustomer,
    isBarber: auth.isBarber,
    isAdmin: auth.isAdmin,
    isSuperAdmin: auth.isSuperAdmin,
  };
}