import React, { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Modal, Animated, TouchableOpacity, Dimensions, ImageBackground, Platform, Linking } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../constants/theme';
import { createShadow, createTextShadow } from '../utils/shadows';
import { FighterCard } from './common/FighterCard';
import { captureRef } from 'react-native-view-shot';
import * as Sharing from 'expo-sharing';
import { AdminService } from '../services/AdminService';

const { width } = Dimensions.get('window');

interface EpicFighterSuccessModalProps {
    visible: boolean;
    onClose: () => void;
    fighterData: {
        nombre: string;
        apellidos: string;
        apodo?: string;
        peso?: string;
        genero?: string;
        photoUri?: string | null;
        edad?: string;
        altura?: string;
        clubName?: string;
    };
    email: string;
    dni: string;
    isAutoLoggedIn: boolean;
    isAlreadyAuth?: boolean;
    title?: string;
    subtitle?: string;
    backgroundOffsetY?: number;
    backgroundOffsetX?: number;
    backgroundScale?: number;
    backgroundResizeMode?: 'cover' | 'contain' | 'stretch' | 'center';
    companyLogoUri?: string | null;
    stickerUri?: string | null;
    stickerOffsetY?: number;
    stickerOffsetX?: number;
    stickerScale?: number;
    stickerRotation?: number;
}

export const EpicFighterSuccessModal: React.FC<EpicFighterSuccessModalProps> = ({
    visible,
    onClose,
    fighterData,
    email,
    dni,
    isAutoLoggedIn,
    isAlreadyAuth = false,
    title = '¡YA ERES UN PELEADOR!',
    subtitle,
    backgroundOffsetY = 0,
    backgroundOffsetX = 0,
    backgroundScale = 1,
    backgroundResizeMode = 'contain',
    companyLogoUri = null,
    stickerUri = null,
    stickerOffsetY = 0,
    stickerOffsetX = 0,
    stickerScale = 1,
    stickerRotation = 0
}) => {
    const scaleAnim = useRef(new Animated.Value(0)).current;
    const opacityAnim = useRef(new Animated.Value(0)).current;
    const cardRef = useRef<View>(null);

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

    // Fetch Company Logo
    const [fetchedLogo, setFetchedLogo] = React.useState<string | null>(null);

    useEffect(() => {
        if (visible && !companyLogoUri) {
            const fetchLogo = async () => {
                try {
                    const data = await AdminService.getActiveLogos();
                    if (data.success && data.logos.card) {
                        setFetchedLogo(data.logos.card.url);
                    }
                } catch (e) {
                    console.error("Error fetching logo", e);
                }
            };
            fetchLogo();
        }
    }, [visible, companyLogoUri]);

    const handleShare = async () => {
        try {
            if (Platform.OS === 'web') {
                const message = `¡Ya soy un peleador oficial de Box TioVE! 🥊\n\nNombre: ${fighterData.nombre} "${fighterData.apodo || ''}" ${fighterData.apellidos}\nPeso: ${fighterData.peso}kg\nClub: ${fighterData.clubName || 'Independiente'}\n\n¡Nos vemos en el ring! 🔥`;
                window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
                return;
            }

            // 1. Capturar la vista como imagen
            // Para asegurar que view-shot capture los estilos transformados, a veces necesita collapsable=false (ya puesto)
            const uri = await captureRef(cardRef, {
                format: 'png',
                quality: 1,
                result: 'tmpfile'
            });

            console.log("📸 Imagen capturada:", uri);

            // 2. Verificar si se puede compartir
            if (!(await Sharing.isAvailableAsync())) {
                alert('El compartir no está disponible en este dispositivo');
                return;
            }

            // 3. Compartir la imagen
            await Sharing.shareAsync(uri, {
                mimeType: 'image/png',
                dialogTitle: 'Compartir mi Ficha de Peleador',
                UTI: 'image/png'
            });

        } catch (error) {
            console.error("Error al compartir imagen:", error);
        }
    };



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
                            <Text style={styles.title}>{title}</Text>

                            <View
                                ref={cardRef}
                                collapsable={false}
                                style={styles.cardContainer}
                            >
                                <FighterCard
                                    fighter={{
                                        nombre: fighterData.nombre,
                                        apellidos: fighterData.apellidos,
                                        apodo: fighterData.apodo,
                                        peso: fighterData.peso,
                                        genero: fighterData.genero,
                                        photoUri: fighterData.photoUri,
                                        edad: fighterData.edad,
                                        altura: fighterData.altura,
                                        clubName: fighterData.clubName
                                    }}
                                    variant="large"
                                    onShare={handleShare}
                                    backgroundOffsetY={backgroundOffsetY}
                                    backgroundOffsetX={backgroundOffsetX}
                                    backgroundScale={backgroundScale}
                                    backgroundResizeMode={backgroundResizeMode}
                                    companyLogoUri={companyLogoUri || fetchedLogo}
                                    stickerUri={stickerUri}
                                    stickerOffsetY={stickerOffsetY}
                                    stickerOffsetX={stickerOffsetX}
                                    stickerScale={stickerScale}
                                    stickerRotation={stickerRotation}
                                />
                            </View>

                            {!isAlreadyAuth && (
                                <View style={styles.credentialsContainer}>
                                    <View style={styles.credentialRow}>
                                        <Ionicons name="mail" size={18} color={COLORS.text.secondary} />
                                        <Text style={styles.credentialLabel}>Usuario:</Text>
                                        <Text style={styles.credentialValue}>{email}</Text>
                                    </View>
                                    <View style={[styles.credentialRow, { alignItems: 'flex-start' }]}>
                                        <Ionicons name="key" size={18} color={COLORS.text.secondary} style={{ marginTop: 3 }} />
                                        <Text style={[styles.credentialLabel, { marginTop: 3 }]}>Contraseña:</Text>
                                        <View>
                                            <Text style={styles.credentialValue}>{dni}</Text>
                                            <Text style={[styles.tag, { alignSelf: 'flex-start', marginTop: 4 }]}>
                                                (Temporal)
                                            </Text>
                                        </View>
                                    </View>
                                    <Text style={styles.securityHint}>
                                        * Tu DNI es tu contraseña temporal. Por seguridad, cámbiala al ingresar.
                                    </Text>
                                </View>
                            )}

                            <Text style={styles.message}>
                                {isAlreadyAuth
                                    ? "Registro completado exitosamente."
                                    : (isAutoLoggedIn
                                        ? "Entra a tu perfil para cambiar la contraseña."
                                        : "Usa estas credenciales para acceder a tu perfil.")
                                }
                            </Text>

                            <TouchableOpacity style={styles.button} onPress={onClose}>
                                <LinearGradient
                                    colors={isAutoLoggedIn || isAlreadyAuth ? ['#FFFFFF', '#E0E0E0'] : ['#FFD700', '#DAA520']}
                                    style={styles.buttonGradient}
                                >
                                    <Text style={[styles.buttonText, { color: (isAutoLoggedIn || isAlreadyAuth) ? '#CC0000' : '#000' }]}>
                                        {isAlreadyAuth
                                            ? 'ENTENDIDO'
                                            : (isAutoLoggedIn ? 'IR A MI PERFIL' : 'INICIAR SESIÓN')
                                        }
                                    </Text>
                                    <Ionicons
                                        name={isAlreadyAuth ? "checkmark-circle" : (isAutoLoggedIn ? "person" : "log-in")}
                                        size={20}
                                        color={(isAutoLoggedIn || isAlreadyAuth) ? '#CC0000' : '#000'}
                                    />
                                </LinearGradient>
                            </TouchableOpacity>
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
        maxWidth: 400,
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
        gap: SPACING.md,
    },
    title: {
        fontSize: 24,
        fontWeight: '900',
        color: '#FFD700',
        letterSpacing: 1,
        textAlign: 'center',
        marginBottom: SPACING.sm,
        ...createTextShadow('rgba(0,0,0,0.5)', 2, 2, 5),
    },
    cardContainer: {
        width: '100%',
        transform: [{ scale: 0.95 }],
    },
    credentialsContainer: {
        backgroundColor: 'rgba(255,255,255,0.05)',
        borderRadius: BORDER_RADIUS.md,
        padding: SPACING.md,
        width: '100%',
        marginTop: SPACING.xs,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.1)',
    },
    credentialRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 8,
        gap: 8,
    },
    credentialLabel: {
        color: COLORS.text.secondary,
        fontSize: 14,
        width: 80,
    },
    credentialValue: {
        color: '#FFF',
        fontSize: 14,
        fontWeight: 'bold',
        flex: 1,
    },
    tag: {
        fontSize: 10,
        color: '#FFD700',
        backgroundColor: 'rgba(255,215,0,0.1)',
        paddingHorizontal: 6,
        paddingVertical: 2,
        borderRadius: 4,
        overflow: 'hidden',
    },
    securityHint: {
        color: COLORS.text.tertiary,
        fontSize: 11,
        fontStyle: 'italic',
        marginTop: 4,
    },
    message: {
        fontSize: 13,
        color: 'rgba(255,255,255,0.7)',
        textAlign: 'center',
        fontStyle: 'italic',
        marginTop: SPACING.xs,
    },
    button: {
        width: '100%',
        marginTop: SPACING.md,
    },
    buttonGradient: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: SPACING.md,
        borderRadius: BORDER_RADIUS.full,
        gap: SPACING.sm,
    },
    buttonText: {
        fontSize: 15,
        fontWeight: '900',
        letterSpacing: 1,
    },
});
