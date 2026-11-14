export function getApiCollection(response) {
  const payload = response?.data?.data;
  if (Array.isArray(payload)) return payload;
  return Array.isArray(payload?.data) ? payload.data : [];
}
