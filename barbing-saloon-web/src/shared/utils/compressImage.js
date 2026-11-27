/**
 * Best-effort client-side image resize + WebP pre-compression before multipart upload.
 * Keeps large camera photos fast on slow mobile networks while falling back safely
 * to the original file whenever a format or browser lacks canvas WebP support.
 */
export async function compressImageFile(
  file,
  { maxDimension = 1920, quality = 0.85, minBytesToCompress = 262144 } = {}
) {
  if (!file || typeof window === 'undefined' || typeof document === 'undefined') {
    return file
  }

  const mime = String(file.type || '').toLowerCase()
  const compressibleTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
  if (!compressibleTypes.includes(mime) || file.size < minBytesToCompress) {
    return file
  }

  let objectUrl = null
  let canvas = null
  try {
    objectUrl = URL.createObjectURL(file)
    const img = await new Promise((resolve, reject) => {
      const el = new Image()
      el.onload = () => resolve(el)
      el.onerror = reject
      el.src = objectUrl
    })

    const srcWidth = img.naturalWidth || img.width
    const srcHeight = img.naturalHeight || img.height
    if (!srcWidth || !srcHeight) return file

    const scale = Math.min(1, maxDimension / Math.max(srcWidth, srcHeight))
    const targetWidth = Math.max(1, Math.round(srcWidth * scale))
    const targetHeight = Math.max(1, Math.round(srcHeight * scale))

    canvas = document.createElement('canvas')
    canvas.width = targetWidth
    canvas.height = targetHeight
    const ctx = canvas.getContext('2d')
    if (!ctx) return file

    ctx.drawImage(img, 0, 0, targetWidth, targetHeight)

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', quality))
    if (!blob || blob.type !== 'image/webp' || blob.size >= file.size) {
      return file
    }

    const baseName = file.name.replace(/\.[^.]+$/, '') || 'upload'
    return new File([blob], `${baseName}.webp`, {
      type: 'image/webp',
      lastModified: Date.now(),
    })
  } catch {
    return file
  } finally {
    if (canvas) {
      canvas.width = 0
      canvas.height = 0
    }
    if (objectUrl) {
      URL.revokeObjectURL(objectUrl)
    }
  }
}
