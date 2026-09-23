import React from 'react';
import { StyleSheet, View } from 'react-native';
import Svg, { Circle, Defs, LinearGradient, Stop } from 'react-native-svg';
import { useAppTheme } from '../../hooks/useAppTheme';

const DOT_COLUMNS = 13;
const DOT_ROWS = 29;
const DOTS = Array.from({ length: DOT_COLUMNS * DOT_ROWS }, (_, index) => ({
  column: index % DOT_COLUMNS,
  row: Math.floor(index / DOT_COLUMNS),
}));

export function ThemePatternOverlay({ visible = true }: { visible?: boolean }) {
  const { colors, isDark } = useAppTheme();

  if (!visible) return null;

  return (
    <View pointerEvents="none" style={StyleSheet.absoluteFill}>
      <Svg width="100%" height="100%" viewBox="0 0 390 844" preserveAspectRatio="xMidYMid slice">
        <Defs>
          <LinearGradient id="themeDotGold" x1="0" y1="0" x2="1" y2="1">
            <Stop offset="0" stopColor={colors.patternDotGlow} stopOpacity={isDark ? 0.05 : 0.12} />
            <Stop offset="0.5" stopColor={colors.patternDot} stopOpacity={isDark ? 0.16 : 0.2} />
            <Stop offset="1" stopColor={colors.patternDotGlow} stopOpacity={isDark ? 0.06 : 0.12} />
          </LinearGradient>
        </Defs>
        {DOTS.map(({ column, row }) => (
          <Circle
            key={`${column}-${row}`}
            cx={18 + column * 30}
            cy={12 + row * 30}
            r={row > 17 ? 1.05 : 0.85}
            fill="url(#themeDotGold)"
          />
        ))}
      </Svg>
    </View>
  );
}
