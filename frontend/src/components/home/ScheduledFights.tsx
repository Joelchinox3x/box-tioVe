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

interface Fight {
  id?: number;
  peleador1?: Fighter;
  peleador2?: Fighter;
  categoria?: string;
  rondas?: number;
  fecha_pelea?: string;
}

interface ScheduledFightsProps {
  fights: Fight[];
  title?: string;
  subtitle?: string;
  onFightPress?: (fight: Fight) => void;
  emptyMessage?: string;
}

export const ScheduledFights: React.FC<ScheduledFightsProps> = ({
  fights,
  title = 'Peleas Pactadas',
  subtitle = 'Unóximo manicinos procinon peleadas fightos',
  onFightPress,
  emptyMessage = 'No hay peleas confirmadas aún',
}) => {
  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.title}>{title}</Text>
        {subtitle && <Text style={styles.subtitle}>{subtitle}</Text>}
      </View>

      {/* Lista de peleas */}
      {fights && fights.length > 0 ? (
        <View style={styles.fightsContainer}>
          {fights.map((fight, index) => (
            <TouchableOpacity
              key={fight.id || index}
              style={styles.fightCard}
              onPress={() => onFightPress?.(fight)}
              activeOpacity={0.9}
            >
              <LinearGradient
                colors={['#1a1a1a', '#0f0f0f']}
                style={styles.cardGradient}
              >
                {/* Peleador 1 */}
                <View style={styles.fighterContainer}>
                  <View style={styles.fighterImageWrapper}>
                    {fight.peleador1?.foto_perfil ? (
                      <ImageBackground
                        source={{ uri: fight.peleador1.foto_perfil }}
                        style={styles.fighterImage}
                        imageStyle={styles.fighterImageStyle}
                      />
                    ) : (
                      <View style={styles.placeholderImage}>
                        <Text style={styles.placeholderText}>👤</Text>
                      </View>
                    )}
                  </View>
                  <View style={styles.fighterInfo}>
                    <Text style={styles.fighterApodo} numberOfLines={1}>
                      {fight.peleador1?.apodo || 'Por confirmar'}
                    </Text>
                    <Text style={styles.fighterNickname} numberOfLines={1}>
                      "{fight.peleador1?.nombre || 'The Fighter'}"
                    </Text>
                    <Text style={styles.fighterCompany} numberOfLines={1}>
                      {fight.peleador1?.empresa || fight.peleador1?.club_nombre || 'Sin empresa'}
                    </Text>
                  </View>
                </View>

                {/* VS Badge */}
                <View style={styles.vsContainer}>
                  <View style={styles.vsBadge}>
                    <Text style={styles.vsText}>VS</Text>
                  </View>
                </View>

                {/* Peleador 2 */}
                <View style={[styles.fighterContainer, styles.fighterContainerRight]}>
                  <View style={styles.fighterInfo}>
                    <Text style={[styles.fighterApodo, styles.textRight]} numberOfLines={1}>
                      {fight.peleador2?.apodo || 'Por confirmar'}
                    </Text>
                    <Text style={[styles.fighterNickname, styles.textRight]} numberOfLines={1}>
                      "{fight.peleador2?.nombre || 'The Fighter'}"
                    </Text>
                    <Text style={[styles.fighterCompany, styles.textRight]} numberOfLines={1}>
                      {fight.peleador2?.empresa || fight.peleador2?.club_nombre || 'Sin empresa'}
                    </Text>
                  </View>
                  <View style={styles.fighterImageWrapper}>
                    {fight.peleador2?.foto_perfil ? (
                      <ImageBackground
                        source={{ uri: fight.peleador2.foto_perfil }}
                        style={styles.fighterImage}
                        imageStyle={styles.fighterImageStyle}
                      />
                    ) : (
                      <View style={styles.placeholderImage}>
                        <Text style={styles.placeholderText}>👤</Text>
                      </View>
                    )}
                  </View>
                </View>
              </LinearGradient>
            </TouchableOpacity>
          ))}
        </View>
      ) : (
        <View style={styles.emptyContainer}>
          <Text style={styles.emptyText}>{emptyMessage}</Text>
        </View>
      )}
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
  },
  fightsContainer: {
    paddingHorizontal: SPACING.lg,
    gap: SPACING.md,
  },
  fightCard: {
    borderRadius: BORDER_RADIUS.lg,
    overflow: 'hidden',
    ...SHADOWS.md,
  },
  cardGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: SPACING.lg,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    borderRadius: BORDER_RADIUS.lg,
  },
  fighterContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  fighterContainerRight: {
    flexDirection: 'row-reverse',
  },
  fighterImageWrapper: {
    width: 60,
    height: 60,
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
    fontSize: 28,
  },
  fighterInfo: {
    flex: 1,
  },
  fighterApodo: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    marginBottom: 2,
  },
  fighterNickname: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.secondary,
    marginBottom: 2,
  },
  fighterCompany: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
  },
  textRight: {
    textAlign: 'right',
  },
  vsContainer: {
    marginHorizontal: SPACING.sm,
  },
  vsBadge: {
    width: 44,
    height: 44,
    borderRadius: BORDER_RADIUS.full,
    backgroundColor: COLORS.surface,
    borderWidth: 2,
    borderColor: COLORS.primary,
    justifyContent: 'center',
    alignItems: 'center',
  },
  vsText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    letterSpacing: 1,
  },
  emptyContainer: {
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.xl,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.tertiary,
    textAlign: 'center',
  },
});
