import React, { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ImageBackground, Animated, Platform } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import * as Haptics from 'expo-haptics';
import { useAudioPlayer } from 'expo-audio';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../frontend/src/constants/theme';
import { styles } from './FighterCTABanner.styles';

interface FighterCTABannerProps {
    onPress?: () => void;
    title?: string;
    subtitle?: string;
    buttonText?: string;
}

export const FighterCTABanner: React.FC<FighterCTABannerProps> = ({
    onPress,
    title = '¿QUIERES PELEAR?',
    subtitle = 'Inscríbete y se una leyenda en el ring.',
    buttonText = 'REGISTRARME COMO PELEADOR',
}) => {
    // Estado para las imágenes
    const [images, setImages] = React.useState<any[]>([]);
    const [loading, setLoading] = React.useState(true);

    const [currentIndex, setCurrentIndex] = React.useState(0);
    const pulseAnim = useRef(new Animated.Value(1)).current;
    const shakeAnim = useRef(new Animated.Value(0)).current;
    const glowAnim = useRef(new Animated.Value(0.3)).current;

    // Audio players
    const bellPlayer = useAudioPlayer(require('../../../assets/sounds/bell-01.mp3'));
    const punchPlayer = useAudioPlayer(require('../../../assets/sounds/power-punch.mp3'));

    // Cargar imágenes desde API al montar
    useEffect(() => {
        const loadBanners = async () => {
            try {
                // Importar dinámicamente para evitar ciclos si es necesario, o usar el servicio directamente
                const { bannerService } = require('../../services/bannerService');
                const apiBanners = await bannerService.getAll();

                if (apiBanners && apiBanners.length > 0) {
                    // Convertir objetos banner a URIs
                    const bannerUris = apiBanners.map((b: any) => ({ uri: b.url }));
                    setImages(bannerUris);
                } else {
                    // Fallback a locales si no hay en API
                    loadLocalImages();
                }
            } catch (error) {
                console.error("Error loading banners", error);
                loadLocalImages();
            } finally {
                setLoading(false);
            }
        };

        const loadLocalImages = () => {
            // Carga DINÁMICA de imágenes locales (Fallback)
            const imagesContext = (require as any).context('../../../assets/banner_fighter', false, /\.(png|webp)$/);
            const localImages = imagesContext.keys().map((key: string) => imagesContext(key));
            setImages(localImages);
        };

        loadBanners();
    }, []);

    // Intervalo de rotación (segundos)
    const ROTATION_INTERVAL = 5000;

    useEffect(() => {
        if (images.length === 0) return;

        // Iniciar rotación de imágenes
        const intervalId = setInterval(() => {
            setCurrentIndex((prevIndex) => (prevIndex + 1) % images.length);
        }, ROTATION_INTERVAL);

        return () => clearInterval(intervalId);
    }, [images]);

    // Efecto cuando cambia la imagen (Golpe + Shake)
    useEffect(() => {
        if (currentIndex === 0 && Platform.OS !== 'web') {
            // No reproducir sonido en la carga inicial para no molestar
        } else {
            triggerImpactEffect();
        }
    }, [currentIndex]);

    const triggerImpactEffect = () => {
        // 1. Sonido de golpe (Solo si no es web o si el usuario ya interactuó)
        // En web, el audio automático suele estar bloqueado
        if (Platform.OS !== 'web') {
            punchPlayer.play();
        }

        // 2. Vibración Haptic fuerte (Solo en nativo, en web falla sin interacción)
        if (Platform.OS !== 'web') {
            Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Heavy);
        }

        // 3. Animación de sacudida (Shake) y escala
        Animated.sequence([
            // Pequeña contracción antes del golpe
            Animated.timing(pulseAnim, {
                toValue: 0.95,
                duration: 50,
                useNativeDriver: Platform.OS !== 'web',
            }),
            // Expansión rápida (golpe)
            Animated.spring(pulseAnim, {
                toValue: 1.05,
                friction: 3,
                tension: 40,
                useNativeDriver: Platform.OS !== 'web',
            }),
            // Shake horizontal rápido
            Animated.sequence([
                Animated.timing(shakeAnim, { toValue: 10, duration: 50, useNativeDriver: Platform.OS !== 'web' }),
                Animated.timing(shakeAnim, { toValue: -10, duration: 50, useNativeDriver: Platform.OS !== 'web' }),
                Animated.timing(shakeAnim, { toValue: 5, duration: 50, useNativeDriver: Platform.OS !== 'web' }),
                Animated.timing(shakeAnim, { toValue: 0, duration: 50, useNativeDriver: Platform.OS !== 'web' }),
            ]),
            // Volver a tamaño normal
            Animated.timing(pulseAnim, {
                toValue: 1,
                duration: 200,
                useNativeDriver: Platform.OS !== 'web',
            }),
        ]).start();
    };

    const handlePress = () => {
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
        bellPlayer.play();
        if (onPress) onPress();
    };

    useEffect(() => {
        // Animación de brillo continuo
        Animated.loop(
            Animated.sequence([
                Animated.timing(glowAnim, {
                    toValue: 0.8,
                    duration: 1500,
                    useNativeDriver: Platform.OS !== 'web',
                }),
                Animated.timing(glowAnim, {
                    toValue: 0.3,
                    duration: 1500,
                    useNativeDriver: Platform.OS !== 'web',
                }),
            ])
        ).start();
    }, []);

    return (
        <View style={styles.container}>
            <Animated.View style={[
                styles.bannerWrapper,
                {
                    transform: [
                        { scale: pulseAnim },
                        { translateX: shakeAnim }
                    ]
                }
            ]}>
                <TouchableOpacity
                    style={styles.banner}
                    onPress={handlePress}
                    activeOpacity={0.9}
                >
                    <ImageBackground
                        source={images.length > 0 ? images[currentIndex] : null}
                        style={styles.backgroundImage}
                        resizeMode="cover"
                        imageStyle={styles.backgroundImageStyle}
                    >
                        <LinearGradient
                            // Degradado DORADO (transparente -> oro medio -> oro oscuro/bronce)
                            // Usamos tonos 'DarkGoldenrod' para que las letras blancas se sigan leyendo
                            colors={['transparent', 'rgba(218, 165, 32, 0.4)', 'rgba(139, 101, 8, 0.95)']}
                            start={{ x: 0.5, y: 0 }}
                            end={{ x: 0.5, y: 1 }}
                            style={styles.gradient}
                        >
                            <View style={styles.content}>
                                {/* Badge de estatus */}
                                <View style={styles.badge}>
                                    <Ionicons name="shield-checkmark" size={12} color="#000" />
                                    <Text style={styles.badgeText}>Box TioVE</Text>
                                </View>

                                <View style={styles.headerRow}>
                                    <Text style={styles.title}>{title}</Text>
                                    <View style={styles.iconContainer}>
                                        <Ionicons name="flash" size={24} color="#FFD700" />
                                    </View>
                                </View>

                                <Text style={styles.subtitle}>{subtitle}</Text>

                                <View style={styles.buttonWrapper}>
                                    <LinearGradient
                                        colors={['#FFD700', '#E5C100']} // Degradado Dorado Fuerte
                                        style={styles.button}
                                    >
                                        <Text style={styles.buttonText}>{buttonText}</Text>
                                        <Ionicons name="arrow-forward" size={18} color="#000000" />
                                    </LinearGradient>
                                </View>
                            </View>

                            {/* Efecto de borde brillante animado */}
                            <Animated.View style={[styles.glowEffect, { opacity: glowAnim }]} />
                        </LinearGradient>
                    </ImageBackground>
                </TouchableOpacity>
            </Animated.View>
        </View>
    );
};

