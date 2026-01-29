import React, { useState, useEffect } from 'react';
import {
  View,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  TextInput,
} from 'react-native';
import { AdminService } from '../../services/AdminService';

interface Club {
  id: number;
  nombre: string;
}

interface Usuario {
  id: number;
  nombre: string;
  email: string;
  telefono: string;
  dni: string;
  tipo_nombre: string;
  club_nombre: string | null;
}

export default function AssignOwners() {
  const [clubs, setClubs] = useState<Club[]>([]);
  const [dni, setDni] = useState('');
  const [searching, setSearching] = useState(false);
  const [usuario, setUsuario] = useState<Usuario | null>(null);
  const [selectedClub, setSelectedClub] = useState<number | null>(null);
  const [assigning, setAssigning] = useState(false);

  useEffect(() => {
    loadClubs();
  }, []);

  const loadClubs = async () => {
    try {
      const data = await AdminService.getAllClubs();
      setClubs(data.clubs);
    } catch (error) {
      Alert.alert('Error', 'No se pudieron cargar los clubs');
    }
  };

  const handleSearch = async () => {
    if (!dni.trim()) {
      Alert.alert('Error', 'Ingresa un DNI para buscar');
      return;
    }

    try {
      setSearching(true);
      setUsuario(null);
      const data = await AdminService.buscarUsuarioPorDNI(dni);
      setUsuario(data.usuario);
    } catch (error) {
      Alert.alert('No encontrado', 'No se encontró ningún usuario con ese DNI');
    } finally {
      setSearching(false);
    }
  };

  const handleAssignOwner = async () => {
    if (!usuario || !selectedClub) {
      Alert.alert('Error', 'Selecciona un club');
      return;
    }

    const club = clubs.find((c) => c.id === selectedClub);

    Alert.alert(
      'Confirmar asignación',
      `¿Asignar a ${usuario.nombre} como dueño del club "${club?.nombre}"?`,
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Confirmar',
          onPress: async () => {
            try {
              setAssigning(true);
              await AdminService.asignarDuenioClub(usuario.id, selectedClub);

              Alert.alert('Éxito', 'Dueño asignado correctamente al club');

              // Limpiar formulario
              setDni('');
              setUsuario(null);
              setSelectedClub(null);
            } catch (error) {
              Alert.alert('Error', 'No se pudo asignar el dueño');
            } finally {
              setAssigning(false);
            }
          },
        },
      ]
    );
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={{ paddingBottom: 100 }}>
      <View style={styles.header}>
        <Text style={styles.title}>Asignar Dueños a Clubs</Text>
        <Text style={styles.subtitle}>
          Busca un peleador por DNI y asígnalo como manager de un club
        </Text>
      </View>

      {/* Búsqueda por DNI */}
      <View style={styles.searchSection}>
        <Text style={styles.sectionTitle}>1. Buscar Usuario por DNI</Text>
        <View style={styles.searchRow}>
          <TextInput
            style={styles.searchInput}
            placeholder="Ingresa el DNI del usuario"
            placeholderTextColor="#666"
            value={dni}
            onChangeText={setDni}
            keyboardType="numeric"
          />
          <TouchableOpacity
            style={styles.searchButton}
            onPress={handleSearch}
            disabled={searching}
          >
            {searching ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <Text style={styles.searchButtonText}>🔍 Buscar</Text>
            )}
          </TouchableOpacity>
        </View>
      </View>

      {/* Resultado de búsqueda */}
      {usuario && (
        <View style={styles.resultSection}>
          <Text style={styles.sectionTitle}>2. Usuario Encontrado</Text>
          <View style={styles.userCard}>
            <View style={styles.userHeader}>
              <Text style={styles.userName}>{usuario.nombre}</Text>
              <View style={[styles.badge, getRoleBadgeColor(usuario.tipo_nombre)]}>
                <Text style={styles.badgeText}>{usuario.tipo_nombre.toUpperCase()}</Text>
              </View>
            </View>

            <View style={styles.userInfo}>
              <View style={styles.infoRow}>
                <Text style={styles.infoLabel}>DNI:</Text>
                <Text style={styles.infoValue}>{usuario.dni}</Text>
              </View>
              <View style={styles.infoRow}>
                <Text style={styles.infoLabel}>Email:</Text>
                <Text style={styles.infoValue}>{usuario.email}</Text>
              </View>
              <View style={styles.infoRow}>
                <Text style={styles.infoLabel}>Teléfono:</Text>
                <Text style={styles.infoValue}>{usuario.telefono}</Text>
              </View>
              {usuario.club_nombre && (
                <View style={styles.infoRow}>
                  <Text style={styles.infoLabel}>Club actual:</Text>
                  <Text style={styles.infoValue}>{usuario.club_nombre}</Text>
                </View>
              )}
            </View>

            {usuario.tipo_nombre === 'manager_club' && (
              <View style={styles.warningBox}>
                <Text style={styles.warningIcon}>⚠️</Text>
                <Text style={styles.warningText}>
                  Este usuario ya es manager de un club. Al asignarlo a otro club, se cambiará
                  su afiliación.
                </Text>
              </View>
            )}
          </View>
        </View>
      )}

      {/* Selección de club */}
      {usuario && (
        <View style={styles.clubSection}>
          <Text style={styles.sectionTitle}>3. Seleccionar Club</Text>
          <View style={styles.clubsGrid}>
            {clubs.map((club) => (
              <TouchableOpacity
                key={club.id}
                style={[
                  styles.clubOption,
                  selectedClub === club.id && styles.clubOptionSelected,
                ]}
                onPress={() => setSelectedClub(club.id)}
              >
                <Text
                  style={[
                    styles.clubOptionText,
                    selectedClub === club.id && styles.clubOptionTextSelected,
                  ]}
                >
                  {club.nombre}
                </Text>
                {selectedClub === club.id && (
                  <Text style={styles.checkIcon}>✓</Text>
                )}
              </TouchableOpacity>
            ))}
          </View>
        </View>
      )}

      {/* Botón de asignación */}
      {usuario && selectedClub && (
        <View style={styles.actionSection}>
          <TouchableOpacity
            style={styles.assignButton}
            onPress={handleAssignOwner}
            disabled={assigning}
          >
            {assigning ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <>
                <Text style={styles.assignButtonIcon}>👤</Text>
                <Text style={styles.assignButtonText}>Asignar como Dueño</Text>
              </>
            )}
          </TouchableOpacity>
        </View>
      )}

      {/* Instrucciones */}
      {!usuario && (
        <View style={styles.instructionsBox}>
          <Text style={styles.instructionsTitle}>📋 Instrucciones</Text>
          <Text style={styles.instructionsText}>
            1. Ingresa el DNI del peleador que quieres hacer manager{'\n'}
            2. Verifica que sea la persona correcta{'\n'}
            3. Selecciona el club que administrará{'\n'}
            4. Confirma la asignación
          </Text>
          <Text style={styles.instructionsNote}>
            💡 Nota: Solo los peleadores registrados pueden ser asignados como managers.
          </Text>
        </View>
      )}
    </ScrollView>
  );
}

function getRoleBadgeColor(role: string) {
  switch (role) {
    case 'admin':
      return { backgroundColor: '#e74c3c' };
    case 'peleador':
      return { backgroundColor: '#3498db' };
    case 'manager_club':
      return { backgroundColor: '#f39c12' };
    default:
      return { backgroundColor: '#95a5a6' };
  }
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#1a1a1a',
    padding: 20,
  },
  header: {
    marginBottom: 30,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 5,
  },
  subtitle: {
    fontSize: 14,
    color: '#999',
    lineHeight: 20,
  },
  searchSection: {
    marginBottom: 25,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#e74c3c',
    marginBottom: 15,
  },
  searchRow: {
    flexDirection: 'row',
    gap: 10,
  },
  searchInput: {
    flex: 1,
    backgroundColor: '#2c2c2c',
    borderRadius: 10,
    padding: 15,
    color: '#fff',
    fontSize: 16,
    borderWidth: 1,
    borderColor: '#444',
  },
  searchButton: {
    backgroundColor: '#e74c3c',
    paddingHorizontal: 20,
    paddingVertical: 15,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    minWidth: 100,
  },
  searchButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  resultSection: {
    marginBottom: 25,
  },
  userCard: {
    backgroundColor: '#2c2c2c',
    borderRadius: 15,
    padding: 20,
    borderWidth: 1,
    borderColor: '#444',
  },
  userHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
    paddingBottom: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#444',
  },
  userName: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
  },
  badge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 8,
  },
  badgeText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: 'bold',
  },
  userInfo: {
    gap: 10,
  },
  infoRow: {
    flexDirection: 'row',
  },
  infoLabel: {
    fontSize: 14,
    color: '#999',
    width: 100,
  },
  infoValue: {
    fontSize: 14,
    color: '#fff',
    flex: 1,
    fontWeight: '500',
  },
  warningBox: {
    flexDirection: 'row',
    backgroundColor: '#f39c1220',
    borderRadius: 10,
    padding: 12,
    marginTop: 15,
    borderWidth: 1,
    borderColor: '#f39c12',
    gap: 10,
  },
  warningIcon: {
    fontSize: 20,
  },
  warningText: {
    flex: 1,
    fontSize: 13,
    color: '#f39c12',
    lineHeight: 18,
  },
  clubSection: {
    marginBottom: 25,
  },
  clubsGrid: {
    gap: 10,
  },
  clubOption: {
    backgroundColor: '#2c2c2c',
    borderRadius: 10,
    padding: 15,
    borderWidth: 2,
    borderColor: '#444',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  clubOptionSelected: {
    backgroundColor: '#e74c3c20',
    borderColor: '#e74c3c',
  },
  clubOptionText: {
    fontSize: 16,
    color: '#ccc',
    fontWeight: '500',
  },
  clubOptionTextSelected: {
    color: '#fff',
    fontWeight: 'bold',
  },
  checkIcon: {
    fontSize: 20,
    color: '#e74c3c',
  },
  actionSection: {
    marginBottom: 30,
  },
  assignButton: {
    backgroundColor: '#27ae60',
    padding: 18,
    borderRadius: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
  },
  assignButtonIcon: {
    fontSize: 24,
  },
  assignButtonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  instructionsBox: {
    backgroundColor: '#2c2c2c',
    borderRadius: 15,
    padding: 20,
    borderWidth: 1,
    borderColor: '#444',
  },
  instructionsTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 15,
  },
  instructionsText: {
    fontSize: 14,
    color: '#ccc',
    lineHeight: 24,
    marginBottom: 15,
  },
  instructionsNote: {
    fontSize: 13,
    color: '#999',
    fontStyle: 'italic',
    lineHeight: 20,
  },
});