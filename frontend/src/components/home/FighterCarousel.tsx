import React from 'react';
import { View, Text, StyleSheet, ScrollView, ImageBackground, TouchableOpacity } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../../constants/theme';
import { Config } from '../../config/config';

interface Fighter {
  id?: number;
  nombre?: string;
  apodo?: string;
  foto_perfil?: string;
  victorias?: number;
  derrotas?: number;
}

interface FighterCarouselProps {
  fighters: Fighter[];
  title?: string;
  subtitle?: string;
  onFighterPress?: (fighter: Fighter) => void;
}

export const FighterCarousel: React.FC<FighterCarouselProps> = ({
  fighters,
  title = 'Últimos Inscritos',
  subtitle = 'Luchadores ejecutivos destacados de esta temporada',
  onFighterPress,
}) => {
  if (!fighters || fighters.length === 0) {
    return null;
  }

  return (
    <View style={styles.container}>
      {/* Header de la sección */}
      <View style={styles.header}>
        <Text style={styles.title}>{title}</Text>
        <Text style={styles.subtitle}>{subtitle}</Text>
      </View>

      {/* Carrusel de peleadores */}
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {fighters.map((fighter, index) => (
          <TouchableOpacity
            key={fighter.id || index}
            style={styles.fighterCard}
            onPress={() => onFighterPress?.(fighter)}
            activeOpacity={0.9}
          >
            {fighter.foto_perfil ? (
              <ImageBackground
                source={{ uri: fighter.foto_perfil?.startsWith('http') ? fighter.foto_perfil : `${Config.BASE_URL}/${fighter.foto_perfil}` }}
                style={styles.fighterImage}
                imageStyle={styles.fighterImageStyle}
              >
                {/* Gradiente inferior */}
                <LinearGradient
                  colors={['transparent', 'rgba(0,0,0,0.9)']}
                  style={styles.imageGradient}
                >
                  {/* Nombre en la parte inferior */}
                  <View style={styles.fighterNameContainer}>
                    <Text style={styles.fighterApodo} numberOfLines={1}>
                      "{fighter.apodo || fighter.nombre}"
                    </Text>
                    {/* Record */}
                    {(fighter.victorias !== undefined || fighter.derrotas !== undefined) && (
                      <Text style={styles.fighterRecord}>
                        {fighter.victorias || 0}V - {fighter.derrotas || 0}D
                      </Text>
                    )}
                  </View>
                </LinearGradient>
              </ImageBackground>
            ) : (
              <View style={styles.placeholderImage}>
                <Text style={styles.placeholderIcon}>🥊</Text>
                <Text style={styles.placeholderName} numberOfLines={1}>
                  {fighter.apodo || fighter.nombre || 'Peleador'}
                </Text>
              </View>
            )}
          </TouchableOpacity>
        ))}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    paddingVertical: SPACING.lg,
  },
  header: {
    paddingHorizontal: SPACING.lg,
    marginBottom: SPACING.md,
  },
  title: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    marginBottom: SPACING.xs,
  },
  subtitle: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.tertiary,
    lineHeight: 18,
  },
  scrollContent: {
    paddingHorizontal: SPACING.lg,
    gap: SPACING.md,
  },
  fighterCard: {
    width: 140,
    height: 180,
    borderRadius: BORDER_RADIUS.lg,
    overflow: 'hidden',
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  fighterImage: {
    width: '100%',
    height: '100%',
  },
  fighterImageStyle: {
    borderRadius: BORDER_RADIUS.lg,
  },
  imageGradient: {
    flex: 1,
    justifyContent: 'flex-end',
  },
  fighterNameContainer: {
    padding: SPACING.sm,
  },
  fighterApodo: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    marginBottom: 2,
  },
  fighterRecord: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.primary,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
  },
  placeholderImage: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    padding: SPACING.sm,
  },
  placeholderIcon: {
    fontSize: 48,
    marginBottom: SPACING.sm,
  },
  placeholderName: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.secondary,
    textAlign: 'center',
  },
});
