import { spawn } from 'node:child_process';
import path from 'node:path';
import net from 'node:net';
import http from 'node:http';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.resolve(__dirname, '..');
const webDir = path.join(rootDir, 'barbing-saloon-web');

console.log('========================================');
console.log('  CandyCutz Unified Development Runner  ');
console.log('========================================');

// Function to check if a TCP port is open
function checkPort(port, host = '127.0.0.1', timeout = 1000) {
  return new Promise((resolve) => {
    const socket = new net.Socket();
    let isConnected = false;

    socket.setTimeout(timeout);
    socket.once('connect', () => {
      isConnected = true;
      socket.destroy();
      resolve(true);
    });

    socket.once('timeout', () => {
      socket.destroy();
      resolve(false);
    });

    socket.once('error', () => {
      resolve(false);
    });

    socket.connect(port, host);
  });
}

function checkApiHealth(timeout = 2500) {
  return new Promise((resolve) => {
    const request = http.get(
      { hostname: '127.0.0.1', port: 8000, path: '/api/v1/health' },
      (response) => {
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
      }
    );

    request.setTimeout(timeout, () => request.destroy());
    request.on('error', () => resolve(false));
  });
}

// Check and attempt to start MySQL if in XAMPP
async function ensureMySQL() {
  const mysqlRunning = await checkPort(3306);
  if (mysqlRunning) {
    console.log('  [DB]  MySQL detected active on port 3306.');
    return;
  }

  console.log('  [DB]  MySQL is not currently listening on port 3306.');
  const xamppMysql = 'C:\\xampp\\mysql_start.bat';
  if (fs.existsSync(xamppMysql)) {
    console.log('  [DB]  Triggering XAMPP MySQL starter...');
    try {
      spawn('cmd.exe', ['/c', xamppMysql], { detached: true, stdio: 'ignore' });
      for (let i = 0; i < 5; i++) {
        await new Promise((r) => setTimeout(r, 1000));
        if (await checkPort(3306)) {
          console.log('  [DB]  XAMPP MySQL is now operational!');
          return;
        }
      }
    } catch {
      // Ignored; user can start manually via XAMPP Control Panel
    }
  }
  console.log('  [DB]  Tip: Ensure MySQL is started in XAMPP Control Panel.');
}

// Managed Service Runner with Auto-Restart
class ManagedService {
  constructor({ name, command, args, cwd, color, restartOnSuccessfulExit = true }) {
    this.name = name;
    this.command = command;
    this.args = args;
    this.cwd = cwd;
    this.color = color;
    this.restartOnSuccessfulExit = restartOnSuccessfulExit;
    this.process = null;
    this.shouldRestart = true;
  }

  start() {
    console.log(`${this.color}[${this.name}]${'\x1b[0m'} Initializing service...`);
    const isWindows = process.platform === 'win32';

    this.process = spawn(this.command, this.args, {
      cwd: this.cwd,
      shell: isWindows,
      stdio: ['ignore', 'pipe', 'pipe'],
    });

    this.process.stdout.on('data', (chunk) => {
      const lines = chunk.toString().split('\n');
      for (const line of lines) {
        if (line.trim()) {
          console.log(`${this.color}[${this.name}]${'\x1b[0m'} ${line}`);
        }
      }
    });

    this.process.stderr.on('data', (chunk) => {
      const lines = chunk.toString().split('\n');
      for (const line of lines) {
        if (line.trim()) {
          console.error(`${this.color}[${this.name}]${'\x1b[0m'} ${line}`);
        }
      }
    });

    this.process.on('close', (code) => {
      if (this.shouldRestart && (code !== 0 || this.restartOnSuccessfulExit)) {
        console.warn(`${this.color}[${this.name}]${'\x1b[0m'} Service stopped (exit code ${code}). Auto-restarting in 2s...`);
        setTimeout(() => {
          if (this.shouldRestart) this.start();
        }, 2000);
      } else if (this.shouldRestart) {
        console.log(`${this.color}[${this.name}]${'\x1b[0m'} Existing service was reused; no duplicate will be started.`);
      }
    });
  }

  stop() {
    this.shouldRestart = false;
    if (this.process) {
      if (process.platform === 'win32') {
        spawn('taskkill', ['/pid', String(this.process.pid), '/f', '/t'], { stdio: 'ignore' });
      } else {
        this.process.kill('SIGTERM');
      }
    }
  }
}

async function main() {
  await ensureMySQL();

  let apiService = null;
  if (await checkPort(8000)) {
    if (!(await checkApiHealth())) {
      throw new Error(
        'Port 8000 is occupied, but /api/v1/health is not healthy. Stop the stale API process before starting the development stack; refusing to create a duplicate.'
      );
    }
    console.log('  [API] Healthy API already active on port 8000; reusing it.');
  } else {
    apiService = new ManagedService({
      name: 'API',
      command: 'node',
      args: [path.join(rootDir, 'scripts', 'start-api.mjs')],
      cwd: rootDir,
      color: '\x1b[36m', // Cyan
      restartOnSuccessfulExit: false,
    });
    apiService.start();
  }

  const webService = new ManagedService({
    name: 'WEB',
    command: 'npm',
    args: ['run', 'dev'],
    cwd: webDir,
    color: '\x1b[32m', // Green
  });

  webService.start();

  console.log('\n  Services Active:');
  console.log('   - Web Frontend: http://localhost:5173');
  console.log('   - Backend API:  http://127.0.0.1:8000');
  console.log(`   - API: ${apiService ? 'managed with automatic restart' : 'reusing existing healthy service'}`);
  console.log('   - Auto-Restart: Active for runner-managed services');
  console.log('   - Press Ctrl+C to terminate all services.\n');

  function shutdown() {
    console.log('\n  Terminating services cleanly...');
    apiService?.stop();
    webService.stop();
    process.exit(0);
  }

  process.on('SIGINT', shutdown);
  process.on('SIGTERM', shutdown);
}

main().catch((err) => {
  console.error('Fatal runner error:', err);
  process.exit(1);
});
