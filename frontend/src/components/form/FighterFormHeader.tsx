import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, SPACING, TYPOGRAPHY } from '../../constants/theme';

export const FighterFormHeader: React.FC = () => {
  return (
    <LinearGradient
      colors={['rgba(255, 215, 0, 0.15)', 'transparent']}
      style={styles.header}
    >
      <View style={styles.headerGlowTop} />
      <Text style={styles.eventTitle}>EL JAB DORADO</Text>
      <View style={styles.titleDivider}>
        <View style={styles.dividerLine} />
        <Text style={styles.dividerIcon}>🥊</Text>
        <View style={styles.dividerLine} />
      </View>
      <Text style={styles.formTitle}>INSCRIPCIÓN DE PELEADOR</Text>
      <Text style={styles.subtitle}>
        Únete a la élite del boxeo profesional
      </Text>
    </LinearGradient>
  );
};

const styles = StyleSheet.create({
  header: {
    paddingTop: SPACING.xl,
    paddingHorizontal: SPACING.xl,
    paddingBottom: SPACING.xxl,
    position: 'relative',
    overflow: 'hidden',
  },
  headerGlowTop: {
    position: 'absolute',
    top: -50,
    left: '50%',
    width: 200,
    height: 200,
    backgroundColor: COLORS.primary,
    opacity: 0.1,
    borderRadius: 100,
    transform: [{ translateX: -100 }],
  },
  eventTitle: {
    fontSize: 36,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    textAlign: 'center',
    letterSpacing: 4,
    textShadowColor: COLORS.primary,
    textShadowOffset: { width: 0, height: 0 },
    textShadowRadius: 20,
  },
  titleDivider: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginVertical: SPACING.lg,
  },
  dividerLine: {
    height: 2,
    width: 60,
    backgroundColor: COLORS.primary,
    opacity: 0.3,
  },
  dividerIcon: {
    fontSize: 20,
    marginHorizontal: SPACING.md,
  },
  formTitle: {
    fontSize: TYPOGRAPHY.fontSize.xxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    textAlign: 'center',
    marginBottom: SPACING.sm,
  },
  subtitle: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
    textAlign: 'center',
  },
});
