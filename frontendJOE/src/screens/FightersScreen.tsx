import React, { useEffect, useState } from 'react';
import { 
  SafeAreaView, 
  ScrollView, 
  StyleSheet, 
  View, 
  Text,
  TouchableOpacity,
  TextInput,
  FlatList 
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import boxApi from '../api/boxApi';
import { FighterCard } from '../components/FighterCard';

interface Fighter {
  id: number;
  nombre: string;
  apodo: string;
  foto: string;
  club: string;
  estilo: 'fajador' | 'estilista';
  record: string;
  promociones: number;
}

type FilterType = 'todos' | 'populares' | 'recientes' | 'club';
type SortType = 'recientes' | 'populares' | 'alfabetico';

export default function FightersScreen({ navigation }: any) {
  const [fighters, setFighters] = useState<Fighter[]>([]);
  const [filteredFighters, setFilteredFighters] = useState<Fighter[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeFilter, setActiveFilter] = useState<FilterType>('todos');
  const [searchQuery, setSearchQuery] = useState('');
  const [sortBy, setSortBy] = useState<SortType>('recientes');

  useEffect(() => {
    loadFighters();
  }, [sortBy]);

  useEffect(() => {
    applyFilters();
  }, [fighters, activeFilter, searchQuery]);

  const loadFighters = async () => {
    try {
      const res = await boxApi.get(`/peleadores?ordenar=${sortBy}`);
      setFighters(res.data.peleadores || []);
      setLoading(false);
    } catch (err) {
      console.error('Error cargando peleadores:', err);
      setLoading(false);
    }
  };

  const applyFilters = () => {
    let filtered = [...fighters];

    // Filtrar por búsqueda
    if (searchQuery) {
      filtered = filtered.filter(f => 
        f.nombre.toLowerCase().includes(searchQuery.toLowerCase()) ||
        f.apodo.toLowerCase().includes(searchQuery.toLowerCase()) ||
        f.club.toLowerCase().includes(searchQuery.toLowerCase())
      );
    }

    // Filtrar por tipo
    if (activeFilter === 'populares') {
      filtered = filtered.filter(f => f.promociones >= 10);
    }

    setFilteredFighters(filtered);
  };

  const handleFighterPress = (fighter: Fighter) => {
    // Navegar a detalle del peleador
    // navigation.navigate('FighterDetail', { fighterId: fighter.id });
    console.log('Ver detalle:', fighter.nombre);
  };

  return (
    <SafeAreaView style={styles.container}>
      {/* Header con búsqueda */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>PELEADORES</Text>
        <View style={styles.searchBar}>
          <Ionicons name="search" size={20} color="#888" />
          <TextInput
            style={styles.searchInput}
            placeholder="Buscar peleador, club..."
            placeholderTextColor="#666"
            value={searchQuery}
            onChangeText={setSearchQuery}
          />
          {searchQuery.length > 0 && (
            <TouchableOpacity onPress={() => setSearchQuery('')}>
              <Ionicons name="close-circle" size={20} color="#888" />
            </TouchableOpacity>
          )}
        </View>
      </View>

      {/* Filtros */}
      <ScrollView 
        horizontal 
        showsHorizontalScrollIndicator={false}
        style={styles.filtersContainer}
        contentContainerStyle={styles.filtersContent}
      >
        <FilterButton
          icon="list"
          label="Todos"
          active={activeFilter === 'todos'}
          onPress={() => setActiveFilter('todos')}
        />
        <FilterButton
          icon="flame"
          label="Populares"
          active={activeFilter === 'populares'}
          onPress={() => setActiveFilter('populares')}
        />
        <FilterButton
          icon="time"
          label="Recientes"
          active={activeFilter === 'recientes'}
          onPress={() => setActiveFilter('recientes')}
        />
        <FilterButton
          icon="business"
          label="Por Club"
          active={activeFilter === 'club'}
          onPress={() => setActiveFilter('club')}
        />
      </ScrollView>

      {/* Ordenar por */}
      <View style={styles.sortContainer}>
        <Text style={styles.sortLabel}>Ordenar:</Text>
        <TouchableOpacity 
          style={[styles.sortButton, sortBy === 'populares' && styles.activeSortButton]}
          onPress={() => setSortBy('populares')}
        >
          <Text style={[styles.sortText, sortBy === 'populares' && styles.activeSortText]}>
            🔥 Más populares
          </Text>
        </TouchableOpacity>
        <TouchableOpacity 
          style={[styles.sortButton, sortBy === 'recientes' && styles.activeSortButton]}
          onPress={() => setSortBy('recientes')}
        >
          <Text style={[styles.sortText, sortBy === 'recientes' && styles.activeSortText]}>
            ⏱️ Recientes
          </Text>
        </TouchableOpacity>
      </View>

      {/* Contador de resultados */}
      <View style={styles.resultsCounter}>
        <Text style={styles.resultsText}>
          {filteredFighters.length} {filteredFighters.length === 1 ? 'peleador' : 'peleadores'} encontrado{filteredFighters.length !== 1 ? 's' : ''}
        </Text>
      </View>

      {/* Lista de peleadores */}
      <FlatList
        data={filteredFighters}
        keyExtractor={(item) => item.id.toString()}
        renderItem={({ item }) => (
          <FighterCard 
            fighter={item} 
            onPress={() => handleFighterPress(item)}
          />
        )}
        contentContainerStyle={styles.listContent}
        showsVerticalScrollIndicator={false}
        ListEmptyComponent={
          <View style={styles.emptyState}>
            <Ionicons name="search" size={64} color="#333" />
            <Text style={styles.emptyText}>No se encontraron peleadores</Text>
            <Text style={styles.emptySubtext}>
              Intenta con otro filtro o búsqueda
            </Text>
          </View>
        }
      />

      {/* Botón flotante: Quiero Pelear */}
      <TouchableOpacity 
        style={styles.floatingButton}
        onPress={() => navigation.navigate('Register')}
      >
        <Ionicons name="add" size={28} color="#000" />
        <Text style={styles.floatingButtonText}>¡QUIERO PELEAR!</Text>
      </TouchableOpacity>
    </SafeAreaView>
  );
}

// Componente auxiliar para botones de filtro
const FilterButton = ({ 
  icon, 
  label, 
  active, 
  onPress 
}: { 
  icon: any; 
  label: string; 
  active: boolean; 
  onPress: () => void;
}) => (
  <TouchableOpacity 
    style={[styles.filterButton, active && styles.activeFilterButton]}
    onPress={onPress}
  >
    <Ionicons 
      name={icon} 
      size={18} 
      color={active ? '#000' : '#888'} 
    />
    <Text style={[styles.filterText, active && styles.activeFilterText]}>
      {label}
    </Text>
  </TouchableOpacity>
);

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  header: {
    padding: 15,
    paddingTop: 20,
  },
  headerTitle: {
    color: '#FFD700',
    fontSize: 24,
    fontWeight: 'bold',
    letterSpacing: 2,
    marginBottom: 15,
    textAlign: 'center',
  },
  searchBar: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#1a1a1a',
    borderRadius: 10,
    paddingHorizontal: 15,
    paddingVertical: 12,
    gap: 10,
    borderWidth: 1,
    borderColor: '#333',
  },
  searchInput: {
    flex: 1,
    color: '#fff',
    fontSize: 14,
  },
  filtersContainer: {
    maxHeight: 50,
  },
  filtersContent: {
    paddingHorizontal: 15,
    gap: 10,
  },
  filterButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#1a1a1a',
    paddingHorizontal: 15,
    paddingVertical: 10,
    borderRadius: 20,
    gap: 6,
    borderWidth: 1,
    borderColor: '#333',
  },
  activeFilterButton: {
    backgroundColor: '#FFD700',
    borderColor: '#FFD700',
  },
  filterText: {
    color: '#888',
    fontSize: 13,
    fontWeight: '600',
  },
  activeFilterText: {
    color: '#000',
  },
  sortContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingVertical: 12,
    gap: 10,
  },
  sortLabel: {
    color: '#888',
    fontSize: 13,
    fontWeight: '600',
  },
  sortButton: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 15,
    backgroundColor: '#1a1a1a',
    borderWidth: 1,
    borderColor: '#333',
  },
  activeSortButton: {
    backgroundColor: '#FFD700',
    borderColor: '#FFD700',
  },
  sortText: {
    color: '#888',
    fontSize: 12,
  },
  activeSortText: {
    color: '#000',
    fontWeight: 'bold',
  },
  resultsCounter: {
    paddingHorizontal: 15,
    paddingVertical: 8,
  },
  resultsText: {
    color: '#888',
    fontSize: 12,
  },
  listContent: {
    paddingBottom: 120,
  },
  emptyState: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 60,
  },
  emptyText: {
    color: '#666',
    fontSize: 16,
    fontWeight: '600',
    marginTop: 15,
  },
  emptySubtext: {
    color: '#444',
    fontSize: 13,
    marginTop: 8,
  },
  floatingButton: {
    position: 'absolute',
    bottom: 90,
    right: 20,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFD700',
    paddingHorizontal: 20,
    paddingVertical: 15,
    borderRadius: 30,
    gap: 8,
    shadowColor: '#FFD700',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.6,
    shadowRadius: 8,
    elevation: 8,
  },
  floatingButtonText: {
    color: '#000',
    fontSize: 14,
    fontWeight: 'bold',
  },
});