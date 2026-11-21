import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const distDir = path.resolve(__dirname, '../dist');
const apiPublicDir = path.resolve(__dirname, '../../barbing-saloon-api/public');

if (fs.existsSync(apiPublicDir) && fs.existsSync(distDir)) {
  console.log('🔄 Syncing web dist to barbing-saloon-api/public for local server...');
  fs.cpSync(distDir, apiPublicDir, {
    recursive: true,
    force: true,
    filter: (src) => {
      const base = path.basename(src);
      return base !== 'index.php' && base !== '.htaccess';
    }
  });
  console.log('✅ Successfully synced web dist to barbing-saloon-api/public!');
}
