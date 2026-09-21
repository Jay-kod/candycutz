const fs = require('fs');
const path = require('path');

const data = JSON.parse(
  fs.readFileSync(path.resolve(__dirname, 'extracted_icons.json'), 'utf8')
);

const iconKeys = Object.keys(data);

const tsContent = `import React from 'react';
import { StyleProp, ViewStyle } from 'react-native';
import Svg, { Path } from 'react-native-svg';

export type UIconName =
${iconKeys.map((k) => `  | '${k}'`).join('\n')};

export const UICON_PATHS: Record<UIconName, string> = {
${iconKeys
  .map(
    (k) =>
      `  ${k}: '${data[k].pathData}',`
  )
  .join('\n')}
};

export interface UIconProps {
  name: UIconName;
  size?: number;
  color?: string;
  style?: StyleProp<ViewStyle>;
}

/**
 * Official Flaticon UIcons (Regular Rounded) Component for CandyCutz
 * Renders vector SVG interface icons from @flaticon/flaticon-uicons
 */
export const UIcon: React.FC<UIconProps> = ({
  name,
  size = 24,
  color = '#FFFFFF',
  style,
}) => {
  const pathData = UICON_PATHS[name];
  if (!pathData) return null;

  return (
    <Svg
      width={size}
      height={size}
      viewBox="0 0 300 300"
      style={style}
      accessibilityRole="image"
    >
      <Path d={pathData} fill={color} fillRule="evenodd" />
    </Svg>
  );
};
`;

fs.writeFileSync(
  path.resolve(__dirname, '../src/components/common/UIcon.tsx'),
  tsContent,
  'utf8'
);
console.log('Successfully generated src/components/common/UIcon.tsx!');
