import React, { PropsWithChildren } from 'react';
import { SafeAreaView, SafeAreaViewProps } from 'react-native-safe-area-context';
import { StyleSheet, ViewStyle } from 'react-native';

interface ScreenProps extends PropsWithChildren {
  edges?: SafeAreaViewProps['edges'];
  style?: ViewStyle;
}

export function Screen({ children, edges = ['top', 'bottom'], style }: ScreenProps) {
  return (
    <SafeAreaView edges={edges} style={[styles.container, style]}>
      {children}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});
