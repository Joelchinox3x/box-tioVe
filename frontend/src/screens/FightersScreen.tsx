import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
  TouchableOpacity,
  TextInput,
  ScrollView,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { COLORS, SPACING, TYPOGRAPHY } from '../constants/theme';
import { createShadow } from '../utils/shadows';
import api from '../services/api';

interface Peleador {
  id: number;
  nombre: string;
  apellido: string;
  apodo: string | null;
  dni: string;
  email: string;
  telefono: string;
  genero: string;
  fecha_nacimiento: string;
  edad: number;
  peso: number;
  altura: number;
  estilo: string | null;
  categoria: string | null;
  club_nombre: string | null;
  victorias: number;
  derrotas: number;
  empates: number;
  total_promociones: number;
  estado_inscripcion: string;
  foto_perfil: string | null; 
}

const WEIGHT_CATEGORIES = [
  { id: 'mosca', label: 'Mosca', min: 0, max: 50.8 },
  { id: 'pluma', label: 'Pluma', min: 50.8, max: 57.15 },
  { id: 'ligero', label: 'Ligero', min: 57.15, max: 61.23 },
  { id: 'welter', label: 'Wélter', min: 61.23, max: 66.68 },
  { id: 'mediano', label: 'Mediano', min: 66.68, max: 72.57 },
  { id: 'mediopesado', label: 'Mediopesado', min: 72.57, max: 91 },
  { id: 'superpesado', label: 'Superpesado', min: 91, max: 300 },
];

type FilterType = 'all' | 'destacados' | 'masculino' | 'femenino';

export default function FightersScreen() {
  const navigation = useNavigation<any>();

  const [loading, setLoading] = useState(true);
  const [fighters, setFighters] = useState<Peleador[]>([]);
  const [filteredFighters, setFilteredFighters] = useState<Peleador[]>([]);

  const [searchQuery, setSearchQuery] = useState('');
  const [filterType, setFilterType] = useState<FilterType>('all');
  const [selectedCategory, setSelectedCategory] = useState<any | null>(null);

  const [isDropdownOpen, setIsDropdownOpen] = useState(false);

  useEffect(() => {
    loadFighters();
  }, []);

  useEffect(() => {
    filterFighters();
  }, [searchQuery, filterType, selectedCategory, fighters]);

  const loadFighters = async () => {
    try {
      console.log('🔄 Cargando peleadores...');
      const response = await api.get('/peleadores-aprobados');
      setFighters(response.data.peleadores || []);
    } catch (error) {
      console.error('❌ Error:', error);
      setFighters([]);
    } finally {
      setLoading(false);
    }
  };

  const filterFighters = () => {
    let filtered = [...fighters];

    if (searchQuery.trim()) {
      const query = searchQuery.toLowerCase();
      filtered = filtered.filter((f) =>
        `${f.nombre} ${f.apellido}`.toLowerCase().includes(query) ||
        f.apodo?.toLowerCase().includes(query) ||
        f.club_nombre?.toLowerCase().includes(query)
      );
    }

    switch (filterType) {
      case 'destacados': filtered = filtered.filter((f) => f.total_promociones > 0); break;
      case 'masculino': filtered = filtered.filter((f) => f.genero?.toLowerCase() === 'masculino' || f.genero === 'M'); break;
      case 'femenino': filtered = filtered.filter((f) => f.genero?.toLowerCase() === 'femenino' || f.genero === 'F'); break;
    }

    if (selectedCategory) {
      filtered = filtered.filter(f => f.peso > selectedCategory.min && f.peso <= selectedCategory.max);
    }

    setFilteredFighters(filtered);
  };

  const getCountByCategory = (cat: any) => {
    return fighters.filter(f => f.peso > cat.min && f.peso <= cat.max).length;
  };

  const renderCompactFighter = ({ item }: { item: Peleador }) => {
    const genderIcon = item.genero?.toLowerCase() === 'femenino' ? 'woman' : 'man';
    const isActive = item.estado_inscripcion === 'aprobado';

    return (
      <TouchableOpacity
        style={styles.compactCard}
        activeOpacity={0.7}
        onPress={() => navigation.navigate('FighterDetail', { fighter: item })}
      >
        <View style={styles.cardContent}>
          
          {/* ✅ Lógica de Foto de Perfil */}
          <View style={styles.avatarContainer}>
            {item.foto_perfil ? (
                <Image 
                    source={{ uri: item.foto_perfil }} 
                    style={styles.avatarImage} 
                />
            ) : (
                <Ionicons name={genderIcon} size={24} color={COLORS.text.inverse} />
            )}
          </View>

          <View style={styles.infoContainer}>
            {item.apodo && <Text style={styles.apodoText}>"{item.apodo}"</Text>}
            <Text style={styles.nameText}>{item.nombre} {item.apellido}</Text>
            <Text style={styles.clubText}>
              {item.peso}kg • {item.club_nombre || 'Agente Libre'}
            </Text>
          </View>
          <View style={styles.statusContainer}>
            <View style={styles.statusBadge}>
              <View style={[styles.statusDot, { backgroundColor: isActive ? '#27ae60' : '#e74c3c' }]} />
              <Text style={styles.statusText}>{isActive ? 'ACTIVO' : 'INACTIVO'}</Text>
            </View>
            <Ionicons name="chevron-forward" size={20} color={COLORS.text.tertiary} style={{ marginTop: 8 }} />
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  const FilterPill = ({ type, label, icon }: any) => (
    <TouchableOpacity
      style={[styles.filterButton, filterType === type && styles.filterButtonActive]}
      onPress={() => setFilterType(type)}
    >
      {icon && <Ionicons name={icon} size={16} color={filterType === type ? COLORS.text.inverse : COLORS.text.tertiary} />}
      <Text style={[styles.filterButtonText, filterType === type && styles.filterButtonTextActive]}>{label}</Text>
    </TouchableOpacity>
  );

  if (loading) return <View style={styles.loadingContainer}><ActivityIndicator size="large" color={COLORS.primary} /></View>;

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

      <View style={styles.header}>
        <Text style={styles.headerTitle}>PELEADORES</Text>
        <Text style={styles.headerSubtitle}>{filteredFighters.length} encontrados</Text>
      </View>

      <View style={styles.searchContainer}>
        <Ionicons name="search" size={20} color={COLORS.text.tertiary} />
        <TextInput
          style={styles.searchInput}
          placeholder="Nombre, apodo, club..."
          placeholderTextColor={COLORS.text.tertiary}
          value={searchQuery}
          onChangeText={setSearchQuery}
        />
      </View>

      <View style={styles.filtersZone}>

        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.row1Scroll}>
          <FilterPill type="all" label="Todos" />
          <FilterPill type="destacados" label="Destacados" icon="star" />
          <FilterPill type="masculino" label="Hombres" icon="man" />
          <FilterPill type="femenino" label="Mujeres" icon="woman" />
        </ScrollView>

        <TouchableOpacity
          style={[styles.dropdownButton, (selectedCategory || isDropdownOpen) && styles.dropdownButtonActive]}
          onPress={() => setIsDropdownOpen(!isDropdownOpen)}
        >
          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
            <Ionicons
              name="scale-outline"
              size={18}
              color={(selectedCategory || isDropdownOpen) ? COLORS.text.inverse : COLORS.primary}
            />
            <Text style={[styles.dropdownButtonText, (selectedCategory || isDropdownOpen) && { color: COLORS.text.inverse }]}>
              {selectedCategory ? `${selectedCategory.label} (${selectedCategory.max}kg)` : "Seleccionar Categoría"}
            </Text>
          </View>

          {selectedCategory ? (
            <TouchableOpacity onPress={() => setSelectedCategory(null)}>
              <Ionicons name="close-circle" size={20} color={COLORS.text.inverse} />
            </TouchableOpacity>
          ) : (
            <Ionicons
              name={isDropdownOpen ? "chevron-up" : "chevron-down"}
              size={20}
              color={(isDropdownOpen) ? COLORS.text.inverse : COLORS.text.tertiary}
            />
          )}
        </TouchableOpacity>

        {isDropdownOpen && (
          <View style={styles.dropdownListContainer}>
            <ScrollView
              style={{ maxHeight: 250 }}
              showsVerticalScrollIndicator={false}
            >
              <TouchableOpacity
                style={styles.dropdownOption}
                onPress={() => { setSelectedCategory(null); setIsDropdownOpen(false); }}
              >
                <Text style={[styles.optionText, !selectedCategory && styles.optionTextSelected]}>
                  Todas las categorías
                </Text>
                {!selectedCategory && <Ionicons name="checkmark" size={20} color={COLORS.primary} />}
              </TouchableOpacity>

              {WEIGHT_CATEGORIES.map((cat) => {
                const count = getCountByCategory(cat);
                const isSelected = selectedCategory?.id === cat.id;
                return (
                  <TouchableOpacity
                    key={cat.id}
                    style={[styles.dropdownOption, isSelected && styles.dropdownOptionSelected]}
                    onPress={() => { setSelectedCategory(cat); setIsDropdownOpen(false); }}
                  >
                    <View>
                      <Text style={[styles.optionText, isSelected && styles.optionTextSelected]}>{cat.label}</Text>
                      <Text style={styles.optionSubText}>Hasta {cat.max}kg</Text>
                    </View>
                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                      <View style={styles.countBadge}>
                        <Text style={styles.countText}>{count}</Text>
                      </View>
                      {isSelected && <Ionicons name="checkmark" size={20} color={COLORS.primary} />}
                    </View>
                  </TouchableOpacity>
                )
              })}
            </ScrollView>
          </View>
        )}

      </View>

      <FlatList
        data={filteredFighters}
        renderItem={renderCompactFighter}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.list}
        showsVerticalScrollIndicator={false}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Ionicons name="search" size={64} color={COLORS.text.tertiary} />
            <Text style={styles.emptyText}>No hay resultados con estos filtros.</Text>
          </View>
        }
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: COLORS.background },
  loadingContainer: { flex: 1, backgroundColor: COLORS.background, justifyContent: 'center', alignItems: 'center' },

  header: { padding: SPACING.lg, borderBottomWidth: 1, borderBottomColor: COLORS.border.primary },
  headerTitle: { fontSize: TYPOGRAPHY.fontSize.xxxl, fontWeight: 'bold', color: COLORS.primary, letterSpacing: 1 },
  headerSubtitle: { fontSize: TYPOGRAPHY.fontSize.sm, color: COLORS.text.secondary },

  searchContainer: { flexDirection: 'row', alignItems: 'center', backgroundColor: COLORS.surface, margin: SPACING.md, paddingHorizontal: SPACING.md, borderRadius: 12, borderWidth: 1, borderColor: COLORS.border.primary, height: 50 },
  searchInput: { flex: 1, color: COLORS.text.primary, marginLeft: SPACING.sm, fontSize: TYPOGRAPHY.fontSize.md },

  filtersZone: { marginBottom: SPACING.sm },

  row1Scroll: { paddingHorizontal: SPACING.md, gap: SPACING.sm, paddingBottom: SPACING.sm },
  filterButton: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, paddingHorizontal: 16, borderRadius: 20, borderWidth: 1, borderColor: COLORS.border.primary, backgroundColor: COLORS.surface, gap: 6 },
  filterButtonActive: { backgroundColor: COLORS.primary, borderColor: COLORS.primary },
  filterButtonText: { fontSize: 12, color: COLORS.text.tertiary, fontWeight: '600' },
  filterButtonTextActive: { color: COLORS.text.inverse },

  dropdownButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginHorizontal: SPACING.md,
    paddingHorizontal: SPACING.md,
    height: 45,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.primary,
    backgroundColor: COLORS.surface,
  },
  dropdownButtonActive: {
    backgroundColor: COLORS.primary,
  },
  dropdownButtonText: {
    fontSize: 14,
    color: COLORS.primary,
    fontWeight: 'bold',
  },

  dropdownListContainer: {
    marginHorizontal: SPACING.md,
    marginTop: 4,
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    overflow: 'hidden',
  },
  dropdownOption: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: SPACING.md,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border.light,
  },
  dropdownOptionSelected: {
    backgroundColor: 'rgba(39, 174, 96, 0.1)',
  },
  optionText: { fontSize: 14, color: COLORS.text.primary },
  optionTextSelected: { color: COLORS.primary, fontWeight: 'bold' },
  optionSubText: { fontSize: 11, color: COLORS.text.tertiary },
  countBadge: { backgroundColor: COLORS.background, paddingHorizontal: 8, paddingVertical: 2, borderRadius: 10 },
  countText: { fontSize: 10, color: COLORS.text.secondary, fontWeight: 'bold' },

  list: { paddingHorizontal: SPACING.md, paddingBottom: SPACING.xl, paddingTop: SPACING.sm },
  compactCard: { backgroundColor: COLORS.surface, borderRadius: 12, marginBottom: SPACING.sm, borderWidth: 1, borderColor: COLORS.border.primary, padding: SPACING.md, ...createShadow("#000", 0, 2, 0.1, 4, 3) },
  cardContent: { flexDirection: 'row', alignItems: 'center' },
  
  avatarContainer: { 
    width: 45, 
    height: 45, 
    borderRadius: 22.5, 
    backgroundColor: COLORS.primary, 
    justifyContent: 'center', 
    alignItems: 'center', 
    marginRight: SPACING.md,
    overflow: 'hidden'
  },
  avatarImage: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover',
  },

  infoContainer: { flex: 1 },
  apodoText: { fontSize: 12, color: COLORS.primary, fontWeight: 'bold', marginBottom: 2 },
  nameText: { fontSize: 16, color: COLORS.text.primary, fontWeight: '700' },
  clubText: { fontSize: 12, color: COLORS.text.tertiary, marginTop: 2 },
  statusContainer: { alignItems: 'flex-end', justifyContent: 'center' },
  statusBadge: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  statusDot: { width: 6, height: 6, borderRadius: 3 },
  statusText: { fontSize: 10, fontWeight: 'bold', color: COLORS.text.tertiary },
  emptyContainer: { alignItems: 'center', marginTop: SPACING.xxl },
  emptyText: { color: COLORS.text.secondary, marginTop: SPACING.md, textAlign: 'center' },
});