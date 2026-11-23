import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { publicApi } from '@/shared/api/old_publicApi';
import { useAuth } from './useAuth';

const GOOGLE_SCRIPT = 'https://accounts.google.com/gsi/client';
const APPLE_SCRIPT = 'https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js';

/** @type {any} */
const win = typeof window !== 'undefined' ? window : {};

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
    const existing = document.querySelector(`script[data-candycutz-src="${src}"]`);
    if (existing) {
      if (existing.dataset.candycutzLoaded !== 'true' && existing.dataset.candycutzFailed === 'true') {
        existing.remove();
      } else {
        existing.addEventListener('load', onLoad, { once: true });
        existing.addEventListener('error', () => {
          existing.dataset.candycutzFailed = 'true';
        }, { once: true });
        if (win.google?.accounts?.id && src === GOOGLE_SCRIPT) {
          existing.dataset.candycutzLoaded = 'true';
          onLoad();
        }
        return;
      }
    }
    const script = document.createElement('script');
    script.src = src;
    script.async = true;
    script.dataset.candycutzSrc = src;
    script.onload = () => {
      script.dataset.candycutzLoaded = 'true';
      onLoad();
    };
    script.onerror = () => {
      script.dataset.candycutzFailed = 'true';
    };
    document.head.appendChild(script);
  }

  function loadConfig() {
    const loadOnce = () => publicApi.settings().then((res) => {
      const s = res.data?.data || res.data || {};
      googleClientId.value = String(s.google_client_id || s.social?.google_client_id || '').trim();
      appleClientId.value = String(s.apple_client_id || s.social?.apple_client_id || '').trim();
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

    return loadOnce().catch(async () => {
      await new Promise((resolve) => setTimeout(resolve, 500));
      try {
        await loadOnce();
      } catch {
        // Settings remain unconfigured until the next page load or retry.
      }
    });
  }

  function ensureGoogleReady() {
    return new Promise((resolve) => {
      if (win.google?.accounts?.id) {
        resolve(true);
        return;
      }
      if (!googleClientId.value) {
        resolve(false);
        return;
      }
      let settled = false;
      const finish = (ready) => {
        if (settled) return;
        settled = true;
        resolve(ready);
      };
      loadScript(GOOGLE_SCRIPT, () => finish(Boolean(win.google?.accounts?.id)));
      const deadline = setInterval(() => {
        if (win.google?.accounts?.id) {
          clearInterval(deadline);
          finish(true);
        }
      }, 100);
      setTimeout(() => {
        clearInterval(deadline);
        finish(Boolean(win.google?.accounts?.id));
      }, 10000);
    });
  }

  async function googleSignIn() {
    if (!googleClientId.value) {
      await loadConfig();
    }
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
      win.google.accounts.id.initialize({
        client_id: googleClientId.value,
        callback: (response) => handleCredential(response.credential, 'google'),
        ux_mode: 'popup',
        auto_select: false,
      });
      win.google.accounts.id.prompt((notification) => {
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

  async function renderGoogleButton(container) {
    if (!container) return;
    if (!googleClientId.value) {
      await loadConfig();
    }
    if (!googleClientId.value) {
      handleUnconfigured('Google');
      return;
    }
    const ready = await ensureGoogleReady();
    if (!ready) {
      socialError.value = 'Could not load Google Sign-In. Please try again.';
      return;
    }

    try {
      container.replaceChildren();
      win.google.accounts.id.initialize({
        client_id: googleClientId.value,
        callback: (response) => handleCredential(response.credential, 'google'),
        ux_mode: 'popup',
        auto_select: false,
      });
      win.google.accounts.id.renderButton(container, {
        type: 'standard',
        theme: 'outline',
        size: 'large',
        shape: 'pill',
        text: 'continue_with',
        width: Math.min(Math.max(container.offsetWidth || 260, 180), 400),
      });
    } catch (error) {
      socialError.value = 'Could not start Google sign-in. Please try again.';
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
      if (appleScriptLoaded.value && win.AppleID) {
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
        resolve(Boolean(win.AppleID));
      });
    });
  }

  function initApple() {
    if (!win.AppleID?.auth || !appleClientId.value) {
      return;
    }
    win.AppleID.auth.init({
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
      const response = await win.AppleID.auth.signIn();
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
    renderGoogleButton,
    handleUnconfigured,
    appleSignIn,
    loadConfig,
  };
}