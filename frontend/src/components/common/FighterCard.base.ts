import { StyleSheet, Dimensions } from 'react-native';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { createTextShadow } from '../../utils/shadows';

const { width } = Dimensions.get('window');
export const cardWidth = width - (SPACING.lg * 2); // Dynamic width matching carousel

export const styles = StyleSheet.create({
    container: {
        width: '100%',
        marginVertical: SPACING.xs,
        position: 'relative', // Ensure absolute children are relative to this
    },
    card: {
        width: '100%',
        borderRadius: BORDER_RADIUS.lg,
        overflow: 'hidden',
        ...SHADOWS.md,
        backgroundColor: 'transparent', // Ensure container is transparent so layers show through
        borderWidth: 2,
        borderColor: 'rgba(255, 215, 0, 0.25)',
    },
    cardBg: {
        flex: 1,
        width: '100%',
        height: '100%',
    },
    cardImage: {
        opacity: 0.9,
    },
    companyLogo: {
        position: 'absolute',
        bottom: 12,
        alignSelf: 'center',
        width: '50%',
        height: 50,
        zIndex: 50,
    },
    overlay: {
        flex: 1,
        padding: SPACING.md,
        justifyContent: 'space-between',
    },
    topInfo: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
    },
    badge: {
        backgroundColor: '#FFD700',
        paddingHorizontal: SPACING.sm,
        paddingVertical: 4,
        borderRadius: 4,
        ...SHADOWS.sm,
    },
    badgeText: {
        fontSize: 10,
        fontWeight: '900',
        color: '#000',
        letterSpacing: 0.5,
    },
    recordBadge: {
        backgroundColor: 'rgba(10,10,10,0.25)',
        paddingHorizontal: 6,
        paddingVertical: 3,
        borderRadius: 6,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.18)',
        alignItems: 'center',
    },
    recordBadgeBottomRight: {
        position: 'absolute',
        right: SPACING.sm,
        bottom: SPACING.sm,
    },
    recordLabel: {
        fontSize: 9,
        color: '#FFD700',
        fontWeight: '900',
        letterSpacing: 0.6,
    },
    recordValue: {
        fontSize: 14,
        color: '#FFF',
        fontWeight: '800',
    },
    recordRow: {
        flexDirection: 'row',
        gap: 4,
    },
    recordNumber: {
        fontSize: 14,
        fontWeight: '900',
        letterSpacing: 0.3,
    },
    recordNumberWin: {
        color: '#27AE60',
    },
    recordNumberLoss: {
        color: '#E74C3C',
    },
    recordNumberDraw: {
        color: '#F39C12',
    },
    recordChip: {
        paddingHorizontal: 4,
        paddingVertical: 1,
        borderRadius: 5,
        minWidth: 26,
        alignItems: 'center',
        justifyContent: 'center',
    },
    recordChipWin: {
        backgroundColor: 'rgba(39, 174, 96, 0.9)',
    },
    recordChipLoss: {
        backgroundColor: 'rgba(231, 76, 60, 0.9)',
    },
    recordChipDraw: {
        backgroundColor: 'rgba(243, 156, 18, 0.9)',
    },
    recordChipText: {
        color: '#fff',
        fontSize: 10,
        fontWeight: '800',
    },
    bottomInfo: {
        gap: 4,
    },
    nickname: {
        fontWeight: '900',
        color: '#FFF',
        textTransform: 'uppercase',
        fontStyle: 'italic',
        letterSpacing: 1,
    },
    name: {
        color: 'rgba(255,255,255,0.95)',
        fontWeight: '600',
        marginBottom: 2,
    },
    clubRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
        marginBottom: 6,
    },
    clubText: {
        color: '#FFD700',
        fontSize: 12,
        fontWeight: '600',
    },
    statsRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: 4,
        paddingTop: 8,
        borderTopWidth: 1,
        borderTopColor: 'rgba(255,255,255,0.1)',
    },
    stat: {
        paddingRight: SPACING.md,
        marginRight: SPACING.md,
    },
    statBorder: {
        borderRightWidth: 1,
        borderColor: 'rgba(255,255,255,0.2)'
    },
    statLabel: {
        fontSize: 9,
        color: '#FFD700',
        fontWeight: '800',
        letterSpacing: 0.5,
        marginBottom: 1,
    },
    statValue: {
        fontSize: 14,
        color: '#FFF',
        fontWeight: '700',
    },
    hint: {
        fontSize: 10,
        color: COLORS.text.tertiary,
        marginTop: 8,
        letterSpacing: 2,
        fontWeight: '800',
        textAlign: 'center',
        width: '100%',
    },
    uploadButton: {
        position: 'absolute',
        bottom: 35,
        right: 15,
        backgroundColor: '#FFD700',
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 12,
        paddingVertical: 6,
        borderRadius: 20,
        gap: 4,
        zIndex: 10,
        ...SHADOWS.md,
    },
    uploadButtonText: {
        fontSize: 10,
        fontWeight: '900',
        color: '#000',
    },
    shareButton: {
        position: 'absolute',
        top: 10, // Same position as uploadButton to replace it contextually
        right: 10,
        backgroundColor: '#25D366', // WhatsApp Green
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 12,
        paddingVertical: 6,
        borderRadius: 20,
        gap: 4,
        zIndex: 10,
        ...SHADOWS.md,
    },
    shareButtonText: {
        fontSize: 10,
        fontWeight: '900',
        color: '#fff',
    }
});
