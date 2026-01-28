import React, { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ImageBackground, Animated, Platform } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import * as Haptics from 'expo-haptics';
import { useAudioPlayer } from 'expo-audio';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { createTextShadow } from '../../utils/shadows';

interface GeneralTicketBannerProps {
  onPress?: () => void;
  title?: string;
  subtitle?: string;
  buttonText?: string;
  isVIP?: boolean;
}

export const GeneralTicketBanner: React.FC<GeneralTicketBannerProps> = ({
  onPress,
  title = 'Entradas General',
  subtitle = 'Asegura tu lugar en Box TioVE - El Jab Dorado',
  buttonText = 'Comprar S/. 10',
  isVIP = false,
}) => {
  const pulseAnim = useRef(new Animated.Value(1)).current;
  const glowAnim = useRef(new Animated.Value(0.4)).current;

  // Audio player usando expo-audio
  const player = useAudioPlayer(require('../../../assets/sounds/bell-02.mp3'));

  useEffect(() => {
    // Animación de pulso
    Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, {
          toValue: 1.015,
          duration: 2500,
          useNativeDriver: Platform.OS !== 'web',
        }),
        Animated.timing(pulseAnim, {
          toValue: 1,
          duration: 2500,
          useNativeDriver: Platform.OS !== 'web',
        }),
      ])
    ).start();

    // Animación de brillo
    Animated.loop(
      Animated.sequence([
        Animated.timing(glowAnim, {
          toValue: 0.8,
          duration: 2000,
          useNativeDriver: Platform.OS !== 'web',
        }),
        Animated.timing(glowAnim, {
          toValue: 0.4,
          duration: 2000,
          useNativeDriver: Platform.OS !== 'web',
        }),
      ])
    ).start();
  }, []);

  const handlePress = () => {
    Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);

    // Reproducir sonido con expo-audio
    player.seekTo(0);
    player.play();

    if (onPress) onPress();
  };

  return (
    <View style={styles.container}>
      <Animated.View style={[styles.bannerWrapper, { transform: [{ scale: pulseAnim }] }]}>
        <TouchableOpacity
          style={styles.banner}
          onPress={handlePress}
          activeOpacity={0.9}
        >
          <ImageBackground
            source={require('../../../assets/ticket_banner_bg.png')}
            style={styles.backgroundImage}
            resizeMode="cover"
            imageStyle={styles.backgroundImageStyle}
          >
            <LinearGradient
              colors={
                isVIP
                  ? ['rgba(0,0,0,0.2)', 'rgba(184,134,11,0.6)', 'rgba(0,0,0,0.9)']
                  : ['rgba(0,0,0,0.3)', 'rgba(42,42,42,0.5)', 'rgba(0,0,0,0.9)']
              }
              start={{ x: 0.5, y: 0 }}
              end={{ x: 0.5, y: 1 }}
              style={styles.gradient}
            >
              <View style={styles.content}>
                {/* Badge de estatus */}
                <View style={[styles.badge, isVIP && styles.badgeVIP]}>
                  <Ionicons name={isVIP ? "star" : "flash"} size={12} color="#000" />
                  <Text style={styles.badgeText}>
                    {isVIP ? 'EXPERIENCIA VIP' : 'ACCESO TOTAL'}
                  </Text>
                </View>

                <View style={styles.headerRow}>
                  <Text style={styles.title}>{title}</Text>
                  <Ionicons
                    name={isVIP ? "ribbon" : "ticket"}
                    size={28}
                    color={COLORS.primary}
                  />
                </View>

                <Text style={styles.subtitle}>{subtitle}</Text>

                <View style={styles.buttonWrapper}>
                  <LinearGradient
                    colors={isVIP ? ['#FFD700', '#B8860B'] : [COLORS.primary, '#FFA500']}
                    style={styles.button}
                  >
                    <Text style={styles.buttonText}>{buttonText}</Text>
                    <Ionicons name="cart" size={18} color={COLORS.text.inverse} />
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

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.lg,
  },
  bannerWrapper: {
    borderRadius: BORDER_RADIUS.xl,
    ...SHADOWS.lg,
    elevation: 10,
  },
  banner: {
    borderRadius: BORDER_RADIUS.xl,
    overflow: 'hidden',
    minHeight: 220,
    backgroundColor: '#000',
  },
  backgroundImage: {
    flex: 1,
    width: '100%',
    minHeight: 220,
  },
  backgroundImageStyle: Platform.select({
    web: {
      width: '100%',
      height: '100%',
      objectFit: 'cover' as any,
    },
    default: {},
  }),
  gradient: {
    flex: 1,
    padding: SPACING.md,
    paddingTop: SPACING.lg,
    paddingBottom: SPACING.lg,
    justifyContent: 'flex-end',
  },
  content: {
    gap: SPACING.xs,
  },
  badge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primary,
    paddingHorizontal: SPACING.sm,
    paddingVertical: 4,
    borderRadius: BORDER_RADIUS.sm,
    alignSelf: 'flex-start',
    marginBottom: SPACING.xs,
    gap: 4,
  },
  badgeVIP: {
    backgroundColor: '#FFD700',
  },
  badgeText: {
    fontSize: 9,
    fontWeight: '900',
    color: '#000',
    letterSpacing: 0.5,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  title: {
    fontSize: 24,
    fontWeight: '900',
    color: '#FFFFFF',
    letterSpacing: 0.5,
    flexShrink: 1,
    ...createTextShadow('rgba(0, 0, 0, 0.8)', 2, 2, 10),
  },
  subtitle: {
    fontSize: 13,
    color: 'rgba(255, 255, 255, 0.95)',
    lineHeight: 17,
    marginBottom: SPACING.sm,
    fontWeight: '500',
    ...createTextShadow('rgba(0, 0, 0, 0.5)', 1, 1, 5),
  },
  buttonWrapper: {
    alignSelf: 'flex-start',
  },
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.sm,
    borderRadius: BORDER_RADIUS.full,
    gap: SPACING.xs,
    elevation: 5,
  },
  buttonText: {
    fontSize: 12,
    fontWeight: '900',
    color: COLORS.text.inverse,
    letterSpacing: 0.5,
    flexShrink: 1,
  },
  glowEffect: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    borderWidth: 2,
    borderColor: 'rgba(255, 215, 0, 0.3)',
    borderRadius: BORDER_RADIUS.xl,
  },
});
