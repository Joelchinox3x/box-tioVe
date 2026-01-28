import { StyleSheet, Platform } from 'react-native';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { createShadow, createTextShadow } from '../../utils/shadows';

export const styles = StyleSheet.create({
    container: {
        paddingHorizontal: SPACING.lg,
        paddingVertical: SPACING.lg,
    },
    bannerWrapper: {
        borderRadius: BORDER_RADIUS.xl,
        ...SHADOWS.lg,
        elevation: 12,
    },
    banner: {
        borderRadius: BORDER_RADIUS.xl,
        overflow: 'hidden',
        width: '100%',
        aspectRatio: 2.2, // Equilibrio entre panorámico (2.5) y visible (2.0)
        backgroundColor: '#000',
    },
    backgroundImage: {
        flex: 1,
        width: '100%',
        height: '100%',
    },
    backgroundImageStyle: {
        // Native properties if any
    },
    gradient: {
        flex: 1,
        padding: SPACING.md,
        paddingTop: 4, // Reducido de lg a md
        paddingBottom: 8,
        justifyContent: 'flex-end',
    },
    content: {
        gap: 4, // Reduce gap for better fit in 2:1
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
        fontSize: 22, // Adjusted font size for 2:1 ratio
        fontWeight: '900',
        color: '#FFFFFF',
        letterSpacing: 0.5,
        flexShrink: 1,
        ...createTextShadow('rgba(0, 0, 0, 0.8)', 2, 2, 15),
    },
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
        ...createShadow('#000', 0, 4, 0.3, 5, 5),
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
