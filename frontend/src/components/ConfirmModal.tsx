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

interface ConfirmModalProps {
    visible: boolean;
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    onConfirm: () => void;
    onCancel: () => void;
    confirmColor?: string; // Color del botón confirmar (por defecto primary, para borrar puede ser error)
}

export const ConfirmModal: React.FC<ConfirmModalProps> = ({
    visible,
    title,
    message,
    confirmText = 'Confirmar',
    cancelText = 'Cancelar',
    onConfirm,
    onCancel,
    confirmColor = COLORS.primary,
}) => {
    return (
        <Modal
            visible={visible}
            transparent={true}
            animationType="fade"
            onRequestClose={onCancel}
        >
            <View style={styles.overlay}>
                <View style={styles.modalContainer}>
                    {/* Icono de Alerta */}
                    <View style={styles.iconContainer}>
                        <Ionicons name="alert-circle" size={80} color={confirmColor} />
                    </View>

                    {/* Título */}
                    <Text style={styles.title}>{title}</Text>

                    {/* Mensaje */}
                    <Text style={styles.message}>{message}</Text>

                    {/* Botones */}
                    <View style={styles.buttonsContainer}>
                        <TouchableOpacity
                            style={[styles.button, styles.cancelButton]}
                            onPress={onCancel}
                        >
                            <Text style={styles.cancelButtonText}>{cancelText}</Text>
                        </TouchableOpacity>

                        <TouchableOpacity
                            style={[styles.button, { backgroundColor: confirmColor }]}
                            onPress={onConfirm}
                        >
                            <Text style={styles.confirmButtonText}>{confirmText}</Text>
                        </TouchableOpacity>
                    </View>
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
        maxWidth: 400,
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
    buttonsContainer: {
        flexDirection: 'row',
        gap: SPACING.md,
        width: '100%',
    },
    button: {
        flex: 1,
        paddingVertical: SPACING.md,
        borderRadius: BORDER_RADIUS.md,
        alignItems: 'center',
        justifyContent: 'center',
    },
    cancelButton: {
        backgroundColor: 'transparent',
        borderWidth: 1,
        borderColor: COLORS.border.primary,
    },
    cancelButtonText: {
        fontSize: TYPOGRAPHY.fontSize.md,
        fontWeight: TYPOGRAPHY.fontWeight.bold,
        color: COLORS.text.primary,
    },
    confirmButtonText: {
        fontSize: TYPOGRAPHY.fontSize.md,
        fontWeight: TYPOGRAPHY.fontWeight.bold,
        color: '#FFF',
    },
});
