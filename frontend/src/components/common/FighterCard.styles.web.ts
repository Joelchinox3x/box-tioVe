import { StyleSheet, Dimensions } from 'react-native';
import { styles as baseStyles } from './FighterCard.base';
import { SPACING } from '../../constants/theme';

const { width } = Dimensions.get('window');

// Lógica para limitar el ancho en Desktop pero mantenerlo fluido en Mobile
const MAX_WEB_WIDTH = 360; // Reduced from 435 to match standard phone width
const isDesktop = width > MAX_WEB_WIDTH;

// Margen para web mobile
const MOBILE_MARGIN = 24;

export const cardWidth = isDesktop ? MAX_WEB_WIDTH : width - (MOBILE_MARGIN * 2);

export const styles = StyleSheet.create({
    ...baseStyles,
    container: {
        ...baseStyles.container,
        alignItems: 'center', // Centra el contenido en el contenedor padre
    },
    card: {
        ...baseStyles.card,
        // En web usamos boxShadow manual
        boxShadow: '0px 10px 15px -3px rgba(0,0,0,0.1)',
        overflow: 'hidden', // CRITICAL: Ensure zoomed images don't bleed out and cover buttons
    } as any,
    nickname: {
        ...baseStyles.nickname,
        textShadow: '2px 2px 4px rgba(0, 0, 0, 0.9)',
    } as any,
    name: {
        ...baseStyles.name,
        textShadow: '1px 1px 2px rgba(0, 0, 0, 0.8)',
    } as any,
});
