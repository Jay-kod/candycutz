import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const distDir = path.resolve(__dirname, '../dist');
const apiPublicDir = path.resolve(__dirname, '../../barbing-saloon-api/public');

if (!fs.existsSync(distDir)) {
  console.error('❌ Web dist directory does not exist. Run `vite build` first.');
  process.exit(1);
}

if (fs.existsSync(apiPublicDir)) {
  console.log('🔄 Syncing web dist to barbing-saloon-api/public for local server...');

  // 1. Copy new assets and static files before updating entry files so concurrent
  //    requests never observe an entry HTML referencing assets not yet on disk.
  fs.cpSync(distDir, apiPublicDir, {
    recursive: true,
    force: true,
    filter: (src) => {
      const base = path.basename(src);
      return (
        base !== 'index.php' &&
        base !== '.htaccess' &&
        base !== 'index.html' &&
        base !== 'sw.js'
      );
    },
  });

  // 2. Switch entry HTML and service worker once all referenced assets exist.
  for (const entryFile of ['index.html', 'sw.js']) {
    const srcFile = path.join(distDir, entryFile);
    if (fs.existsSync(srcFile)) {
      fs.copyFileSync(srcFile, path.join(apiPublicDir, entryFile));
    }
  }

  // 3. Prune stale hashed assets from prior builds after the new build is live.
  const distAssetsDir = path.join(distDir, 'assets');
  const apiAssetsDir = path.join(apiPublicDir, 'assets');
  if (fs.existsSync(distAssetsDir) && fs.existsSync(apiAssetsDir)) {
    const currentAssets = new Set(fs.readdirSync(distAssetsDir));
    for (const existing of fs.readdirSync(apiAssetsDir)) {
      if (!currentAssets.has(existing)) {
        fs.rmSync(path.join(apiAssetsDir, existing), { recursive: true, force: true });
      }
    }
  }

  console.log('✅ Successfully synced web dist to barbing-saloon-api/public!');
}
