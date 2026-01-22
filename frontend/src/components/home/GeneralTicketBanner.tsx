import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ImageBackground } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';

interface GeneralTicketBannerProps {
  onPress?: () => void;
  title?: string;
  subtitle?: string;
  buttonText?: string;
  isVIP?: boolean; // Para futuro
}

export const GeneralTicketBanner: React.FC<GeneralTicketBannerProps> = ({
  onPress,
  title = 'Entradas General',
  subtitle = 'Asegura tu lugar en el evento',
  buttonText = 'Comprar Ahora',
  isVIP = false,
}) => {
  return (
    <View style={styles.container}>
      <TouchableOpacity
        style={styles.banner}
        onPress={onPress}
        activeOpacity={0.9}
      >
        {/* Background decorativo */}
        <View style={styles.backgroundContainer}>
          {/* Efecto de luces */}
          <View style={[styles.lightBeam, styles.lightBeam1]} />
          <View style={[styles.lightBeam, styles.lightBeam2]} />
          <View style={[styles.lightBeam, styles.lightBeam3]} />
        </View>

        <LinearGradient
          colors={
            isVIP
              ? ['#FFD700', '#FFA500', '#FF8C00']
              : ['#2a2a2a', '#1a1a1a', '#000000']
          }
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={styles.gradient}
        >
          {/* Contenido */}
          <View style={styles.content}>
            {/* Icono */}
            <View style={styles.iconContainer}>
              <LinearGradient
                colors={[COLORS.primary, '#FFA500']}
                style={styles.iconGradient}
              >
                <Ionicons
                  name={isVIP ? 'star' : 'ticket'}
                  size={32}
                  color={COLORS.text.inverse}
                />
              </LinearGradient>
            </View>

            {/* Textos */}
            <View style={styles.textContainer}>
              <Text style={[styles.title, isVIP && styles.titleVIP]}>
                {title}
              </Text>
              <Text style={[styles.subtitle, isVIP && styles.subtitleVIP]}>
                {subtitle}
              </Text>
            </View>

            {/* Botón CTA */}
            <View style={styles.buttonContainer}>
              <LinearGradient
                colors={
                  isVIP
                    ? ['#000000', '#1a1a1a']
                    : [COLORS.primary, '#FFA500']
                }
                style={styles.button}
              >
                <Text style={[styles.buttonText, isVIP && styles.buttonTextVIP]}>
                  {buttonText}
                </Text>
                <Ionicons
                  name="arrow-forward"
                  size={20}
                  color={isVIP ? COLORS.primary : COLORS.text.inverse}
                />
              </LinearGradient>
            </View>
          </View>

          {/* Detalles decorativos */}
          <View style={styles.decorativeElements}>
            {[...Array(6)].map((_, i) => (
              <View
                key={i}
                style={[
                  styles.decorativeDot,
                  {
                    opacity: 0.1 + (i * 0.1),
                    transform: [{ scale: 0.5 + (i * 0.1) }],
                  },
                ]}
              />
            ))}
          </View>
        </LinearGradient>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.lg,
  },
  banner: {
    borderRadius: BORDER_RADIUS.xl,
    overflow: 'hidden',
    ...SHADOWS.lg,
  },
  backgroundContainer: {
    ...StyleSheet.absoluteFillObject,
    overflow: 'hidden',
  },
  lightBeam: {
    position: 'absolute',
    backgroundColor: COLORS.primary,
    opacity: 0.1,
  },
  lightBeam1: {
    width: 200,
    height: 200,
    borderRadius: 100,
    top: -50,
    left: -50,
  },
  lightBeam2: {
    width: 150,
    height: 150,
    borderRadius: 75,
    bottom: -30,
    right: -30,
  },
  lightBeam3: {
    width: 100,
    height: 100,
    borderRadius: 50,
    top: '50%',
    right: '20%',
  },
  gradient: {
    padding: SPACING.xl,
    minHeight: 160,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  content: {
    flexDirection: 'column',
    gap: SPACING.md,
  },
  iconContainer: {
    alignSelf: 'flex-start',
  },
  iconGradient: {
    width: 64,
    height: 64,
    borderRadius: BORDER_RADIUS.full,
    justifyContent: 'center',
    alignItems: 'center',
    ...SHADOWS.md,
  },
  textContainer: {
    gap: SPACING.xs,
  },
  title: {
    fontSize: TYPOGRAPHY.fontSize.xxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    letterSpacing: 0.5,
  },
  titleVIP: {
    color: COLORS.text.inverse,
  },
  subtitle: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
    lineHeight: 20,
  },
  subtitleVIP: {
    color: COLORS.text.inverse,
    opacity: 0.9,
  },
  buttonContainer: {
    alignSelf: 'flex-start',
    marginTop: SPACING.sm,
  },
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
    paddingHorizontal: SPACING.xl,
    paddingVertical: SPACING.md,
    borderRadius: BORDER_RADIUS.full,
    ...SHADOWS.md,
  },
  buttonText: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    letterSpacing: 0.5,
  },
  buttonTextVIP: {
    color: COLORS.primary,
  },
  decorativeElements: {
    position: 'absolute',
    right: SPACING.lg,
    top: SPACING.lg,
    flexDirection: 'row',
    gap: 4,
  },
  decorativeDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.primary,
  },
});
