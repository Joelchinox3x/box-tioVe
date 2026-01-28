import React from 'react';
import { View, StyleSheet, Platform, ViewStyle, useWindowDimensions } from 'react-native';
import { COLORS } from '../../constants/theme';

interface WebLayoutContainerProps {
    children: React.ReactNode;
}

/**
 * Contenedor para simular vista móvil en navegadores de escritorio.
 * En nativo (iOS/Android) o web móvil, pasa los hijos directamente.
 * En web de escritorio, centra el contenido y limita el ancho.
 */
export const WebLayoutContainer: React.FC<WebLayoutContainerProps> = ({ children }) => {
    const { width } = useWindowDimensions();
    const isMobileWeb = width < 500; // Umbral para considerar "celular"

    // Si no es web, o si es web en móvil (pantalla pequeña),
    // renderizar directamente (passthrough) para que ocupe todo el ancho nativo.
    if (Platform.OS !== 'web' || isMobileWeb) {
        return <>{children}</>;
    }

    return (
        <View style={styles.container}>
            <View style={styles.contentContainer}>
                {children}
            </View>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#000000', // Fondo negro para los laterales
        alignItems: 'center', // Centrar horizontalmente
        justifyContent: 'center',
        minHeight: '100%', // Asegurar altura completa en web
    },
    contentContainer: {
        width: '100%',
        maxWidth: 480, // Ancho máximo de "celular grande"
        height: '100%',
        flex: 1,
        backgroundColor: COLORS.background, // Fondo de la app
        // Sombra para dar efecto de profundidad/celular
        ...Platform.select({
            web: {
                boxShadow: '0px 0px 20px rgba(255, 215, 0, 0.1)', // Sutil brillo dorado
            } as ViewStyle,
            default: {}
        }),
        overflow: 'hidden', // Asegurar que nada se salga del "celular"
    },
});
