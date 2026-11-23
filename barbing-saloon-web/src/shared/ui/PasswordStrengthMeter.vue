<template>
  <div class="password-strength-meter w-full mt-2" data-testid="password-strength-meter">
    <!-- Header with Label and Strength Rating -->
    <div class="flex items-center justify-between text-xs mb-1.5">
      <span class="text-[10px] uppercase tracking-wider text-theme-muted font-semibold">
        Password strength
      </span>
      <span
        v-if="password"
        :class="['text-[11px] font-bold transition-colors duration-200', currentLevel.textColor]"
        data-testid="strength-label"
      >
        {{ currentLevel.label }}
      </span>
      <span v-else class="text-[10px] text-theme-muted/60">
        Minimum 8 characters
      </span>
    </div>

    <!-- 4-Segment Progress Bar -->
    <div class="grid grid-cols-4 gap-1.5 w-full" role="progressbar" :aria-valuenow="score" aria-valuemin="0" aria-valuemax="4">
      <div
        v-for="index in 4"
        :key="index"
        :class="[
          'h-1.5 rounded-full transition-all duration-300',
          index <= score ? currentLevel.barColor : 'bg-theme-border/50'
        ]"
        :data-testid="`strength-bar-${index}`"
      ></div>
    </div>

    <!-- Live Requirements Checklist -->
    <div class="grid grid-cols-2 gap-x-2 gap-y-1 mt-2.5 text-[11px]">
      <div
        v-for="rule in rulesList"
        :key="rule.id"
        :class="[
          'flex items-center gap-1.5 transition-colors duration-200',
          rule.passed ? 'text-emerald-400 font-medium' : 'text-theme-muted/70'
        ]"
        :data-testid="`rule-${rule.id}`"
      >
        <!-- Checkmark icon when satisfied -->
        <svg
          v-if="rule.passed"
          class="w-3.5 h-3.5 text-emerald-400 shrink-0"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="3"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <!-- Subtle dot indicator when not yet satisfied -->
        <span
          v-else
          class="w-1.5 h-1.5 rounded-full bg-theme-border shrink-0 ml-1 mr-1"
        ></span>
        <span class="truncate">{{ rule.label }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  password: {
    type: String,
    default: '',
  },
});

const hasMinLength = computed(() => (props.password?.length || 0) >= 8);
const hasUppercase = computed(() => /[A-Z]/.test(props.password || ''));
const hasLowercase = computed(() => /[a-z]/.test(props.password || ''));
const hasNumber = computed(() => /[0-9]/.test(props.password || ''));
const hasSpecial = computed(() => /[^A-Za-z0-9]/.test(props.password || ''));

const rulesList = computed(() => [
  {
    id: 'min-length',
    label: '8+ characters',
    passed: hasMinLength.value,
  },
  {
    id: 'mixed-case',
    label: 'Upper & lower case',
    passed: hasUppercase.value && hasLowercase.value,
  },
  {
    id: 'number',
    label: 'At least 1 number',
    passed: hasNumber.value,
  },
  {
    id: 'special',
    label: 'At least 1 symbol',
    passed: hasSpecial.value,
  },
]);

const score = computed(() => {
  const pwd = props.password || '';
  if (!pwd) return 0;

  let points = 0;
  if (hasMinLength.value) points += 1;
  if (hasUppercase.value && hasLowercase.value) points += 1;
  if (hasNumber.value) points += 1;
  if (hasSpecial.value) points += 1;

  // Bonus for very strong passwords (length >= 12 and at least 3 rules)
  if (pwd.length >= 12 && points >= 3) {
    points = 4;
  }

  return Math.min(4, Math.max(1, points));
});

const currentLevel = computed(() => {
  switch (score.value) {
    case 1:
      return {
        label: 'Weak',
        textColor: 'text-red-400',
        barColor: 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.4)]',
      };
    case 2:
      return {
        label: 'Fair',
        textColor: 'text-amber-400',
        barColor: 'bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.4)]',
      };
    case 3:
      return {
        label: 'Good',
        textColor: 'text-gold',
        barColor: 'bg-gold shadow-[0_0_8px_rgba(255,153,0,0.4)]',
      };
    case 4:
      return {
        label: 'Strong',
        textColor: 'text-emerald-400',
        barColor: 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]',
      };
    default:
      return {
        label: 'Enter password',
        textColor: 'text-theme-muted',
        barColor: 'bg-theme-border/50',
      };
  }
});
</script>
