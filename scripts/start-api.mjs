import { spawn } from 'node:child_process';
import http from 'node:http';
import net from 'node:net';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const apiDir = path.join(rootDir, 'barbing-saloon-api');
const host = '127.0.0.1';
const port = 8000;

function checkPort(timeout = 1000) {
  return new Promise((resolve) => {
    const socket = new net.Socket();
    socket.setTimeout(timeout);
    socket.once('connect', () => {
      socket.destroy();
      resolve(true);
    });
    socket.once('timeout', () => {
      socket.destroy();
      resolve(false);
    });
    socket.once('error', () => resolve(false));
    socket.connect(port, host);
  });
}

function checkApiHealth(timeout = 2500) {
  return new Promise((resolve) => {
    const request = http.get({ hostname: host, port, path: '/api/v1/health' }, (response) => {
      let body = '';
      response.setEncoding('utf8');
      response.on('data', (chunk) => {
        body += chunk;
        if (body.length > 8192) request.destroy();
      });
      response.on('end', () => {
        try {
          const health = JSON.parse(body);
          resolve(response.statusCode === 200 && health.data?.services?.database === 'connected');
        } catch {
          resolve(false);
        }
      });
    });

    request.setTimeout(timeout, () => request.destroy());
    request.on('error', () => resolve(false));
  });
}

async function main() {
  if (await checkPort()) {
    if (await checkApiHealth()) {
      console.log(`[API] A healthy API is already running on ${host}:${port}; not starting a duplicate.`);
      return;
    }

    throw new Error(
      `Port ${port} is already occupied but the API health check failed. Stop the stale API process before retrying; no second server was started.`
    );
  }

  const server = spawn('php', ['-S', `${host}:${port}`, '-t', 'public', 'public/index.php'], {
    cwd: apiDir,
    stdio: 'inherit',
  });
  let stopping = false;

  const stop = () => {
    stopping = true;
    server.kill('SIGINT');
  };

  process.once('SIGINT', stop);
  process.once('SIGTERM', stop);

  server.once('error', (error) => {
    console.error(`[API] Could not start PHP server: ${error.message}`);
    process.exitCode = 1;
  });

  server.once('exit', (code) => {
    process.exitCode = stopping ? 0 : (code || 1);
  });
}

main().catch((error) => {
  console.error(`[API] ${error.message}`);
  process.exitCode = 1;
});
