import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { publicApi } from '../../public/api/public.api';
import { useAuth } from './useAuth';

const GOOGLE_SCRIPT = 'https://accounts.google.com/gsi/client';
const APPLE_SCRIPT = 'https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js';

export function useSocialAuth() {
  const router = useRouter();
  const { socialLogin, redirectAfterLogin } = useAuth();

  const googleClientId = ref('');
  const appleClientId = ref('');
  const googleScriptLoaded = ref(false);
  const appleScriptLoaded = ref(false);
  const socialBusy = ref(false);
  const socialError = ref('');

  const googleConfigured = computed(() => googleClientId.value !== '');
  const appleConfigured = computed(() => appleClientId.value !== '');

  function loadScript(src, onLoad) {
    if (document.querySelector(`script[data-candycutz-src="${src}"]`)) {
      return;
    }
    const script = document.createElement('script');
    script.src = src;
    script.async = true;
    script.dataset.candycutzSrc = src;
    script.onload = onLoad;
    document.head.appendChild(script);
  }

  function loadConfig() {
    return publicApi.settings().then((res) => {
      const s = res.data.data || {};
      googleClientId.value = s.google_client_id || '';
      appleClientId.value = s.apple_client_id || '';
      if (googleClientId.value) {
        loadScript(GOOGLE_SCRIPT, () => {
          googleScriptLoaded.value = true;
        });
      }
      if (appleClientId.value) {
        loadScript(APPLE_SCRIPT, () => {
          appleScriptLoaded.value = true;
        });
      }
    });
  }

  function ensureGoogleReady() {
    return new Promise((resolve) => {
      if (window.google?.accounts?.id) {
        resolve(true);
        return;
      }
      if (!googleClientId.value) {
        resolve(false);
        return;
      }
      loadScript(GOOGLE_SCRIPT, () => resolve(Boolean(window.google?.accounts?.id)));
    });
  }

  async function googleSignIn() {
    if (!googleClientId.value) {
      handleUnconfigured('Google');
      return;
    }
    socialBusy.value = true;
    socialError.value = '';
    const ready = await ensureGoogleReady();
    if (!ready) {
      socialError.value = 'Could not load Google Sign-In. Please try again.';
      socialBusy.value = false;
      return;
    }
    try {
      window.google.accounts.id.initialize({
        client_id: googleClientId.value,
        callback: (response) => handleCredential(response.credential, 'google'),
        ux_mode: 'popup',
        auto_select: false,
      });
      window.google.accounts.id.prompt((notification) => {
        if (notification?.getNotDisplayedReason?.()) {
          socialError.value = 'Google sign-in was blocked by this browser (private/incognito mode or third-party cookies disabled). Please use a normal window or sign in with email.';
        }
        socialBusy.value = false;
      });
    } catch (error) {
      socialError.value = 'Could not start Google sign-in. Please try again.';
      socialBusy.value = false;
    }
  }

  function handleUnconfigured(provider) {
    socialError.value = `${provider} sign-in isn't configured yet. Add your ${provider} credentials in Admin → Integrations.`;
  }

  async function handleCredential(credential, provider) {
    socialBusy.value = true;
    socialError.value = '';
    try {
      await socialLogin({
        provider,
        id_token: credential,
        user_data: {},
      });
      await router.push(redirectAfterLogin());
    } catch (error) {
      socialError.value = error?.response?.data?.error || 'Could not sign you in with this provider. Please try again.';
    } finally {
      socialBusy.value = false;
    }
  }

  function ensureAppleReady() {
    return new Promise((resolve) => {
      if (appleScriptLoaded.value && window.AppleID) {
        initApple();
        resolve(true);
        return;
      }
      if (!appleClientId.value) {
        resolve(false);
        return;
      }
      loadScript(APPLE_SCRIPT, () => {
        appleScriptLoaded.value = true;
        initApple();
        resolve(Boolean(window.AppleID));
      });
    });
  }

  function initApple() {
    if (!window.AppleID?.auth || !appleClientId.value) {
      return;
    }
    window.AppleID.auth.init({
      clientId: appleClientId.value,
      scope: 'name email',
      redirectURI: window.location.origin,
      usePopup: true,
      state: `cc_${Date.now()}`,
    });
  }

  async function appleSignIn() {
    if (!appleClientId.value) {
      handleUnconfigured('Apple');
      return;
    }
    socialBusy.value = true;
    socialError.value = '';
    const ready = await ensureAppleReady();
    if (!ready) {
      handleUnconfigured('Apple');
      socialBusy.value = false;
      return;
    }
    try {
      const response = await window.AppleID.auth.signIn();
      const idToken = response?.authorization?.id_token;
      if (!idToken) {
        throw new Error('no_token');
      }
      const user = response?.user;
      const name = user?.name
        ? [user.name.firstName, user.name.lastName].filter(Boolean).join(' ')
        : '';
      await socialLogin({
        provider: 'apple',
        id_token: idToken,
        user_data: { name },
      });
      await router.push(redirectAfterLogin());
    } catch (error) {
      if (error?.error !== 'user_cancelled') {
        socialError.value = 'Could not sign you in with Apple. Please try again.';
      }
    } finally {
      socialBusy.value = false;
    }
  }

  onMounted(loadConfig);

  return {
    googleConfigured,
    appleConfigured,
    socialBusy,
    socialError,
    googleSignIn,
    handleUnconfigured,
    appleSignIn,
    loadConfig,
  };
}