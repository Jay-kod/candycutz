#!/usr/bin/env node

/**
 * CandyCutz Responsiveness & Overflow Linter
 * 
 * Static analysis engine that enforces mobile-first, fluid, and responsive design patterns
 * across all Vue templates and styles. Prevents rigid fixed widths, uncontained tables,
 * un-padded mobile safe areas, and mobile viewport address bar layout shift.
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const srcDir = path.resolve(__dirname, '../src');

const VIOLATIONS = [];
let filesScanned = 0;
const startTime = Date.now();

/**
 * Recursively find all files in directory matching extensions
 */
function walk(dir, ext = ['.vue', '.css']) {
  let results = [];
  try {
    const list = fs.readdirSync(dir);
    for (const file of list) {
      const fullPath = path.join(dir, file);
      const stat = fs.statSync(fullPath);
      if (stat && stat.isDirectory()) {
        results = results.concat(walk(fullPath, ext));
      } else if (ext.some(e => file.endsWith(e))) {
        results.push(fullPath);
      }
    }
  } catch (err) {
    console.error(`Error reading directory: ${dir}`, err);
  }
  return results;
}

/**
 * Audit an individual file for anti-responsive patterns
 */
function auditFile(filePath) {
  const content = fs.readFileSync(filePath, 'utf8');
  const relPath = path.relative(srcDir, filePath);
  const lines = content.split('\n');

  // Check file-level ignore directives
  if (content.includes('audit-ignore-file: responsiveness')) {
    return;
  }

  // File-level check for raw <table> tags without overflow-x-auto
  if (filePath.endsWith('.vue') && content.includes('<table')) {
    const hasOverflowWrapper = content.includes('overflow-x-auto') || content.includes('overflow-x-scroll');
    const isHiddenOnMobile = content.includes('hidden md:block') || content.includes('hidden lg:block');
    if (!hasOverflowWrapper && !isHiddenOnMobile && !content.includes('audit-ignore: table-overflow')) {
      VIOLATIONS.push({
        file: relPath,
        line: 1,
        severity: 'WARN',
        rule: 'TABLE_OVERFLOW_CONTAINMENT',
        message: 'Table detected without "overflow-x-auto" or mobile-hidden wrapper. Use BaseTable.vue or wrap table in an overflow-x-auto container.',
        snippet: '<table> tag inside template'
      });
    }
  }

  lines.forEach((line, index) => {
    const lineNum = index + 1;

    // Skip lines with explicit audit ignore comment
    if (line.includes('audit-ignore:') || line.includes('responsive-audit-ignore')) {
      return;
    }

    // 1. Check for un-prefixed arbitrary fixed widths > 320px: e.g. w-[380px], min-w-[500px]
    // Allow max-w-[...px] or responsive prefixes like sm:w-[...], md:min-w-[...]
    const fixedWidthRegex = /(?:^|[\s"'])((?:xxs|xs|sm|md|lg|xl|2xl):)?(w|min-w)-\[(\d{3,4})px\]/g;
    let match;
    while ((match = fixedWidthRegex.exec(line)) !== null) {
      const prefix = match[1]; // e.g. "md:"
      const prop = match[2];   // "w" or "min-w"
      const size = parseInt(match[3], 10);

      if (!prefix && size > 320) {
        // Exclude specific safe graphics or blur backgrounds or max-w-full constrained elements
        if (
          !line.includes('rounded-full') &&
          !line.includes('blur-') &&
          !line.includes('pointer-events-none') &&
          !line.includes('max-w-full') &&
          !line.includes('max-w-')
        ) {
          VIOLATIONS.push({
            file: relPath,
            line: lineNum,
            severity: 'ERROR',
            rule: 'NO_OVERSIZED_FIXED_WIDTH',
            message: `Found hardcoded un-prefixed ${prop}-[${size}px] (> 320px). Use responsive prefixes (e.g. sm:${prop}-[${size}px]) or max-w-*.`,
            snippet: line.trim()
          });
        }
      }
    }

    // 2. Check for un-prefixed Tailwind scale classes that exceed standard mobile viewports (e.g. w-80 = 320px, w-96 = 384px)
    const scaleWidthRegex = /(?:^|[\s"'])((?:xxs|xs|sm|md|lg|xl|2xl):)?(w|min-w)-(80|96)\b/g;
    let scaleMatch;
    while ((scaleMatch = scaleWidthRegex.exec(line)) !== null) {
      const prefix = scaleMatch[1];
      const prop = scaleMatch[2];
      const scale = scaleMatch[3];

      if (!prefix) {
        if (
          !line.includes('rounded-full') &&
          !line.includes('blur-') &&
          !line.includes('pointer-events-none') &&
          !line.includes('max-w-full') &&
          !line.includes('max-w-')
        ) {
          VIOLATIONS.push({
            file: relPath,
            line: lineNum,
            severity: 'ERROR',
            rule: 'NO_OVERSIZED_TAILWIND_SCALE',
            message: `Found hardcoded un-prefixed ${prop}-${scale} (≥ 320px). Prefix with responsive tier (e.g. sm:${prop}-${scale}) to prevent mobile overflow.`,
            snippet: line.trim()
          });
        }
      }
    }

    // 3. Check for inline fixed width styles exceeding 320px (e.g. style="width: 400px")
    const inlineStyleRegex = /style="[^"]*?(?:width|min-width):\s*(\d{3,4})px/g;
    let inlineMatch;
    while ((inlineMatch = inlineStyleRegex.exec(line)) !== null) {
      const size = parseInt(inlineMatch[1], 10);
      if (size > 320 && !line.includes('max-width')) {
        VIOLATIONS.push({
          file: relPath,
          line: lineNum,
          severity: 'ERROR',
          rule: 'NO_INLINE_FIXED_WIDTH',
          message: `Found inline fixed style width: ${size}px (> 320px). Use responsive utility classes or fluid percentages.`,
          snippet: line.trim()
        });
      }
    }

    // 4. Check for raw `h-screen` without dynamic viewport handling in templates (ignore CSS class selectors)
    if (/\b(?<!min-)h-screen\b/.test(line) && !line.includes('100dvh') && !line.includes('max-h-screen') && !line.includes('.h-screen')) {
      VIOLATIONS.push({
        file: relPath,
        line: lineNum,
        severity: 'WARN',
        rule: 'USE_DYNAMIC_VIEWPORT_HEIGHT',
        message: 'Found raw "h-screen". Consider using "h-[100dvh]" or "min-h-screen" to avoid mobile browser address bar layout jumps.',
        snippet: line.trim()
      });
    }

    // 5. Check for fixed bottom elements without safe-area inset
    if (/\bfixed\s+[^"']*bottom-0\b/.test(line) && !line.includes('pb-safe') && !line.includes('safe-area')) {
      VIOLATIONS.push({
        file: relPath,
        line: lineNum,
        severity: 'WARN',
        rule: 'SAFE_AREA_BOTTOM_INSET',
        message: 'Fixed bottom element should include "pb-safe" or env(safe-area-inset-bottom) for modern mobile home indicators.',
        snippet: line.trim()
      });
    }
  });

  filesScanned++;
}

console.log('🔍 CandyCutz Responsiveness & Overflow Linter running...');
const files = walk(srcDir);
files.forEach(auditFile);
const durationMs = Date.now() - startTime;

console.log(`\n📊 Scan Complete: Scanned ${filesScanned} files in ${durationMs}ms.\n`);

const errors = VIOLATIONS.filter(v => v.severity === 'ERROR');
const warnings = VIOLATIONS.filter(v => v.severity === 'WARN');

if (VIOLATIONS.length > 0) {
  console.log(`Found ${errors.length} error(s) and ${warnings.length} warning(s):\n`);

  VIOLATIONS.forEach(v => {
    const badge = v.severity === 'ERROR' ? '\x1b[31m[ERROR]\x1b[0m' : '\x1b[33m[WARN]\x1b[0m';
    console.log(`${badge} ${v.file}:${v.line} (${v.rule})`);
    console.log(`   Message: ${v.message}`);
    console.log(`   Code:    ${v.snippet.substring(0, 100)}\n`);
  });

  if (errors.length > 0) {
    console.error(`\x1b[31m❌ Responsiveness Audit Failed with ${errors.length} critical error(s).\x1b[0m`);
    process.exit(1);
  } else {
    console.log(`\x1b[33m⚠️ Audit passed with ${warnings.length} warning(s).\x1b[0m`);
    process.exit(0);
  }
} else {
  console.log('\x1b[32m✅ Zero responsiveness violations found across all scanned files!\x1b[0m');
  process.exit(0);
}
