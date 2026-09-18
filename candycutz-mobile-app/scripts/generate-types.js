const fs = require('fs');
const path = require('path');

const openApiPath = path.resolve(__dirname, '../../docs/openapi.json');
const outputPath = path.resolve(__dirname, '../src/api/types.ts');

if (!fs.existsSync(openApiPath)) {
  console.error(`OpenAPI spec not found at: ${openApiPath}`);
  process.exit(1);
}

const spec = JSON.parse(fs.readFileSync(openApiPath, 'utf-8'));
const schemas = spec.components?.schemas || {};

function mapPropertyType(prop) {
  if (!prop) return 'any';
  if (prop.$ref) {
    return prop.$ref.split('/').pop();
  }
  if (prop.enum) {
    return prop.enum.map(e => `'${e}'`).join(' | ');
  }
  if (prop.type === 'array') {
    const itemType = mapPropertyType(prop.items);
    return `${itemType}[]`;
  }
  if (prop.type === 'integer' || prop.type === 'number') {
    return 'number';
  }
  if (prop.type === 'boolean') {
    return 'boolean';
  }
  if (prop.type === 'string') {
    return 'string';
  }
  if (prop.type === 'object') {
    if (prop.properties) {
      const innerProps = Object.entries(prop.properties).map(([k, v]) => {
        const opt = prop.required?.includes(k) ? '' : '?';
        return `    ${k}${opt}: ${mapPropertyType(v)};`;
      }).join('\n');
      return `{\n${innerProps}\n  }`;
    }
    return 'Record<string, any>';
  }
  return 'any';
}

const lines = [];
lines.push('/**',
  ' * AUTO-GENERATED FROM OPENAPI 3.0 SPEC (docs/openapi.json)',
  ' * DO NOT EDIT DIRECTLY. Run `npm run generate:types` to regenerate.',
  ' */',
  ''
);

// Specific domain type unions
lines.push(
  "export type UserRole = 'customer' | 'barber' | 'admin' | 'superadmin' | 'super_admin';",
  "export type ChairStatus = 'free' | 'busy' | 'break' | 'offline';",
  "export type AppointmentStatus = 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled' | 'no_show';",
  "export type PaymentStatus = 'pending' | 'paid' | 'partially_paid' | 'refunded' | 'failed' | 'awaiting_transfer' | 'receipt_uploaded' | 'under_review' | 'verified' | 'rejected' | 'successful';",
  "export type PlatformType = 'ios' | 'android' | 'web';",
  ''
);

// Generate interfaces for all schemas
for (const [name, schema] of Object.entries(schemas)) {
  if (name === 'ApiResponse') {
    lines.push(
      'export interface ApiResponse<T = any> {',
      '  success: boolean;',
      '  message?: string;',
      '  data: T;',
      '  meta?: {',
      '    current_page?: number;',
      '    last_page?: number;',
      '    per_page?: number;',
      '    total?: number;',
      '  };',
      '}',
      ''
    );
    continue;
  }

  const props = schema.properties || {};
  const required = schema.required || [];

  lines.push(`export interface ${name} {`);
  for (const [propName, propDef] of Object.entries(props)) {
    const isRequired = required.includes(propName);
    const opt = isRequired ? '' : '?';
    let typeStr = mapPropertyType(propDef);
    if (propDef.nullable && !typeStr.includes('null')) {
      typeStr += ' | null';
    }
    lines.push(`  ${propName}${opt}: ${typeStr};`);
  }
  lines.push('}', '');
}

// Add extra helper interfaces if needed by mobile app
lines.push(
  'export interface WeeklyScheduleDay {',
  '  id?: number | null;',
  '  day_of_week: number;',
  '  day_name: string;',
  '  is_working?: boolean;',
  '  is_closed?: boolean;',
  '  is_off?: boolean;',
  '  open_time?: string;',
  '  close_time?: string;',
  '  start_time: string;',
  '  end_time: string;',
  '}',
  '',
  'export interface BlockedPeriod {',
  '  id: number;',
  '  barber_id: number;',
  '  start_datetime: string;',
  '  end_datetime: string;',
  '  reason?: string;',
  '}',
  '',
  'export interface TimeSlot {',
  '  time: string;',
  '  available: boolean;',
  '  barber_id?: number;',
  '}',
  '',
  'export interface BarberProfile extends Barber {',
  '  today_cuts_count?: number;',
  '  today_earnings?: number;',
  '}',
  ''
);

fs.writeFileSync(outputPath, lines.join('\n'), 'utf-8');
console.log(`Generated types saved to: ${outputPath}`);
