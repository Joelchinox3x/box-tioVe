import { StyleSheet } from 'react-native';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { createShadow, createTextShadow } from '../../utils/shadows';

export const styles = StyleSheet.create({
    container: {
        paddingHorizontal: SPACING.lg,
        paddingVertical: SPACING.lg,
    },
    bannerWrapper: {
        borderRadius: BORDER_RADIUS.xl,
        // Web box shadows handled by createShadow usually return boxShadow used in style prop, 
        // but here we are using ...spread which might not work if SHADOWS.lg returns platform specific keys.
        // For web styles, we can be more explicit.
        boxShadow: '0px 10px 15px -3px rgba(0,0,0,0.1)',
    },
    banner: {
        borderRadius: BORDER_RADIUS.xl,
        overflow: 'hidden',
        width: '100%',
        // Web handles aspect ratio well, but if we want more control or fixed height we can change it here.
        aspectRatio: 2.2, // Equilibrio entre panorámico (2.5) y visible (2.0)
        backgroundColor: '#000',
    },
    backgroundImage: {
        flex: 1,
        width: '100%',
        height: '100%',
    },
    backgroundImageStyle: {
        width: '100%',
        height: '100%',
        objectFit: 'cover', // Standard CSS property for web
    },
    gradient: {
        flex: 1,
        padding: SPACING.md,
        paddingTop: 2, // Mínimo espacio posible arriba
        paddingBottom: 15,
        justifyContent: 'flex-end',
    },
    content: {
        gap: 2, // Reducir espacio entre elementos para que quepan bien
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
    title: {
        fontSize: 22,
        fontWeight: '900',
        color: '#FFFFFF',
        letterSpacing: 0.5,
        flexShrink: 1,
        textShadow: '2px 2px 15px rgba(0, 0, 0, 0.8)', // Web standard CSS text-shadow
    } as any, // Cast to any to avoid TS error with strict React Native types
    iconContainer: {
        transform: [{ rotate: '15deg' }]
    },
    subtitle: {
        fontSize: 12,
        color: 'rgba(255, 255, 255, 0.95)',
        lineHeight: 16,
        marginBottom: 8,
        fontWeight: '500',
        maxWidth: '85%',
    },
    buttonWrapper: {
        alignSelf: 'center', // Centrado horizontal
        marginTop: 4,
    },
    button: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: SPACING.md,
        paddingVertical: 8,
        borderRadius: BORDER_RADIUS.full,
        gap: SPACING.xs,
        // Web box shadow
        boxShadow: '0px 4px 5px rgba(0,0,0,0.3)',
    },
    buttonText: {
        fontSize: 13, // Un "pokitin" más grande (era 11)
        flexShrink: 1,
        fontWeight: '900',
        color: '#000000', // Negro para contrastar con el dorado
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
