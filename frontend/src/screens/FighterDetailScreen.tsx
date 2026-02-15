import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  SafeAreaView,
  TouchableOpacity,
  Share,
  Alert,
  Image,
  useWindowDimensions,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute } from '@react-navigation/native';
import { COLORS, SPACING, TYPOGRAPHY } from '../constants/theme';
import { Config } from '../config/config';
import { getCategoria } from '../utils/categories';

export default function FighterDetailScreen() {
  const navigation = useNavigation<any>();
  const route = useRoute<any>();
  const { fighter } = route.params;
  const { width } = useWindowDimensions();

  const getImageUrl = (path: string | null | undefined) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    return `${Config.BASE_URL}/${path}`;
  };

  const handleShare = async () => {
    try {
      await Share.share({
        message: `Mira el perfil del peleador ${fighter.nombre} ${fighter.apellido} (${fighter.victorias}-${fighter.derrotas}) en nuestra app!`,
        title: `Perfil de ${fighter.nombre}`,
      });
    } catch (error: any) {
      Alert.alert(error.message);
    }
  };

  const getRecordColor = () => {
    const total = fighter.victorias + fighter.derrotas + fighter.empates;
    if (total === 0) return COLORS.text.tertiary;
    const winRate = fighter.victorias / total;
    if (winRate >= 0.7) return '#27ae60';
    if (winRate >= 0.4) return '#f39c12';
    return '#e74c3c';
  };

  const bigNameFontSize =
    width < 360 ? 18 : width < 420 ? 20 : TYPOGRAPHY.fontSize.xxl;

  const bigApodoFontSize =
    width < 360 ? 14 : TYPOGRAPHY.fontSize.xl;

  return (
    <SafeAreaView style={styles.container}>
      {/* Navbar */}
      <View style={styles.navBar}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color={COLORS.text.primary} />
        </TouchableOpacity>
        <Text style={styles.navTitle}>PERFIL</Text>
        <TouchableOpacity onPress={handleShare}>
          <Ionicons name="share-social-outline" size={24} color={COLORS.primary} />
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        <View style={styles.fullCard}>
          {fighter.total_promociones > 0 && (
            <View style={styles.starBadge}>
              <Ionicons name="star" size={16} color="#f39c12" />
              <Text style={styles.starText}>{fighter.total_promociones} Promociones</Text>
            </View>
          )}

          <View style={styles.headerCenter}>
            <View style={styles.largeAvatar}>
              {fighter.foto_perfil ? (
                <Image
                  source={{ uri: getImageUrl(fighter.foto_perfil) || '' }}
                  style={styles.avatarImage}
                  resizeMode="cover"
                />
              ) : (
                <Ionicons
                  name={fighter.genero?.toLowerCase() === 'femenino' ? 'woman' : 'man'}
                  size={40}
                  color={COLORS.text.inverse}
                />
              )}
            </View>

            {fighter.apodo && (
              <Text
                style={[styles.bigApodo, { fontSize: bigApodoFontSize }]}
                numberOfLines={1}
                ellipsizeMode="tail"
              >
                "{fighter.apodo}"
              </Text>
            )}

            <Text
              style={[styles.bigName, { fontSize: bigNameFontSize }]}
              numberOfLines={2}
              ellipsizeMode="tail"
            >
              {fighter.nombre} {fighter.apellido}
            </Text>

            <View style={[styles.recordBadge, { backgroundColor: getRecordColor() }]}>
              <Text style={styles.recordText}>
                Récord: {fighter.victorias} - {fighter.derrotas} - {fighter.empates}
              </Text>
            </View>
          </View>

          <View style={styles.divider} />

          {/* Detalles */}
          <View style={styles.detailsGrid}>
            <DetailItem icon="body" label="Edad" value={`${fighter.edad} años`} />
            <DetailItem icon="barbell" label="Peso" value={`${fighter.peso || fighter.peso_actual} kg`} />
            <DetailItem icon="resize" label="Altura" value={`${fighter.altura} m`} />
            <DetailItem icon="trophy" label="Categoría" value={fighter.categoria || getCategoria(fighter.peso || fighter.peso_actual)} />
            <DetailItem icon="fitness" label="Estilo" value={fighter.estilo || 'N/A'} />
            <DetailItem icon="business" label="Club" value={fighter.club_nombre || 'Indep.'} />
          </View>

          {/* Stats */}
          <View style={styles.statsRow}>
            <StatBox number={fighter.victorias} label="GANADAS" color="#27ae60" />
            <StatBox number={fighter.derrotas} label="PERDIDAS" color="#e74c3c" />
            <StatBox number={fighter.empates} label="EMPATES" color="#f39c12" />
          </View>
        </View>

        <TouchableOpacity
          style={styles.viewMoreButton}
          onPress={() => navigation.navigate('FighterStats', { fighter })}
        >
          <Text style={styles.viewMoreText}>VER MÁS DETALLES E HISTORIAL</Text>
          <Ionicons name="arrow-forward-circle" size={24} color={COLORS.text.inverse} />
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
}

const DetailItem = ({ icon, label, value }: any) => (
  <View style={styles.detailItem}>
    <Ionicons
      name={icon}
      size={20}
      color={COLORS.text.tertiary}
      style={styles.detailIcon}
    />
    <View style={styles.detailTextContainer}>
      <Text style={styles.detailLabel}>{label}</Text>
      <Text
        style={styles.detailValue}
        numberOfLines={label === 'Club' ? 3 : 1}
        ellipsizeMode="tail"
      >
        {value}
      </Text>
    </View>
  </View>
);

const StatBox = ({ number, label, color }: any) => (
  <View style={styles.statBox}>
    <Text style={[styles.statNumber, { color }]}>{number}</Text>
    <Text style={styles.statLabel}>{label}</Text>
  </View>
);

/* ------------------ Styles ------------------ */

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: COLORS.background },

  navBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: SPACING.lg,
  },

  backButton: { padding: SPACING.xs },

  navTitle: {
    color: COLORS.text.primary,
    fontWeight: 'bold',
    fontSize: TYPOGRAPHY.fontSize.lg,
  },

  scrollContent: {
    paddingHorizontal: SPACING.md,
    paddingBottom: SPACING.xl,
  },

  fullCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 20,
    padding: SPACING.lg,
    marginBottom: SPACING.xl,
    overflow: 'hidden',
  },

  starBadge: {
    flexDirection: 'row',
    alignSelf: 'center',
    backgroundColor: 'rgba(243,156,18,0.15)',
    padding: SPACING.xs,
    borderRadius: 10,
    marginBottom: SPACING.md,
  },

  starText: {
    color: '#f39c12',
    fontWeight: 'bold',
    fontSize: 12,
    marginLeft: 4,
  },

  headerCenter: { alignItems: 'center', marginBottom: SPACING.lg },

  largeAvatar: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: COLORS.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: SPACING.md,
    overflow: 'hidden',
    borderWidth: 2,
    borderColor: COLORS.primary + '40',
  },

  avatarImage: { width: '100%', height: '100%' },

  bigApodo: {
    color: COLORS.primary,
    fontWeight: 'bold',
    fontStyle: 'italic',
  },

  bigName: {
    color: COLORS.text.primary,
    fontWeight: 'bold',
    textAlign: 'center',
    maxWidth: '95%',
  },

  recordBadge: {
    marginTop: SPACING.md,
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.xs,
    borderRadius: 20,
  },

  recordText: { color: '#FFF', fontWeight: 'bold' },

  divider: {
    height: 1,
    backgroundColor: COLORS.border.primary,
    marginVertical: SPACING.md,
  },

  detailsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },

  /* 👇 ÚNICOS CAMBIOS IMPORTANTES AQUÍ */
  detailItem: {
    width: '48%',
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: COLORS.background,
    padding: SPACING.md,
    borderRadius: 12,
    marginBottom: SPACING.md,
  },

  detailIcon: {
    marginRight: SPACING.sm,
    marginTop: 2,
  },

  detailTextContainer: {
    flex: 1,
  },

  detailLabel: {
    fontSize: 10,
    color: COLORS.text.tertiary,
    textTransform: 'uppercase',
    marginBottom: 2,
  },

  detailValue: {
    fontSize: 14,
    color: COLORS.text.secondary,
    fontWeight: '600',
    lineHeight: 18,
  },
  /* 👆 FIN CAMBIOS */

  statsRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: SPACING.lg,
    flexWrap: 'wrap',
  },

  statBox: {
    flex: 1,
    alignItems: 'center',
    minWidth: 80,
    paddingVertical: SPACING.sm,
  },

  statNumber: { fontSize: 22, fontWeight: 'bold' },
  statLabel: { fontSize: 10, color: COLORS.text.tertiary },

  viewMoreButton: {
    backgroundColor: COLORS.primary,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    padding: SPACING.md,
    borderRadius: 12,
  },

  viewMoreText: {
    color: COLORS.text.inverse,
    fontWeight: 'bold',
    fontSize: 16,
    marginRight: 6,
  },
});
