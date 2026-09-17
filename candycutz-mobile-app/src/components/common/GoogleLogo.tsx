import React from 'react';
import Svg, { Path } from 'react-native-svg';

interface GoogleLogoProps {
  size?: number;
}

export function GoogleLogo({ size = 20 }: GoogleLogoProps) {
  return (
    <Svg width={size} height={size} viewBox="0 0 24 24" accessibilityLabel="Google">
      <Path fill="#4285F4" d="M21.35 12.27c0-.79-.07-1.55-.2-2.27H12v4.3h5.24a4.48 4.48 0 0 1-1.94 2.94v2.45h3.14c1.84-1.69 2.91-4.18 2.91-7.42Z" />
      <Path fill="#34A853" d="M12 21.8c2.63 0 4.84-.87 6.45-2.36l-3.14-2.45c-.87.58-1.98.92-3.31.92-2.54 0-4.7-1.72-5.47-4.03H3.29v2.53A9.74 9.74 0 0 0 12 21.8Z" />
      <Path fill="#FBBC05" d="M6.53 13.88A5.86 5.86 0 0 1 6.22 12c0-.65.11-1.28.31-1.88V7.59H3.29A9.78 9.78 0 0 0 2.25 12c0 1.58.38 3.08 1.04 4.41l3.24-2.53Z" />
      <Path fill="#EA4335" d="M12 6.09c1.43 0 2.71.49 3.72 1.46l2.79-2.79C16.84 3.17 14.63 2.2 12 2.2a9.74 9.74 0 0 0-8.71 5.39l3.24 2.53C7.3 7.81 9.46 6.09 12 6.09Z" />
    </Svg>
  );
}
