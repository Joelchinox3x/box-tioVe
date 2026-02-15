import React, { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Modal, Animated, TouchableOpacity, Dimensions, Platform, ImageBackground, Image, Alert } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../constants/theme';
import { createTextShadow } from '../utils/shadows';
import { AdminService } from '../services/AdminService';
import { FighterCard } from './common/FighterCard';
import * as Sharing from 'expo-sharing';
import * as FileSystem from 'expo-file-system/legacy';
import { MotivationalQuote } from './common/MotivationalQuote';

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
        bakedUrl?: string | null;
        compositionJson?: string | null;
        edad?: number;
        altura?: number;
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
    const [companyLogoUri, setCompanyLogoUri] = React.useState<string | null>(null);
    const [imageError, setImageError] = React.useState(false);

    const parsedComposition = React.useMemo(() => {
        if (!fighter.compositionJson) return null;
        try {
            return JSON.parse(fighter.compositionJson);
        } catch {
            return null;
        }
    }, [fighter.compositionJson]);

    const normalizeUrl = (url?: string | null) => {
        if (!url) return null;
        return url.startsWith('http') ? url : `${url}`;
    };

    const compositionAssets = React.useMemo(() => {
        if (!parsedComposition) return null;
        const backgroundUri = normalizeUrl(parsedComposition?.background?.url);
        const borderUri = normalizeUrl(parsedComposition?.border?.url);
        const compLogo = normalizeUrl(parsedComposition?.companyLogo?.url);
        const layers = Array.isArray(parsedComposition?.layers)
            ? parsedComposition.layers.map((layer: any) => ({
                ...layer,
                uri: normalizeUrl(layer.uri) || layer.uri,
            }))
            : [];
        const stickers = Array.isArray(parsedComposition?.stickers) ? parsedComposition.stickers : [];
        const selectedStickers = stickers.map((s: any) => normalizeUrl(s.url) || s.url).filter(Boolean);
        const stickerTransforms = stickers.reduce((acc: any, s: any) => {
            const key = normalizeUrl(s.url) || s.url;
            if (key) acc[key] = s.transform || { x: 0, y: 0, scale: 1, rotation: 0, flipX: false };
            return acc;
        }, {});

        return {
            backgroundUri,
            borderUri,
            companyLogoUri: compLogo,
            fighterLayers: layers,
            selectedStickers,
            stickerTransforms
        };
    }, [parsedComposition]);

    useEffect(() => {
        if (visible) {
            setImageError(false); // Reset error state on open
            console.log("🔍 [Modal] Props actualizadas. BakedUrl:", fighter.bakedUrl);

            // Fetch branding
            const fetchBranding = async () => {
                try {
                    const data = await AdminService.getActiveLogos();
                    if (data.success && data.logos.card) {
                        setCompanyLogoUri(data.logos.card.url);
                    }
                } catch (e) {
                    console.log('Error fetching branding', e);
                }
            };
            fetchBranding();

            Animated.parallel([
                Animated.spring(scaleAnim, {
                    toValue: 1,
                    friction: 8,
                    tension: 40,
                    useNativeDriver: Platform.OS !== 'web',
                }),
                Animated.timing(opacityAnim, {
                    toValue: 1,
                    duration: 400,
                    useNativeDriver: Platform.OS !== 'web',
                }),
            ]).start();
        } else {
            scaleAnim.setValue(0);
            opacityAnim.setValue(0);
        }
    }, [visible, fighter.bakedUrl]);

    const handleShare = async () => {
        try {
            if (Platform.OS === 'web') {
                const toHdUrl = (url: string) => {
                    const clean = url.split('?')[0];
                    if (clean.endsWith('.png')) {
                        return url.replace(/\.png(\?.*)?$/, '_HD.png$1');
                    }
                    return url;
                };
                const hdUrl = fighter.bakedUrl ? toHdUrl(fighter.bakedUrl) : '';
                const fullName = `${fighter.nombre || ''} ${fighter.apellidos || ''}`.trim();
                const message = `¡Ya soy un peleador oficial de Box TioVE! 🥊\n\nNombre: ${fullName}\nApodo: ${fighter.apodo || ''}\n\nMi tarjeta HD: ${hdUrl || 'Pendiente'}\n\n¡Nos vemos en el ring! 🔥`;
                if (typeof window !== 'undefined') {
                    window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
                } else {
                    Alert.alert('Compartir', 'La función de compartir no está disponible en web.');
                }
                return;
            }

            let uriToShare = '';

            if (fighter.bakedUrl) {
                const toHdUrl = (url: string) => {
                    const clean = url.split('?')[0];
                    if (clean.endsWith('.png')) {
                        return url.replace(/\.png(\?.*)?$/, '_HD.png$1');
                    }
                    return url;
                };

                console.log("📥 Descargando imagen quemada para compartir...");
                const filename = `fighter_card_${Date.now()}.png`;
                const downloadPath = `${FileSystem.cacheDirectory}${filename}`;

                const hdUrl = toHdUrl(fighter.bakedUrl);
                try {
                    const downloadResult = await FileSystem.downloadAsync(
                        hdUrl,
                        downloadPath
                    );
                    uriToShare = downloadResult.uri;
                } catch (e) {
                    const downloadResult = await FileSystem.downloadAsync(
                        fighter.bakedUrl,
                        downloadPath
                    );
                    uriToShare = downloadResult.uri;
                }
            } else {
                Alert.alert('Aviso', 'Tu tarjeta aún se está procesando. Por favor, intenta compartirla en unos momentos desde tu perfil.');
                return;
            }

            if (await Sharing.isAvailableAsync()) {
                await Sharing.shareAsync(uriToShare);
            } else {
                Alert.alert('Error', 'Compartir no está disponible en este dispositivo');
            }
        } catch (error) {
            console.error('Error sharing card:', error);
            Alert.alert('Error', 'No se pudo generar la imagen para compartir');
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
                        imageStyle={{ borderRadius: BORDER_RADIUS.xl, opacity: 0.2 }}
                    >
                        <LinearGradient
                            colors={['rgba(15,15,15,0.95)', 'rgba(5,5,5,0.98)']}
                            style={styles.gradient}
                        >
                            <View style={styles.headerIndicator} />

                            <View style={styles.titleContainer}>
                                <Text style={styles.title}>¡YA ERES UN PELEADOR!</Text>
                                <Text style={styles.subtitle}>Tu registro ya está activo en Box TioVE</Text>
                            </View>

                            <View style={styles.cardContainer}>
                                {fighter.bakedUrl && !imageError ? (
                                    <View style={styles.bakedCardWrapper}>
                                        <ImageBackground
                                            source={{ uri: fighter.bakedUrl }}
                                            style={styles.bakedImage}
                                            imageStyle={{ borderRadius: 12 }}
                                            resizeMode="contain"
                                            onLoadStart={() => console.log("⏳ [Image] Iniciando carga:", fighter.bakedUrl)}
                                            onLoad={() => console.log("✅ [Image] Carga completada:", fighter.bakedUrl)}
                                            onError={(e) => {
                                                console.log("❌ [Image] Error cargando (" + fighter.bakedUrl + "):", e.nativeEvent.error);
                                                setImageError(true);
                                            }}
                                        />
                                        <TouchableOpacity
                                            style={styles.shareOverlay}
                                            onPress={handleShare}
                                        >
                                            <Ionicons name="share-social" size={24} color="#FFD700" />
                                        </TouchableOpacity>
                                    </View>
                                ) : (
                                    <View style={styles.cardWrapper}>
                                        <FighterCard
                                            fighter={{
                                                nombre: fighter.nombre,
                                                apellidos: fighter.apellidos,
                                                apodo: fighter.apodo,
                                                peso: fighter.peso,
                                                genero: fighter.genero,
                                                photoUri: fighter.photoUri,
                                                edad: fighter.edad !== undefined && fighter.edad !== null
                                                    ? String(fighter.edad)
                                                    : undefined,
                                                altura: fighter.altura ? String(fighter.altura) : undefined,
                                                clubName: fighter.clubName,
                                                record: fighter.record
                                            }}
                                            variant="large"
                                            backgroundUri={compositionAssets?.backgroundUri || undefined}
                                            borderUri={compositionAssets?.borderUri || undefined}
                                            fighterLayers={compositionAssets?.fighterLayers || []}
                                            selectedStickers={compositionAssets?.selectedStickers || []}
                                            stickerTransforms={compositionAssets?.stickerTransforms || {}}
                                            companyLogoUri={compositionAssets?.companyLogoUri || companyLogoUri}
                                            onShare={handleShare}
                                        />
                                    </View>
                                )}
                            </View>

                            <View style={styles.infoContainer}>
                                <MotivationalQuote style={styles.message} />
                            </View>

                            <View style={styles.actions}>
                                <TouchableOpacity
                                    style={styles.primaryButton}
                                    onPress={onEdit}
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
                                    onPress={onClose}
                                >
                                    <Text style={styles.secondaryButtonText}>VOLVER AL INICIO</Text>
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
        backgroundColor: 'rgba(0,0,0,0.95)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: SPACING.md,
    },
    content: {
        width: '100%',
        maxWidth: 420,
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
        paddingVertical: SPACING.xl,
        paddingHorizontal: SPACING.lg,
        alignItems: 'center',
        gap: SPACING.lg,
    },
    headerIndicator: {
        width: 40,
        height: 4,
        backgroundColor: 'rgba(255,215,0,0.3)',
        borderRadius: 2,
        marginBottom: -SPACING.md,
    },
    titleContainer: {
        alignItems: 'center',
        gap: SPACING.xs,
    },
    title: {
        fontSize: 24,
        fontWeight: '900',
        color: '#FFD700',
        letterSpacing: 1.5,
        textAlign: 'center',
        ...createTextShadow('rgba(0,0,0,0.5)', 2, 2, 8),
    },
    subtitle: {
        fontSize: 13,
        color: 'rgba(255,255,255,0.6)',
        textAlign: 'center',
        fontWeight: '600',
    },
    cardContainer: {
        width: '100%',
        marginVertical: SPACING.sm,
    },
    cardWrapper: {
        width: '100%',
    },
    bakedCardWrapper: {
        width: '100%',
        aspectRatio: 1.9,
        backgroundColor: '#0a0a0a',
        borderRadius: 12,
        overflow: 'hidden',
        borderWidth: 1,
        borderColor: 'rgba(255,215,0,0.2)',
        ...SHADOWS.md,
    },
    bakedImage: {
        width: '100%',
        height: '100%',
    },
    shareOverlay: {
        position: 'absolute',
        top: 10,
        right: 10,
        backgroundColor: 'rgba(0,0,0,0.6)',
        padding: 10,
        borderRadius: BORDER_RADIUS.full,
        borderWidth: 1,
        borderColor: 'rgba(255,215,0,0.3)',
    },
    infoContainer: {
        width: '100%',
        paddingHorizontal: SPACING.md,
    },
    message: {
        fontSize: 13,
        color: 'rgba(255,255,255,0.5)',
        textAlign: 'center',
        fontStyle: 'italic',
        lineHeight: 18,
    },
    actions: {
        width: '100%',
        gap: SPACING.md,
        marginTop: SPACING.sm,
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
        fontSize: 16,
        fontWeight: '900',
        letterSpacing: 1,
        color: '#000',
    },
    secondaryButton: {
        width: '100%',
        paddingVertical: SPACING.sm,
        alignItems: 'center',
    },
    secondaryButtonText: {
        fontSize: 14,
        fontWeight: '700',
        color: COLORS.text.secondary,
        textDecorationLine: 'underline',
        opacity: 0.8,
    },
});
