import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../../constants/theme';

export const SponsorFooter: React.FC = () => {
  return (
    <View style={styles.footer}>
      <View style={styles.divider} />
      <Text style={styles.label}>AUSPICIADORES OFICIALES</Text>
      <View style={styles.sponsorContainer}>
        <LinearGradient
          colors={['rgba(255, 215, 0, 0.1)', 'rgba(255, 215, 0, 0.05)']}
          style={styles.sponsorCard}
        >
          <Text style={styles.badge}>⚡ AI POWERED</Text>
          <Text style={styles.name}>Claude</Text>
          <Text style={styles.tagline}>by Anthropic</Text>
          <View style={styles.glow} />
        </LinearGradient>
      </View>
      <Text style={styles.text}>
        Sistema de inscripción desarrollado con inteligencia artificial
      </Text>
    </View>
  );
};

const styles = StyleSheet.create({
  footer: {
    marginTop: SPACING.xxl,
    paddingTop: SPACING.xl,
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.border.primary,
    marginBottom: SPACING.xl,
  },
  label: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.tertiary,
    textAlign: 'center',
    letterSpacing: 2,
    marginBottom: SPACING.lg,
  },
  sponsorContainer: {
    alignItems: 'center',
    marginBottom: SPACING.lg,
  },
  sponsorCard: {
    paddingVertical: SPACING.lg,
    paddingHorizontal: SPACING.xxl,
    borderRadius: BORDER_RADIUS.lg,
    borderWidth: 1,
    borderColor: COLORS.primary,
    alignItems: 'center',
    position: 'relative',
    overflow: 'hidden',
  },
  badge: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    letterSpacing: 1,
    marginBottom: SPACING.xs,
  },
  name: {
    fontSize: 28,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    letterSpacing: 2,
    marginBottom: SPACING.xs,
  },
  tagline: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
    fontStyle: 'italic',
  },
  glow: {
    position: 'absolute',
    top: '50%',
    left: '50%',
    width: 100,
    height: 100,
    backgroundColor: COLORS.primary,
    opacity: 0.05,
    borderRadius: 50,
    transform: [{ translateX: -50 }, { translateY: -50 }],
  },
  text: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
    textAlign: 'center',
    fontStyle: 'italic',
  },
});
