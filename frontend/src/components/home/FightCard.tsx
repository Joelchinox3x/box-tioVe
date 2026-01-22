import React from 'react';
import { View, Text, StyleSheet, ImageBackground, TouchableOpacity } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';

interface Fighter {
  id?: number;
  nombre?: string;
  apodo?: string;
  empresa?: string;
  club_nombre?: string;
  foto_perfil?: string;
}

interface FightCardProps {
  fighter1: Fighter;
  fighter2: Fighter;
  onPress?: () => void;
  featured?: boolean;
}

export const FightCard: React.FC<FightCardProps> = ({
  fighter1,
  fighter2,
  onPress,
  featured = false,
}) => {
  return (
    <TouchableOpacity
      style={styles.container}
      onPress={onPress}
      activeOpacity={0.9}
    >
      <LinearGradient
        colors={['#1a1a1a', '#0a0a0a']}
        style={styles.gradient}
      >
        {/* Background decorativo */}
        <View style={styles.backgroundPattern}>
          <View style={styles.glowEffect} />
        </View>

        {/* Contenido principal */}
        <View style={styles.content}>
          {/* Peleador 1 */}
          <View style={styles.fighterSection}>
            <View style={styles.fighterImageContainer}>
              {fighter1.foto_perfil ? (
                <ImageBackground
                  source={{ uri: fighter1.foto_perfil }}
                  style={styles.fighterImage}
                  imageStyle={styles.fighterImageStyle}
                >
                  <LinearGradient
                    colors={['transparent', 'rgba(0,0,0,0.8)']}
                    style={styles.imageGradient}
                  />
                </ImageBackground>
              ) : (
                <View style={styles.placeholderImage}>
                  <Text style={styles.placeholderText}>👤</Text>
                </View>
              )}
            </View>
            <View style={styles.fighterInfo}>
              <Text style={styles.fighterApodo} numberOfLines={1}>
                {fighter1.apodo || fighter1.nombre || 'Por confirmar'}
              </Text>
              <Text style={styles.fighterCompany} numberOfLines={1}>
                {fighter1.empresa || fighter1.club_nombre || 'Sin club'}
              </Text>
            </View>
          </View>

          {/* VS Badge */}
          <View style={styles.vsContainer}>
            <LinearGradient
              colors={[COLORS.primary, '#FFA500']}
              style={styles.vsBadge}
            >
              <Text style={styles.vsText}>VS</Text>
            </LinearGradient>
          </View>

          {/* Peleador 2 */}
          <View style={styles.fighterSection}>
            <View style={styles.fighterImageContainer}>
              {fighter2.foto_perfil ? (
                <ImageBackground
                  source={{ uri: fighter2.foto_perfil }}
                  style={styles.fighterImage}
                  imageStyle={styles.fighterImageStyle}
                >
                  <LinearGradient
                    colors={['transparent', 'rgba(0,0,0,0.8)']}
                    style={styles.imageGradient}
                  />
                </ImageBackground>
              ) : (
                <View style={styles.placeholderImage}>
                  <Text style={styles.placeholderText}>👤</Text>
                </View>
              )}
            </View>
            <View style={styles.fighterInfo}>
              <Text style={styles.fighterApodo} numberOfLines={1}>
                {fighter2.apodo || fighter2.nombre || 'Por confirmar'}
              </Text>
              <Text style={styles.fighterCompany} numberOfLines={1}>
                {fighter2.empresa || fighter2.club_nombre || 'Sin club'}
              </Text>
            </View>
          </View>
        </View>

        {/* Badge destacado si es featured */}
        {featured && (
          <View style={styles.featuredBadge}>
            <Text style={styles.featuredText}>⭐ ESTELAR</Text>
          </View>
        )}
      </LinearGradient>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  container: {
    width: 320,
    height: 200,
    marginRight: SPACING.md,
  },
  gradient: {
    flex: 1,
    borderRadius: BORDER_RADIUS.lg,
    overflow: 'hidden',
    ...SHADOWS.lg,
  },
  backgroundPattern: {
    ...StyleSheet.absoluteFillObject,
  },
  glowEffect: {
    position: 'absolute',
    top: '50%',
    left: '50%',
    width: 200,
    height: 200,
    borderRadius: 100,
    backgroundColor: COLORS.primary,
    opacity: 0.1,
    transform: [{ translateX: -100 }, { translateY: -100 }],
  },
  content: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    padding: SPACING.lg,
  },
  fighterSection: {
    flex: 1,
    alignItems: 'center',
  },
  fighterImageContainer: {
    width: 80,
    height: 80,
    marginBottom: SPACING.sm,
  },
  fighterImage: {
    width: '100%',
    height: '100%',
  },
  fighterImageStyle: {
    borderRadius: BORDER_RADIUS.full,
    borderWidth: 2,
    borderColor: COLORS.primary,
  },
  imageGradient: {
    flex: 1,
    borderRadius: BORDER_RADIUS.full,
  },
  placeholderImage: {
    width: '100%',
    height: '100%',
    borderRadius: BORDER_RADIUS.full,
    backgroundColor: COLORS.surface,
    borderWidth: 2,
    borderColor: COLORS.border.primary,
    justifyContent: 'center',
    alignItems: 'center',
  },
  placeholderText: {
    fontSize: 40,
  },
  fighterInfo: {
    alignItems: 'center',
    width: '100%',
  },
  fighterApodo: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    textAlign: 'center',
  },
  fighterCompany: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
    marginTop: 2,
    textAlign: 'center',
  },
  vsContainer: {
    marginHorizontal: SPACING.sm,
  },
  vsBadge: {
    width: 50,
    height: 50,
    borderRadius: BORDER_RADIUS.full,
    justifyContent: 'center',
    alignItems: 'center',
    ...SHADOWS.md,
  },
  vsText: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    letterSpacing: 1,
  },
  featuredBadge: {
    position: 'absolute',
    top: SPACING.sm,
    left: SPACING.sm,
    backgroundColor: COLORS.primary,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.xs,
    borderRadius: BORDER_RADIUS.md,
  },
  featuredText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
  },
});
