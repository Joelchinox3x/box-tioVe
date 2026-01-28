import React from 'react';
import {
    Modal,
    View,
    Text,
    StyleSheet,
    TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../constants/theme';
import { createShadow } from '../utils/shadows';

interface ErrorModalProps {
    visible: boolean;
    title: string;
    message: string;
    buttonText?: string;
    onClose: () => void;
}

export const ErrorModal: React.FC<ErrorModalProps> = ({
    visible,
    title,
    message,
    buttonText = 'Entendido',
    onClose,
}) => {
    return (
        <Modal
            visible={visible}
            transparent={true}
            animationType="fade"
            onRequestClose={onClose}
        >
            <View style={styles.overlay}>
                <View style={styles.modalContainer}>
                    {/* Icono de error */}
                    <View style={styles.iconContainer}>
                        <Ionicons name="alert-circle" size={80} color={COLORS.error} />
                    </View>

                    {/* Título */}
                    <Text style={styles.title}>{title}</Text>

                    {/* Mensaje */}
                    <Text style={styles.message}>{message}</Text>

                    {/* Botón */}
                    <TouchableOpacity style={styles.button} onPress={onClose}>
                        <Text style={styles.buttonText}>{buttonText}</Text>
                    </TouchableOpacity>
                </View>
            </View>
        </Modal>
    );
};

const styles = StyleSheet.create({
    overlay: {
        flex: 1,
        backgroundColor: 'rgba(0, 0, 0, 0.7)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: SPACING.xl,
    },
    modalContainer: {
        backgroundColor: COLORS.surface,
        borderRadius: BORDER_RADIUS.lg,
        padding: SPACING.xl,
        width: '100%',
        maxWidth: 500,
        alignItems: 'center',
        ...createShadow('#000', 0, 4, 0.3, 8, 8),
    },
    iconContainer: {
        marginBottom: SPACING.lg,
    },
    title: {
        fontSize: TYPOGRAPHY.fontSize.xxl,
        fontWeight: TYPOGRAPHY.fontWeight.bold,
        color: COLORS.text.primary,
        textAlign: 'center',
        marginBottom: SPACING.md,
    },
    message: {
        fontSize: TYPOGRAPHY.fontSize.md,
        color: COLORS.text.secondary,
        textAlign: 'center',
        lineHeight: 24,
        marginBottom: SPACING.xl,
    },
    button: {
        backgroundColor: COLORS.error,
        paddingVertical: SPACING.md,
        paddingHorizontal: SPACING.xxl,
        borderRadius: BORDER_RADIUS.md,
        width: '100%',
        alignItems: 'center',
    },
    buttonText: {
        fontSize: TYPOGRAPHY.fontSize.lg,
        fontWeight: TYPOGRAPHY.fontWeight.bold,
        color: COLORS.text.inverse,
        letterSpacing: 0.5,
    },
});
