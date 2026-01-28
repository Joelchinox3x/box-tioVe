import React, { useRef, useState, useEffect } from 'react';
import { View, Text, Modal, TouchableOpacity, StyleSheet, Dimensions, Image, Animated } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../../../constants/theme';

interface FighterImageUploadModalProps {
    visible: boolean;
    onClose: () => void;
    onCamera: () => void;
    onGallery: () => void;
    mode: 'profile' | 'background';
}

const { width } = Dimensions.get('window');

export const FighterImageUploadModal: React.FC<FighterImageUploadModalProps> = ({
    visible,
    onClose,
    onCamera,
    onGallery,
    mode = 'background' // Default for safety
}) => {

    // Dummy data for examples (Background Mode)
    const examples = [
        { name: 'EJEMPLO 1', bg: require('../../../../assets/banner_fighter/banner1.png') },
        { name: 'EJEMPLO 2', bg: require('../../../../assets/banner_fighter/banner2.png') },
        { name: 'EJEMPLO 3', bg: require('../../../../assets/banner_fighter/banner3.png') },
    ];

    const [currentIndex, setCurrentIndex] = useState(0);
    const fadeAnim = useRef(new Animated.Value(1)).current;

    useEffect(() => {
        if (!visible || mode === 'profile') return; // No slideshow for profile

        const interval = setInterval(() => {
            Animated.timing(fadeAnim, {
                toValue: 0.2,
                duration: 300,
                useNativeDriver: true,
            }).start(() => {
                setCurrentIndex((prev) => (prev + 1) % examples.length);
                Animated.timing(fadeAnim, {
                    toValue: 1,
                    duration: 500,
                    useNativeDriver: true,
                }).start();
            });
        }, 3000);

        return () => clearInterval(interval);
    }, [visible, mode]);

    const currentItem = examples[currentIndex];

    // Configuración condicional según el modo
    const isProfile = mode === 'profile';
    const title = isProfile ? "FOTO DE PERFIL" : "GUÍA PARA FOTO DE FONDO";

    return (
        <Modal
            visible={visible}
            transparent={true}
            animationType="fade"
            onRequestClose={onClose}
        >
            <TouchableOpacity
                style={styles.modalOverlay}
                activeOpacity={1}
                onPress={onClose}
            >
                <View style={styles.modalContent}>
                    <View style={styles.modalHeader}>
                        <Text style={styles.modalTitle}>{title}</Text>
                        <TouchableOpacity onPress={onClose}>
                            <Ionicons name="close" size={24} color={COLORS.text.secondary} />
                        </TouchableOpacity>
                    </View>

                    {/* Conditional Preview Area */}
                    <View style={styles.previewContainer}>
                        {isProfile ? (
                            // Profile View: Vertical Placeholder
                            <View style={styles.profilePlaceholder}>
                                <Ionicons name="person" size={80} color="rgba(255,255,255,0.5)" />
                                <View style={styles.faceGuideFrame} />
                            </View>
                        ) : (
                            // Background View: Horizontal Fade Slideshow
                            <Animated.View style={[styles.exampleCardWrapper, { opacity: fadeAnim }]}>
                                <View style={styles.dummyCard}>
                                    <Image source={currentItem.bg} style={styles.dummyImage} resizeMode="stretch" />
                                    <View style={styles.dummyOverlay}>
                                        <Text style={styles.dummyText}>{currentItem.name}</Text>
                                    </View>
                                </View>
                            </Animated.View>
                        )}
                    </View>

                    <View style={styles.instructionsContainer}>
                        {isProfile ? (
                            <>
                                <View style={styles.instructionRow}>
                                    <Ionicons name="phone-portrait" size={20} color={COLORS.primary} />
                                    <Text style={styles.instructionText}>La foto debe ser <Text style={styles.bold}>VERTICAL (PARADO)</Text></Text>
                                </View>
                                <View style={styles.instructionRow}>
                                    <Ionicons name="happy" size={20} color={COLORS.primary} />
                                    <Text style={styles.instructionText}>Que se vea bien tu rostro, sin gafas oscuras</Text>
                                </View>
                                <View style={styles.instructionRow}>
                                    <Ionicons name="scan" size={20} color={COLORS.primary} />
                                    <Text style={styles.instructionText}>Centrada en tu cara y hombros</Text>
                                </View>
                            </>
                        ) : (
                            <>
                                <View style={styles.instructionRow}>
                                    <Ionicons name="phone-landscape" size={20} color={COLORS.primary} />
                                    <Text style={styles.instructionText}>La foto debe ser <Text style={styles.bold}>HORIZONTAL (ECHADA)</Text></Text>
                                </View>
                                <View style={styles.instructionRow}>
                                    <Ionicons name="sunny" size={20} color={COLORS.primary} />
                                    <Text style={styles.instructionText}>Busca buena iluminación, evita sombras duras</Text>
                                </View>
                                <View style={styles.instructionRow}>
                                    <Ionicons name="crop" size={20} color={COLORS.primary} />
                                    <Text style={styles.instructionText}>Se recortará automáticamente al centro</Text>
                                </View>
                            </>
                        )}
                    </View>

                    <View style={styles.imageOptionsContainer}>
                        <TouchableOpacity style={styles.imageOptionButton} onPress={onCamera}>
                            <View style={styles.imageOptionIcon}>
                                <Ionicons name="camera" size={28} color={COLORS.primary} />
                            </View>
                            <Text style={styles.imageOptionText}>Tomar Foto</Text>
                        </TouchableOpacity>

                        <View style={styles.verticalDividerLarge} />

                        <TouchableOpacity style={styles.imageOptionButton} onPress={onGallery}>
                            <View style={styles.imageOptionIcon}>
                                <Ionicons name="images" size={28} color={COLORS.primary} />
                            </View>
                            <Text style={styles.imageOptionText}>Galería</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </TouchableOpacity>
        </Modal>
    );
};

const styles = StyleSheet.create({
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.85)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: SPACING.lg,
    },
    modalContent: {
        backgroundColor: COLORS.surface,
        borderRadius: BORDER_RADIUS.lg,
        width: '100%',
        maxWidth: 500,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.1)',
        ...SHADOWS.lg,
        overflow: 'hidden',
    },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: SPACING.lg,
        borderBottomWidth: 1,
        borderBottomColor: 'rgba(255,255,255,0.1)',
        backgroundColor: 'rgba(255,255,255,0.02)',
    },
    modalTitle: {
        fontSize: 16,
        fontWeight: '800',
        color: COLORS.primary,
        letterSpacing: 1,
    },
    previewContainer: {
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 10,
    },
    exampleCardWrapper: {
        width: '100%', // Use full width of container
        aspectRatio: 2.2,
        borderRadius: BORDER_RADIUS.lg,
        overflow: 'hidden',
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.2)',
        ...SHADOWS.md,
    },
    dummyCard: {
        width: '100%',
        height: '100%',
    },
    dummyImage: {
        width: '100%',
        height: '100%',
    },
    dummyOverlay: {
        position: 'absolute',
        bottom: 0,
        left: 0,
        right: 0,
        backgroundColor: 'rgba(0,0,0,0.6)',
        padding: 4,
        alignItems: 'center',
    },
    dummyText: {
        color: '#fff',
        fontSize: 10,
        fontWeight: 'bold',
    },
    // New Profile Styles
    profilePlaceholder: {
        width: 120,
        height: 120,
        borderRadius: 60,
        backgroundColor: 'rgba(255,255,255,0.1)',
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 2,
        borderColor: COLORS.primary,
        position: 'relative',
    },
    faceGuideFrame: {
        position: 'absolute',
        width: 80,
        height: 80,
        borderWidth: 1,
        borderColor: 'rgba(255, 215, 0, 0.3)',
        borderStyle: 'dashed',
        borderRadius: 40,
    },
    instructionsContainer: {
        padding: SPACING.lg,
        gap: SPACING.sm,
        backgroundColor: 'rgba(255,215,0,0.05)',
        borderTopWidth: 1,
        borderTopColor: 'rgba(255,255,255,0.1)',
        borderBottomWidth: 1,
        borderBottomColor: 'rgba(255,255,255,0.1)',
    },
    instructionRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: SPACING.md,
    },
    instructionText: {
        color: COLORS.text.secondary,
        fontSize: 13,
        flex: 1,
    },
    bold: {
        fontWeight: 'bold',
        color: COLORS.primary,
    },
    imageOptionsContainer: {
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        padding: SPACING.xl,
    },
    imageOptionButton: {
        alignItems: 'center',
        justifyContent: 'center',
        gap: SPACING.sm,
    },
    imageOptionIcon: {
        width: 64,
        height: 64,
        borderRadius: 32,
        backgroundColor: 'rgba(255, 215, 0, 0.1)',
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 1,
        borderColor: COLORS.primary,
        marginBottom: 8,
    },
    imageOptionText: {
        color: COLORS.text.primary,
        fontWeight: '600',
        fontSize: 14,
    },
    verticalDividerLarge: {
        width: 1,
        height: 60,
        backgroundColor: 'rgba(255,255,255,0.1)',
        marginHorizontal: SPACING.xl,
    },
});
