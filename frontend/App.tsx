import React, { useEffect } from 'react';
import { LogBox, Platform } from 'react-native';
import AppNavigator from './src/navigation/AppNavigator';
import { WebLayoutContainer } from './src/components/layout/WebLayoutContainer';

export default function App() {
  useEffect(() => {
    // Inject Custom Scrollbar for Web
    if (Platform.OS === 'web') {
      const style = document.createElement('style');
      style.textContent = `
        ::-webkit-scrollbar {
          height: 8px;
          width: 8px;
        }
        ::-webkit-scrollbar-track {
          background: #1a1a1a;
        }
        ::-webkit-scrollbar-thumb {
          background: #D4AF37;
          border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
          background: #AA8C2C;
        }
      `;
      document.head.appendChild(style);
    }

    // Suprimir warnings de compatibilidad web/móvil
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
