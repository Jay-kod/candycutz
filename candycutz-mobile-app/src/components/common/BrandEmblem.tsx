import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import Svg, { Path, Defs, LinearGradient, Stop } from 'react-native-svg';
import { COLORS, FONTS } from '../../constants/theme';

interface BrandEmblemProps {
  size?: number;
  showText?: boolean;
  brandName?: string;
  subtitle?: string;
}

export function BrandEmblem({
  size = 110,
  showText = true,
  brandName = 'CandyCutz',
  subtitle = 'TOP RATED SALONS FOR YOU',
}: BrandEmblemProps) {
  // Proportional scaling based on viewBox 0 0 120 100
  const width = size;
  const height = (size * 100) / 120;

  return (
    <View style={styles.container}>
      <Svg width={width} height={height} viewBox="0 0 120 100" accessibilityLabel="Brand Logo">
        <Defs>
          <LinearGradient id="goldGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <Stop offset="0%" stopColor="#FDE68A" />
            <Stop offset="45%" stopColor="#E5BA73" />
            <Stop offset="80%" stopColor="#D4AF37" />
            <Stop offset="100%" stopColor="#B38926" />
          </LinearGradient>
          <LinearGradient id="goldAccent" x1="0%" y1="100%" x2="100%" y2="0%">
            <Stop offset="0%" stopColor="#E5BA73" />
            <Stop offset="100%" stopColor="#FFF2B2" />
          </LinearGradient>
        </Defs>

        {/* Fedora Hat Crown */}
        <Path
          d="M 46 16 C 50 11, 68 11, 72 16 C 75 19, 78 27, 78 30 C 73 28, 47 28, 42 30 C 42 27, 43 19, 46 16 Z"
          fill="url(#goldGradient)"
        />
        {/* Hat Crease Indentation */}
        <Path
          d="M 52 16 C 57 19, 63 19, 67 16 C 65 14, 55 14, 52 16 Z"
          fill="#1C1810"
          opacity={0.3}
        />
        {/* Fedora Hat Ribbon / Band */}
        <Path
          d="M 40 31 C 52 29, 68 29, 80 31 C 79 33, 41 33, 40 31 Z"
          fill="url(#goldAccent)"
        />
        {/* Fedora Hat Brim */}
        <Path
          d="M 33 34 C 42 32, 78 32, 87 34 C 91 35.5, 78 37.5, 60 37.5 C 42 37.5, 29 35.5, 33 34 Z"
          fill="url(#goldGradient)"
        />

        {/* Stylized Silhouette Body / Fish Form underneath the Fedora */}
        {/* Main Body Curve */}
        <Path
          d="M 33 46 C 40 38, 76 38, 86 48 C 91 53, 90 59, 84 64 C 77 69, 56 68, 46 66 C 36 64, 28 53, 33 46 Z"
          fill="url(#goldGradient)"
        />
        {/* Lower Tail / Fin Flare */}
        <Path
          d="M 83 51 C 88 47, 93 45, 96 46 C 98 48, 93 54, 87 58 C 92 61, 95 65, 93 68 C 90 69, 86 64, 83 60 Z"
          fill="url(#goldGradient)"
        />
        {/* Underbelly detail cut */}
        <Path
          d="M 62 65 C 67 68, 70 73, 70 76 C 67 73, 63 70, 60 69 Z"
          fill="url(#goldGradient)"
        />
      </Svg>

      {showText && (
        <View style={styles.textContainer}>
          <Text style={styles.brandTitle}>{brandName}</Text>
          {subtitle ? <Text style={styles.brandSubtitle}>{subtitle}</Text> : null}
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  textContainer: {
    alignItems: 'center',
    marginTop: 18,
  },
  brandTitle: {
    color: '#E5BA73',
    fontSize: 34,
    fontWeight: '300',
    letterSpacing: 2,
    fontFamily: FONTS.regular,
  },
  brandSubtitle: {
    color: 'rgba(229, 186, 115, 0.75)',
    fontSize: 10,
    fontWeight: '600',
    letterSpacing: 3.5,
    marginTop: 8,
    textTransform: 'uppercase',
  },
});
