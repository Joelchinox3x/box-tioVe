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
  Modal,
} from 'react-native';
import { AdminService } from '../../services/AdminService';

interface Club {
  id: number;
  nombre: string;
  direccion: string | null;
  telefono: string | null;
  email: string | null;
  descripcion: string | null;
  total_managers: number;
  total_peleadores: number;
  activo: boolean;
}

export default function ClubsManagement() {
  const [clubs, setClubs] = useState<Club[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [creating, setCreating] = useState(false);

  // Formulario de nuevo club
  const [formData, setFormData] = useState({
    nombre: '',
    direccion: '',
    telefono: '',
    email: '',
    descripcion: '',
  });

  useEffect(() => {
    loadClubs();
  }, []);

  const loadClubs = async () => {
    try {
      setLoading(true);
      const data = await AdminService.getAllClubs();
      setClubs(data.clubs || []);
    } catch (error) {
      console.error('Error loading clubs:', error);
      Alert.alert('Error', 'No se pudieron cargar los clubs');
      setClubs([]);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateClub = async () => {
    // Validación
    if (!formData.nombre.trim()) {
      Alert.alert('Error', 'El nombre del club es obligatorio');
      return;
    }

    try {
      setCreating(true);
      await AdminService.crearClub(formData);

      Alert.alert('Éxito', 'Club creado correctamente');

      // Limpiar formulario
      setFormData({
        nombre: '',
        direccion: '',
        telefono: '',
        email: '',
        descripcion: '',
      });

      setShowModal(false);
      await loadClubs();
    } catch (error) {
      Alert.alert('Error', 'No se pudo crear el club. Verifica que el nombre no esté duplicado.');
    } finally {
      setCreating(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#e74c3c" />
        <Text style={styles.loadingText}>Cargando clubs...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.title}>Gestión de Clubs</Text>
          <Text style={styles.subtitle}>{clubs.length} clubs registrados</Text>
        </View>
        <TouchableOpacity style={styles.addButton} onPress={() => setShowModal(true)}>
          <Text style={styles.addButtonText}>+ Nuevo Club</Text>
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.list}>
        {clubs.map((club) => (
          <View key={club.id} style={styles.card}>
            <View style={styles.cardHeader}>
              <Text style={styles.clubName}>{club.nombre}</Text>
              <View style={styles.badge}>
                <Text style={styles.badgeText}>ACTIVO</Text>
              </View>
            </View>

            {club.descripcion && (
              <Text style={styles.clubDescription}>{club.descripcion}</Text>
            )}

            <View style={styles.statsRow}>
              <View style={styles.statItem}>
                <Text style={styles.statIcon}>👤</Text>
                <Text style={styles.statLabel}>{club.total_managers} Manager(s)</Text>
              </View>
              <View style={styles.statItem}>
                <Text style={styles.statIcon}>👊</Text>
                <Text style={styles.statLabel}>{club.total_peleadores} Peleador(es)</Text>
              </View>
            </View>

            {(club.direccion || club.telefono || club.email) && (
              <View style={styles.contactInfo}>
                {club.direccion && (
                  <Text style={styles.contactText}>📍 {club.direccion}</Text>
                )}
                {club.telefono && (
                  <Text style={styles.contactText}>📱 {club.telefono}</Text>
                )}
                {club.email && (
                  <Text style={styles.contactText}>📧 {club.email}</Text>
                )}
              </View>
            )}
          </View>
        ))}
      </ScrollView>

      {/* Modal para crear club */}
      <Modal
        visible={showModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Crear Nuevo Club</Text>
              <TouchableOpacity onPress={() => setShowModal(false)}>
                <Text style={styles.closeButton}>✕</Text>
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.form}>
              <View style={styles.formGroup}>
                <Text style={styles.label}>Nombre del Club *</Text>
                <TextInput
                  style={styles.input}
                  placeholder="Ej: Gimnasio El Campeón"
                  placeholderTextColor="#666"
                  value={formData.nombre}
                  onChangeText={(text) => setFormData({ ...formData, nombre: text })}
                />
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>Dirección</Text>
                <TextInput
                  style={styles.input}
                  placeholder="Ej: Av. Rivadavia 1234, CABA"
                  placeholderTextColor="#666"
                  value={formData.direccion}
                  onChangeText={(text) => setFormData({ ...formData, direccion: text })}
                />
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>Teléfono</Text>
                <TextInput
                  style={styles.input}
                  placeholder="Ej: +54 11 1234-5678"
                  placeholderTextColor="#666"
                  value={formData.telefono}
                  onChangeText={(text) => setFormData({ ...formData, telefono: text })}
                  keyboardType="phone-pad"
                />
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>Email</Text>
                <TextInput
                  style={styles.input}
                  placeholder="Ej: contacto@club.com"
                  placeholderTextColor="#666"
                  value={formData.email}
                  onChangeText={(text) => setFormData({ ...formData, email: text })}
                  keyboardType="email-address"
                  autoCapitalize="none"
                />
              </View>

              <View style={styles.formGroup}>
                <Text style={styles.label}>Descripción</Text>
                <TextInput
                  style={[styles.input, styles.textArea]}
                  placeholder="Descripción del club..."
                  placeholderTextColor="#666"
                  value={formData.descripcion}
                  onChangeText={(text) => setFormData({ ...formData, descripcion: text })}
                  multiline
                  numberOfLines={4}
                />
              </View>
            </ScrollView>

            <View style={styles.modalActions}>
              <TouchableOpacity
                style={[styles.modalButton, styles.cancelButton]}
                onPress={() => setShowModal(false)}
              >
                <Text style={styles.buttonText}>Cancelar</Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.modalButton, styles.createButton]}
                onPress={handleCreateClub}
                disabled={creating}
              >
                {creating ? (
                  <ActivityIndicator color="#fff" />
                ) : (
                  <Text style={styles.buttonText}>Crear Club</Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#1a1a1a',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#1a1a1a',
  },
  loadingText: {
    color: '#fff',
    marginTop: 10,
    fontSize: 16,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#333',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
  },
  subtitle: {
    fontSize: 14,
    color: '#999',
    marginTop: 2,
  },
  addButton: {
    backgroundColor: '#e74c3c',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 10,
  },
  addButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  list: {
    flex: 1,
    padding: 20,
  },
  card: {
    backgroundColor: '#2c2c2c',
    borderRadius: 15,
    padding: 20,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: '#444',
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  clubName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#fff',
    flex: 1,
  },
  badge: {
    backgroundColor: '#27ae60',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 6,
  },
  badgeText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: 'bold',
  },
  clubDescription: {
    fontSize: 14,
    color: '#ccc',
    marginBottom: 15,
    lineHeight: 20,
  },
  statsRow: {
    flexDirection: 'row',
    marginBottom: 15,
    gap: 20,
  },
  statItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
  },
  statIcon: {
    fontSize: 16,
  },
  statLabel: {
    fontSize: 14,
    color: '#999',
  },
  contactInfo: {
    borderTopWidth: 1,
    borderTopColor: '#444',
    paddingTop: 15,
  },
  contactText: {
    fontSize: 13,
    color: '#aaa',
    marginBottom: 5,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.8)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: '#2c2c2c',
    borderRadius: 20,
    width: '90%',
    maxHeight: '80%',
    padding: 20,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
  },
  modalTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#fff',
  },
  closeButton: {
    fontSize: 28,
    color: '#999',
    fontWeight: 'bold',
  },
  form: {
    marginBottom: 20,
  },
  formGroup: {
    marginBottom: 15,
  },
  label: {
    fontSize: 14,
    color: '#ccc',
    marginBottom: 8,
    fontWeight: '600',
  },
  input: {
    backgroundColor: '#1a1a1a',
    borderRadius: 10,
    padding: 12,
    color: '#fff',
    fontSize: 16,
    borderWidth: 1,
    borderColor: '#444',
  },
  textArea: {
    minHeight: 80,
    textAlignVertical: 'top',
  },
  modalActions: {
    flexDirection: 'row',
    gap: 10,
  },
  modalButton: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 10,
    alignItems: 'center',
  },
  cancelButton: {
    backgroundColor: '#555',
  },
  createButton: {
    backgroundColor: '#e74c3c',
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});