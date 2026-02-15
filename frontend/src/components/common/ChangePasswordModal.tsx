import React, { useState } from 'react';
import {
    Modal,
    View,
    Text,
    TextInput,
    TouchableOpacity,
    StyleSheet,
    ActivityIndicator,
    Alert,
    KeyboardAvoidingView,
    Platform
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, BORDER_RADIUS } from '../../constants/theme';
import { LinearGradient } from 'expo-linear-gradient';

interface ChangePasswordModalProps {
    visible: boolean;
    userId: number;
    onClose: () => void;
    onSuccess: () => void;
}

export const ChangePasswordModal = ({ visible, userId, onClose, onSuccess }: ChangePasswordModalProps) => {
    const [newPassword, setNewPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);

    const handleUpdate = async () => {
        if (newPassword.length < 6) {
            Alert.alert('Error', 'La contraseña debe tener al menos 6 caracteres');
            return;
        }

        if (newPassword !== confirmPassword) {
            Alert.alert('Error', 'Las contraseñas no coinciden');
            return;
        }

        setLoading(true);
        try {
            // Using FETCH directly to match existing service style
            const response = await fetch(`${process.env.EXPO_PUBLIC_API_URL}/usuarios/update-password`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    usuario_id: userId,
                    new_password: newPassword
                })
            });

            const result = await response.json();

            if (result.success) {
                Alert.alert('Éxito', 'Contraseña actualizada correctamente');
                onSuccess();
            } else {
                Alert.alert('Error', result.message || 'No se pudo actualizar la contraseña');
            }
        } catch (error) {
            console.error('Error updating password:', error);
            Alert.alert('Error', 'Ocurrió un error al conectar con el servidor');
        } finally {
            setLoading(false);
        }
    };

    return (
        <Modal
            visible={visible}
            transparent
            animationType="fade"
        >
            <KeyboardAvoidingView
                behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
                style={styles.overlay}
            >
                <View style={styles.container}>
                    <LinearGradient
                        colors={['#1a1a1a', '#0a0a0a']}
                        style={styles.content}
                    >
                        {/* Header */}
                        <View style={styles.header}>
                            <View style={styles.iconContainer}>
                                <Ionicons name="shield-checkmark" size={32} color="#FFD700" />
                            </View>
                            <Text style={styles.title}>Seguridad de tu Cuenta</Text>
                            <Text style={styles.subtitle}>
                                Has ingresado con una contraseña temporal. Por tu seguridad, te recomendamos cambiarla ahora.
                            </Text>
                        </View>

                        {/* Form */}
                        <View style={styles.form}>
                            <View style={styles.inputWrapper}>
                                <Ionicons name="lock-closed" size={20} color={COLORS.text.secondary} style={styles.inputIcon} />
                                <TextInput
                                    style={styles.input}
                                    placeholder="Nueva Contraseña"
                                    placeholderTextColor="rgba(255,255,255,0.4)"
                                    secureTextEntry={!showPassword}
                                    value={newPassword}
                                    onChangeText={setNewPassword}
                                />
                                <TouchableOpacity
                                    onPress={() => setShowPassword(!showPassword)}
                                    style={styles.eyeIcon}
                                >
                                    <Ionicons
                                        name={showPassword ? "eye-off" : "eye"}
                                        size={20}
                                        color={COLORS.text.secondary}
                                    />
                                </TouchableOpacity>
                            </View>

                            <View style={styles.inputWrapper}>
                                <Ionicons name="checkmark-circle" size={20} color={COLORS.text.secondary} style={styles.inputIcon} />
                                <TextInput
                                    style={styles.input}
                                    placeholder="Confirmar Contraseña"
                                    placeholderTextColor="rgba(255,255,255,0.4)"
                                    secureTextEntry={!showPassword}
                                    value={confirmPassword}
                                    onChangeText={setConfirmPassword}
                                />
                            </View>

                            <TouchableOpacity
                                style={[styles.mainButton, loading && styles.buttonDisabled]}
                                onPress={handleUpdate}
                                disabled={loading}
                            >
                                {loading ? (
                                    <ActivityIndicator color="#000" />
                                ) : (
                                    <>
                                        <Text style={styles.mainButtonText}>GUARDAR CAMBIOS</Text>
                                        <Ionicons name="arrow-forward" size={18} color="#000" />
                                    </>
                                )}
                            </TouchableOpacity>

                            <TouchableOpacity
                                style={styles.skipButton}
                                onPress={onClose}
                                disabled={loading}
                            >
                                <Text style={styles.skipButtonText}>Lo haré más tarde</Text>
                            </TouchableOpacity>
                        </View>
                    </LinearGradient>
                </View>
            </KeyboardAvoidingView>
        </Modal>
    );
};

const styles = StyleSheet.create({
    overlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.85)',
        justifyContent: 'center',
        padding: SPACING.lg,
    },
    container: {
        width: '100%',
        maxWidth: 400,
        alignSelf: 'center',
    },
    content: {
        borderRadius: BORDER_RADIUS.xl,
        padding: SPACING.xl,
        borderWidth: 1,
        borderColor: 'rgba(255,215,0,0.2)',
        overflow: 'hidden',
    },
    header: {
        alignItems: 'center',
        marginBottom: SPACING.xl,
    },
    iconContainer: {
        width: 64,
        height: 64,
        borderRadius: 32,
        backgroundColor: 'rgba(255,215,0,0.1)',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: SPACING.md,
    },
    title: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#FFD700',
        textAlign: 'center',
        marginBottom: SPACING.xs,
    },
    subtitle: {
        fontSize: 14,
        color: COLORS.text.secondary,
        textAlign: 'center',
        lineHeight: 20,
    },
    form: {
        gap: SPACING.md,
    },
    inputWrapper: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(255,255,255,0.05)',
        borderRadius: BORDER_RADIUS.md,
        paddingHorizontal: SPACING.md,
        height: 56,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.1)',
    },
    inputIcon: {
        marginRight: SPACING.sm,
    },
    input: {
        flex: 1,
        color: '#FFF',
        fontSize: 16,
    },
    eyeIcon: {
        padding: 5,
    },
    mainButton: {
        backgroundColor: '#FFD700',
        height: 56,
        borderRadius: BORDER_RADIUS.md,
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        marginTop: SPACING.sm,
        gap: SPACING.xs,
    },
    buttonDisabled: {
        opacity: 0.7,
    },
    mainButtonText: {
        color: '#000',
        fontSize: 16,
        fontWeight: '900',
        letterSpacing: 1,
    },
    skipButton: {
        height: 48,
        justifyContent: 'center',
        alignItems: 'center',
    },
    skipButtonText: {
        color: 'rgba(255,255,255,0.5)',
        fontSize: 14,
        textDecorationLine: 'underline',
    }
});
