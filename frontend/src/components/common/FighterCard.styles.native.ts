import { StyleSheet, Dimensions } from 'react-native';
import { styles as baseStyles } from './FighterCard.base';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { createShadow } from '../../utils/shadows';

const { width } = Dimensions.get('window');

export const cardWidth = width - (SPACING.lg * 2);

export const styles = StyleSheet.create({
    ...baseStyles,
    container: {
        ...baseStyles.container,
        marginVertical: 15, // Override base for Native only
    },
    card: {
        ...baseStyles.card,
        // Native shadows
        ...SHADOWS.lg,
        elevation: 10,
    },
    overlay: {
        ...baseStyles.overlay,
        paddingTop: SPACING.xs, // Reduced top padding (closer to edge)
    },
    nickname: {
        ...baseStyles.nickname,
        textShadowColor: 'rgba(0, 0, 0, 0.9)',
        textShadowOffset: { width: 2, height: 2 },
        textShadowRadius: 4,
    },
    name: {
        ...baseStyles.name,
        textShadowColor: 'rgba(0, 0, 0, 0.8)',
        textShadowOffset: { width: 1, height: 1 },
        textShadowRadius: 2,
    }
});
