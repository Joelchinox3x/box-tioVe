import React, { useEffect } from 'react';
import { LogBox } from 'react-native';
import AppNavigator from './src/navigation/AppNavigator';
import { WebLayoutContainer } from './src/components/layout/WebLayoutContainer';

export default function App() {
  useEffect(() => {
    // Suprimir warnings de compatibilidad web/móvil
    // Estos son normales cuando se desarrolla para ambas plataformas
    LogBox.ignoreLogs([
      'props.pointerEvents is deprecated',
      '"shadow*" style props are deprecated',
      '"textShadow*" style props are deprecated',
      'Invalid style property of "outline"',
    ]);
  }, []);

  return (
    <WebLayoutContainer>
      <AppNavigator />
    </WebLayoutContainer>
  );
}
