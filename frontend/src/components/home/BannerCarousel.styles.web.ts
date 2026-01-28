import { StyleSheet, Dimensions } from 'react-native';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { createShadow, createTextShadow } from '../../utils/shadows';

const { width } = Dimensions.get('window');

// Lógica para limitar el ancho en Desktop pero mantenerlo fluido en Mobile
const MAX_WEB_WIDTH = 435; // Reducido de 460 a 400 (Feedback: "mas chico")
const isDesktop = width > MAX_WEB_WIDTH;

// Feedback Web Celular: "falta crecer un pokitin" (28px era mucho, 32px era mucho).
// Regresamos a 24px (SPACING.lg) que es el estándar del TicketBanner.
const MOBILE_MARGIN = 24;
const cardWidth = isDesktop ? MAX_WEB_WIDTH : width - (MOBILE_MARGIN * 2);

export const styles = StyleSheet.create({
    container: {
        paddingVertical: SPACING.md,
        alignItems: 'center', // Centra el contenido en el contenedor padre
        width: '100%',
    },
    bannerWrapper: {
        borderRadius: BORDER_RADIUS.xl,
        width: cardWidth,
        maxWidth: MAX_WEB_WIDTH, // Asegura que nunca pase de 500px
        height: cardWidth / 2.2, // Mantiene el aspect ratio basado en el ancho real
        overflow: 'hidden',
        // En web usamos boxShadow manual si createShadow devuelve claves de Native
        boxShadow: '0px 10px 15px -3px rgba(0,0,0,0.1)',
        // Margen horizontal solo si es mobile, en desktop ya está centrado
        marginHorizontal: isDesktop ? 0 : MOBILE_MARGIN,
    },
    backgroundImage: {
        flex: 1,
        width: '100%',
        height: '100%',
    },
    backgroundImageStyle: {
        width: '100%',
        height: '100%',
        objectFit: 'cover',
    },
    gradient: {
        flex: 1,
        padding: SPACING.md,
        paddingTop: SPACING.sm,
        paddingBottom: SPACING.md,
        justifyContent: isDesktop ? 'center' : 'flex-end',
    },
    content: {
        gap: 4,
    },
    badge: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#FFD700',
        paddingHorizontal: SPACING.sm,
        paddingVertical: 4,
        borderRadius: BORDER_RADIUS.sm,
        alignSelf: 'flex-start',
        marginBottom: 4,
        gap: 4,
    },
    badgeText: {
        fontSize: 9,
        fontWeight: '900',
        color: '#000',
        letterSpacing: 0.5,
    },
    headerRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: SPACING.sm,
    },
    iconContainer: {
        transform: [{ rotate: '15deg' }]
    },
    title: {
        fontSize: 22,
        fontWeight: '900',
        color: '#FFFFFF',
        letterSpacing: 0.5,
        flexShrink: 1,
        textShadow: '2px 2px 15px rgba(0, 0, 0, 0.8)',
    } as any,
    subtitle: {
        fontSize: 12,
        color: 'rgba(255, 255, 255, 0.95)',
        lineHeight: 16,
        marginBottom: 8,
        fontWeight: '500',
        maxWidth: '85%',
        textShadow: '1px 1px 5px rgba(0, 0, 0, 0.5)',
    } as any,
    buttonWrapper: {
        alignSelf: 'center',
        marginTop: 4,
    },
    button: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: SPACING.md,
        paddingVertical: 8,
        borderRadius: BORDER_RADIUS.full,
        gap: SPACING.xs,
        boxShadow: '0px 4px 5px rgba(0,0,0,0.3)',
    },
    buttonText: {
        fontSize: 13,
        fontWeight: '900',
        color: '#000000',
        letterSpacing: 0.5,
    },
    glowEffect: {
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        borderWidth: 2,
        borderColor: 'rgba(255, 215, 0, 0.4)',
        borderRadius: BORDER_RADIUS.xl,
    },
});
