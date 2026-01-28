import React, { useState, useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Dimensions, FlatList, TouchableOpacity, ImageBackground, Animated, Platform } from 'react-native';
import { useIsFocused } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import * as Haptics from 'expo-haptics';
import { useAudioPlayer } from 'expo-audio';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { bannerService, Banner } from '../../services/bannerService';
import { createShadow, createTextShadow } from '../../utils/shadows';
import { styles as bannerStyles } from './BannerCarousel.styles';

const { width } = Dimensions.get('window');

interface BannerCarouselProps {
    onPress?: () => void;
}

export const BannerCarousel: React.FC<BannerCarouselProps> = ({ onPress }) => {
    const [banners, setBanners] = useState<Banner[]>([]);
    const [currentIndex, setCurrentIndex] = useState(0);

    // Animations
    const pulseAnim = useRef(new Animated.Value(1)).current;
    const shakeAnim = useRef(new Animated.Value(0)).current;
    const glowAnim = useRef(new Animated.Value(0.3)).current;

    // Audio
    const bellPlayer = useAudioPlayer(require('../../../assets/sounds/bell-01.mp3'));
    const punch01Player = useAudioPlayer(require('../../../assets/sounds/punch-01.mp3'));
    const punch02Player = useAudioPlayer(require('../../../assets/sounds/punch-02.mp3'));
    const powerPunchPlayer = useAudioPlayer(require('../../../assets/sounds/power-punch.mp3'));

    useEffect(() => {
        loadBanners();
        startGlowAnimation();
    }, []);

    const loadBanners = async () => {
        try {
            const data = await bannerService.getAll(false);
            setBanners(data);
        } catch (error) {
            console.error('Error loading banners:', error);
        }
    };

    const startGlowAnimation = () => {
        Animated.loop(
            Animated.sequence([
                Animated.timing(glowAnim, { toValue: 0.8, duration: 1500, useNativeDriver: Platform.OS !== 'web' }),
                Animated.timing(glowAnim, { toValue: 0.3, duration: 1500, useNativeDriver: Platform.OS !== 'web' }),
            ])
        ).start();
    };

    const isFocused = useIsFocused();

    // Random Auto-rotation
    useEffect(() => {
        if (banners.length <= 1 || !isFocused) return;

        const interval = setInterval(() => {
            let nextIndex;
            do {
                nextIndex = Math.floor(Math.random() * banners.length);
            } while (nextIndex === currentIndex && banners.length > 1);

            setCurrentIndex(nextIndex);
        }, 5000);

        return () => clearInterval(interval);
    }, [currentIndex, banners.length, isFocused]);

    // Handle slide change effects
    useEffect(() => {
        if (banners.length > 0) {
            triggerImpactEffect();
        }
    }, [currentIndex]);

    const triggerImpactEffect = () => {
        // Sonido 1: Cambio de foto
        try {
            punch01Player.seekTo(0);
            punch01Player.play();
        } catch (e) { console.log('Audio error', e); }

        if (Platform.OS !== 'web') {
            Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Heavy);
        }

        Animated.sequence([
            Animated.timing(pulseAnim, { toValue: 0.95, duration: 50, useNativeDriver: Platform.OS !== 'web' }),
            Animated.spring(pulseAnim, { toValue: 1.05, friction: 3, tension: 40, useNativeDriver: Platform.OS !== 'web' }),
            Animated.sequence([
                Animated.timing(shakeAnim, { toValue: 10, duration: 50, useNativeDriver: Platform.OS !== 'web' }),
                Animated.timing(shakeAnim, { toValue: -10, duration: 50, useNativeDriver: Platform.OS !== 'web' }),
                Animated.timing(shakeAnim, { toValue: 5, duration: 50, useNativeDriver: Platform.OS !== 'web' }), // Justo aqui en el shake
                Animated.timing(shakeAnim, { toValue: 0, duration: 50, useNativeDriver: Platform.OS !== 'web' }),
            ]),
            Animated.timing(pulseAnim, { toValue: 1, duration: 200, useNativeDriver: Platform.OS !== 'web' }),
        ]).start();

        // Sonido 2: Shake (con un pequeño delay para que suene "combo")
        setTimeout(() => {
            try {
                punch02Player.seekTo(0);
                punch02Player.play();
            } catch (e) { console.log('Audio error 2', e); }
        }, 100);
    };

    const handlePress = () => {
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
        bellPlayer.play();

        // Resetear para la próxima vez (evita lag al dar click)
        setTimeout(() => {
            bellPlayer.seekTo(0);
        }, 1200);

        if (onPress) onPress();
    };

    if (banners.length === 0) return null;

    const currentBanner = banners[currentIndex];

    return (
        <View style={bannerStyles.container}>
            <Animated.View style={[
                bannerStyles.bannerWrapper,
                { transform: [{ scale: pulseAnim }, { translateX: shakeAnim }] }
            ]}>
                <TouchableOpacity
                    activeOpacity={0.9}
                    onPress={handlePress}
                    style={{ flex: 1 }}
                >
                    <ImageBackground
                        source={{ uri: currentBanner.url }}
                        style={bannerStyles.backgroundImage}
                        resizeMode="cover"
                        imageStyle={bannerStyles.backgroundImageStyle}
                    >
                        <LinearGradient
                            colors={['transparent', 'rgba(218, 165, 32, 0.4)', 'rgba(139, 101, 8, 0.95)']}
                            start={{ x: 0.5, y: 0 }}
                            end={{ x: 0.5, y: 1 }}
                            style={bannerStyles.gradient}
                        >
                            <View style={bannerStyles.content}>
                                <View style={bannerStyles.badge}>
                                    <Ionicons name="shield-checkmark" size={12} color="#000" />
                                    <Text style={bannerStyles.badgeText}>Box TioVE</Text>
                                </View>

                                <View style={bannerStyles.headerRow}>
                                    <Text style={bannerStyles.title}>¿QUIERES PELEAR?</Text>
                                    <View style={bannerStyles.iconContainer}>
                                        <Ionicons name="flash" size={24} color="#FFD700" />
                                    </View>
                                </View>

                                <Text style={bannerStyles.subtitle}>Inscríbete HOY y se una leyenda en el ring.</Text>

                                <View style={bannerStyles.buttonWrapper}>
                                    <LinearGradient
                                        colors={['#FFD700', '#E5C100']}
                                        style={bannerStyles.button}
                                    >
                                        <Text style={bannerStyles.buttonText}>REGISTRARME COMO PELEADOR</Text>
                                        <Ionicons name="arrow-forward" size={18} color="#000000" />
                                    </LinearGradient>
                                </View>
                            </View>
                            <Animated.View style={[bannerStyles.glowEffect, { opacity: glowAnim }]} />
                        </LinearGradient>
                    </ImageBackground>
                </TouchableOpacity>
            </Animated.View>
        </View>
    );
};
