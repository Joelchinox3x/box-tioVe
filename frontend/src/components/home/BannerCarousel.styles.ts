import { StyleSheet, Dimensions } from 'react-native';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { createShadow, createTextShadow } from '../../utils/shadows';

const { width } = Dimensions.get('window');

export const styles = StyleSheet.create({
    container: {
        paddingVertical: SPACING.md,
    },
    bannerWrapper: {
        borderRadius: BORDER_RADIUS.xl,
        width: width - (SPACING.lg * 2),
        marginHorizontal: SPACING.lg,
        height: (width - (SPACING.lg * 2)) / 2.2,
        overflow: 'hidden',
        ...SHADOWS.lg,
        elevation: 10,
    },
    backgroundImage: {
        flex: 1,
        width: '100%',
        height: '100%',
    },
    backgroundImageStyle: {
        // Native properties
    },
    gradient: {
        flex: 1,
        padding: SPACING.md,
        paddingTop: SPACING.sm,
        paddingBottom: SPACING.md,
        justifyContent: 'flex-end',
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
        ...createTextShadow('rgba(0, 0, 0, 0.8)', 2, 2, 15),
    },
    subtitle: {
        fontSize: 12,
        color: 'rgba(255, 255, 255, 0.95)',
        lineHeight: 16,
        marginBottom: 8,
        fontWeight: '500',
        ...createTextShadow('rgba(0, 0, 0, 0.5)', 1, 1, 5),
    },
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
        ...createShadow('#000', 0, 4, 0.3, 5, 5),
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
