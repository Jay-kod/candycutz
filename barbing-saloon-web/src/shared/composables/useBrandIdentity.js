import { ref } from 'vue';
import { publicApi } from '@/shared/api/old_publicApi';

const brandName = ref('CandyCutz');
const brandNameFirst = ref('Candy');
const brandNameSecond = ref('Cutz');
const brandNameSeparator = ref('none');
const brandGoldPart = ref('second');
const brandTagline = ref(
  'A sharper standard in executive grooming. Premium precision cuts, tailored beard artistry, and royal styling in a relaxing, luxurious environment.'
);
let loaded = false;
let loadingPromise = null;

export const splitBrandName = (name) => {
  const trimmedName = name.trim();
  const lastSpace = trimmedName.lastIndexOf(' ');
  if (lastSpace > 0 && lastSpace < trimmedName.length - 1) {
    return [trimmedName.slice(0, lastSpace), trimmedName.slice(lastSpace + 1), 'space'];
  }

  const camelCaseMatch = trimmedName.match(/^(.+?)([A-Z][a-z].*)$/);
  if (camelCaseMatch) return [camelCaseMatch[1], camelCaseMatch[2], 'none'];

  const splitAt = Math.ceil(trimmedName.length / 2);
  return [trimmedName.slice(0, splitAt), trimmedName.slice(splitAt), 'none'];
};

export const applyBrandSettings = (settings = {}) => {
  const name = settings.brand_name || settings.site_name || settings.saloon_name;
  const taglineKey = ['brand_tagline', 'tagline', 'site_description', 'meta_description']
    .find((key) => Object.prototype.hasOwnProperty.call(settings, key) && typeof settings[key] === 'string');

  if (typeof name === 'string' && name.trim()) {
    brandName.value = name.trim();
    const [first, second, separator] = splitBrandName(brandName.value);
    brandNameFirst.value = typeof settings.brand_name_part_one === 'string' ? settings.brand_name_part_one : first;
    brandNameSecond.value = typeof settings.brand_name_part_two === 'string' ? settings.brand_name_part_two : second;
    brandNameSeparator.value = settings.brand_name_separator === 'space' || settings.brand_name_separator === 'none'
      ? settings.brand_name_separator
      : separator;
  }
  if (settings.brand_gold_part === 'first' || settings.brand_gold_part === 'second') {
    brandGoldPart.value = settings.brand_gold_part;
  }
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

  return {
    brandName,
    brandNameFirst,
    brandNameSecond,
    brandNameSeparator,
    brandGoldPart,
    brandTagline,
    loadBrandIdentity,
    applyBrandSettings,
  };
};
