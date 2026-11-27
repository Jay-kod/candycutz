import { describe, expect, it, vi, beforeEach } from 'vitest'

const { mockPublicApi } = vi.hoisted(() => ({
  mockPublicApi: {
    gallery: vi.fn(),
    services: vi.fn(),
    settings: vi.fn(),
    serviceCategories: vi.fn(),
    testimonials: vi.fn(),
  },
}))

vi.mock('@/shared/api/old_publicApi', () => ({
  publicApi: mockPublicApi,
}))

import { createPinia, setActivePinia } from 'pinia'
import { usePublicContentStore } from '../stores/publicContent.store'
import { compressImageFile } from '@/shared/utils/compressImage'

describe('publicContent store SWR + session persistence & compressImage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    window.sessionStorage.clear()
    setActivePinia(createPinia())
  })

  it('persists gallery items in sessionStorage and serves cached data immediately across reloads', async () => {
    mockPublicApi.gallery.mockResolvedValue({
      data: { data: [{ id: 1, title: 'Low Taper', thumbnail_url: '/storage/uploads/gallery/thumb_abc.webp' }] },
    })

    const store = usePublicContentStore()
    const first = await store.getGallery()
    expect(first).toHaveLength(1)
    expect(mockPublicApi.gallery).toHaveBeenCalledTimes(1)

    // Simulate navigating away and coming back / reloading Pinia from sessionStorage
    setActivePinia(createPinia())
    const freshPiniaStore = usePublicContentStore()
    expect(freshPiniaStore.gallery).toHaveLength(1)

    const cached = await freshPiniaStore.getGallery()
    expect(cached[0].title).toBe('Low Taper')
    // Already fresh within TTL — zero extra API request
    expect(mockPublicApi.gallery).toHaveBeenCalledTimes(1)
  })

  it('skips client-side compression for small files and PDF documents safely', async () => {
    const smallImg = new File([new Uint8Array(1024)], 'tiny.png', { type: 'image/png' })
    const pdf = new File([new Uint8Array(500 * 1024)], 'receipt.pdf', { type: 'application/pdf' })

    expect(await compressImageFile(smallImg)).toBe(smallImg)
    expect(await compressImageFile(pdf)).toBe(pdf)
  })
})
