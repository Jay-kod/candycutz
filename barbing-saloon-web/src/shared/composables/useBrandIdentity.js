import { ref } from 'vue';
import { publicApi } from '@/shared/api/old_publicApi';

const brandName = ref('CandyCutz');
const brandTagline = ref(
  'A sharper standard in executive grooming. Premium precision cuts, tailored beard artistry, and royal styling in a relaxing, luxurious environment.'
);
let loaded = false;
let loadingPromise = null;

export const applyBrandSettings = (settings = {}) => {
  const name = settings.brand_name || settings.site_name;
  const taglineKey = ['brand_tagline', 'site_description', 'meta_description']
    .find((key) => Object.prototype.hasOwnProperty.call(settings, key) && typeof settings[key] === 'string');

  if (typeof name === 'string' && name.trim()) brandName.value = name.trim();
  if (taglineKey) brandTagline.value = settings[taglineKey].trim();
  if (typeof document !== 'undefined') document.title = brandName.value;
};

export const useBrandIdentity = () => {
  const loadBrandIdentity = async () => {
    if (loaded) return;
    if (!loadingPromise) {
      loadingPromise = publicApi.settings()
        .then((response) => {
          applyBrandSettings(response.data?.data || {});
          loaded = true;
        })
        .catch((error) => {
          console.error('Failed to load brand identity:', error);
        })
        .finally(() => {
          loadingPromise = null;
        });
    }

    await loadingPromise;
  };

  return { brandName, brandTagline, loadBrandIdentity, applyBrandSettings };
};
