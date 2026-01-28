import React, { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Modal, Animated, TouchableOpacity, Dimensions, Platform, ImageBackground } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../constants/theme';
import { createTextShadow } from '../utils/shadows';
import { FighterCard } from './common/FighterCard';

const { width } = Dimensions.get('window');

interface FighterIdentityModalProps {
    visible: boolean;
    onClose: () => void;
    onEdit: () => void;
    fighter: {
        nombre: string;
        apellidos?: string;
        apodo?: string;
        peso?: string;
        genero?: string;
        photoUri?: string | null;
        clubName?: string;
        record?: string;
    };
}

export const FighterIdentityModal: React.FC<FighterIdentityModalProps> = ({
    visible,
    onClose,
    onEdit,
    fighter
}) => {
    const scaleAnim = useRef(new Animated.Value(0)).current;
    const opacityAnim = useRef(new Animated.Value(0)).current;

    useEffect(() => {
        if (visible) {
            Animated.parallel([
                Animated.spring(scaleAnim, {
                    toValue: 1,
                    friction: 6,
                    useNativeDriver: Platform.OS !== 'web',
                }),
                Animated.timing(opacityAnim, {
                    toValue: 1,
                    duration: 500,
                    useNativeDriver: Platform.OS !== 'web',
                }),
            ]).start();
        } else {
            scaleAnim.setValue(0);
            opacityAnim.setValue(0);
        }
    }, [visible]);

    if (!visible) return null;

    return (
        <Modal transparent visible={visible} animationType="none">
            <View style={styles.overlay}>
                <Animated.View
                    style={[
                        styles.content,
                        {
                            opacity: opacityAnim,
                            transform: [{ scale: scaleAnim }]
                        }
                    ]}
                >
                    <ImageBackground
                        source={require('../../assets/fighter_bg.png')}
                        style={styles.bgImage}
                        imageStyle={{ borderRadius: BORDER_RADIUS.xl, opacity: 0.4 }}
                    >
                        <LinearGradient
                            colors={['rgba(0,0,0,0.85)', 'rgba(5,5,5,0.95)']}
                            style={styles.gradient}
                        >
                            <Text style={styles.title}>¡YA ERES UN PELEADOR!</Text>
                            <Text style={styles.subtitle}>Tu registro ya está activo en Box TioVE</Text>

                            <View style={styles.cardContainer}>
                                <FighterCard
                                    fighter={fighter}
                                    variant="large"
                                />
                            </View>

                            <View style={styles.actions}>
                                <TouchableOpacity
                                    style={styles.primaryButton}
                                    onPress={() => {
                                        console.log('👆 Modal: Profile Button Touched');
                                        if (onEdit) onEdit();
                                    }}
                                >
                                    <LinearGradient
                                        colors={['#FFD700', '#DAA520']}
                                        style={styles.buttonGradient}
                                    >
                                        <Text style={styles.primaryButtonText}>IR A MI PERFIL</Text>
                                        <Ionicons name="person" size={20} color="#000" />
                                    </LinearGradient>
                                </TouchableOpacity>

                                <TouchableOpacity
                                    style={styles.secondaryButton}
                                    onPress={() => {
                                        console.log('👆 Modal: Close Button Touched');
                                        if (onClose) onClose();
                                    }}
                                >
                                    <Text style={styles.secondaryButtonText}>CERRAR</Text>
                                </TouchableOpacity>
                            </View>

                        </LinearGradient>
                    </ImageBackground>
                </Animated.View>
            </View>
        </Modal>
    );
};

const styles = StyleSheet.create({
    overlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.9)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: SPACING.lg,
    },
    content: {
        width: '100%',
        maxWidth: 550,
        borderRadius: BORDER_RADIUS.xl,
        overflow: 'hidden',
        ...SHADOWS.lg,
        borderWidth: 1,
        borderColor: 'rgba(255,215,0,0.3)',
    },
    bgImage: {
        width: '100%',
    },
    gradient: {
        padding: SPACING.xl,
        alignItems: 'center',
        gap: SPACING.lg,
    },
    title: {
        fontSize: 24,
        fontWeight: '900',
        color: '#FFD700',
        letterSpacing: 1,
        textAlign: 'center',
        ...createTextShadow('rgba(0,0,0,0.5)', 2, 2, 5),
    },
    subtitle: {
        fontSize: 14,
        color: 'rgba(255,255,255,0.7)',
        textAlign: 'center',
        marginTop: -SPACING.md,
    },
    cardContainer: {
        width: '100%',
        // Removed scale to keep it original size
    },
    actions: {
        width: '100%',
        gap: SPACING.sm,
    },
    buttonGradient: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: SPACING.md,
        borderRadius: BORDER_RADIUS.full,
        gap: SPACING.sm,
        width: '100%',
    },
    primaryButton: {
        width: '100%',
        ...SHADOWS.md,
    },
    primaryButtonText: {
        fontSize: 15,
        fontWeight: '900',
        letterSpacing: 1,
        color: '#000',
    },
    secondaryButton: {
        width: '100%',
        paddingVertical: SPACING.md,
        alignItems: 'center',
    },
    secondaryButtonText: {
        fontSize: 14,
        fontWeight: '700',
        color: COLORS.text.secondary,
        textDecorationLine: 'underline',
    },
});
