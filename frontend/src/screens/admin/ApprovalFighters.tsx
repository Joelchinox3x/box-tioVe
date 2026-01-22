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
  Platform,
} from 'react-native';
import { AdminService } from '../../services/AdminService';

interface Peleador {
  id: number;
  nombre: string;
  email: string;
  telefono: string;
  apodo: string;
  documento_identidad: string;
  peso_actual: number;
  altura: number;
  fecha_nacimiento: string;
  club_nombre: string | null;
  victorias: number;
  derrotas: number;
  empates: number;
  estado_inscripcion: string;
}

export default function ApprovalFighters() {
  const [peleadores, setPeleadores] = useState<Peleador[]>([]);
  const [loading, setLoading] = useState(true);
  const [processingId, setProcessingId] = useState<number | null>(null);
  const [notasMap, setNotasMap] = useState<Record<number, string>>({});

  useEffect(() => {
    loadPeleadores();
  }, []);

  const loadPeleadores = async () => {
    try {
      setLoading(true);
      const data = await AdminService.getPeleadoresPendientes();
      setPeleadores(data.peleadores || []);
    } catch (error) {
      console.error('Error loading fighters:', error);
      Alert.alert('Error', 'No se pudieron cargar los peleadores pendientes');
      setPeleadores([]);
    } finally {
      setLoading(false);
    }
  };

  const handleApproval = async (peleadorId: number, estado_inscripcion: 'aprobado' | 'rechazado') => {

    const notas = notasMap[peleadorId] || '';

    const confirmMessage =
      estado_inscripcion === 'aprobado'
        ? '¿Estás seguro de aprobar este peleador?'
        : '¿Estás seguro de rechazar este peleador?';

    // Función auxiliar que hace la llamada a la API (la lógica que ya tenías)
    const ejecutarCambio = async () => {
      console.log('✅ Usuario confirmó la acción');
      try {
        setProcessingId(peleadorId);
        console.log('🔵 Cambiando estado_inscripcion de peleador:', { peleadorId, estado_inscripcion, notas });

        const response = await AdminService.cambiarEstadoPeleador(peleadorId, estado_inscripcion, notas);
        console.log('✅ Respuesta del servidor:', response);

        // Usamos Alert simple aquí porque es solo informativo y funciona bien en ambos
        if (Platform.OS === 'web') {
           window.alert(`Peleador ${estado_inscripcion === 'aprobado' ? 'aprobado' : 'rechazado'} correctamente`);
        } else {
           Alert.alert('Éxito', `Peleador ${estado_inscripcion === 'aprobado' ? 'aprobado' : 'rechazado'} correctamente`);
        }

        // Recargar lista
        await loadPeleadores();
      } catch (error: any) {
        console.error('❌ Error completo:', error);
        const errorMessage = error.response?.data?.message || 'No se pudo procesar la solicitud';
        
        if (Platform.OS === 'web') {
            window.alert('Error: ' + errorMessage);
        } else {
            Alert.alert('Error', errorMessage);
        }
      } finally {
        setProcessingId(null);
      }
    };

    // LÓGICA DIFERENCIADA PARA WEB Y MÓVIL
    if (Platform.OS === 'web') {
      const confirmado = window.confirm(confirmMessage);
      if (confirmado) {
        ejecutarCambio();
      } else {
        console.log('❌ Usuario canceló la acción (Web)');
      }
    } else {
      Alert.alert(
        'Confirmar acción',
        confirmMessage,
        [
          {
            text: 'Cancelar',
            style: 'cancel',
            onPress: () => console.log('❌ Usuario canceló la acción')
          },
          {
            text: 'Confirmar',
            style: estado_inscripcion === 'aprobado' ? 'default' : 'destructive',
            onPress: ejecutarCambio,
          },
        ],
        { cancelable: true }
      );
    }
  };

  const updateNotas = (peleadorId: number, text: string) => {
    setNotasMap((prev) => ({
      ...prev,
      [peleadorId]: text,
    }));
  };

  const calcularEdad = (fechaNacimiento: string) => {
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
      edad--;
    }
    return edad;
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#e74c3c" />
        <Text style={styles.loadingText}>Cargando peleadores...</Text>
      </View>
    );
  }

  if (peleadores.length === 0) {
    return (
      <View style={styles.centerContainer}>
        <Text style={styles.emptyIcon}>✅</Text>
        <Text style={styles.emptyText}>No hay peleadores pendientes de aprobación</Text>
        <TouchableOpacity style={styles.refreshButton} onPress={loadPeleadores}>
          <Text style={styles.refreshButtonText}>Recargar</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Peleadores Pendientes de Aprobación</Text>
        <Text style={styles.subtitle}>{peleadores.length} solicitudes pendientes</Text>
      </View>

      {peleadores.map((peleador) => (
        <View key={peleador.id} style={styles.card}>
          <View style={styles.cardHeader}>
            <View>
              <Text style={styles.fighterName}>{peleador.nombre}</Text>
              {peleador.apodo && <Text style={styles.fighterNickname}>"{peleador.apodo}"</Text>}
            </View>
            <View style={styles.badge}>
              <Text style={styles.badgeText}>PENDIENTE</Text>
            </View>
          </View>

          <View style={styles.infoGrid}>
            <View style={styles.infoItem}>
              <Text style={styles.infoLabel}>DNI</Text>
              <Text style={styles.infoValue}>{peleador.documento_identidad}</Text>
            </View>

            <View style={styles.infoItem}>
              <Text style={styles.infoLabel}>Edad</Text>
              <Text style={styles.infoValue}>{calcularEdad(peleador.fecha_nacimiento)} años</Text>
            </View>

            <View style={styles.infoItem}>
              <Text style={styles.infoLabel}>Peso</Text>
              <Text style={styles.infoValue}>{peleador.peso_actual} kg</Text>
            </View>

            <View style={styles.infoItem}>
              <Text style={styles.infoLabel}>Altura</Text>
              <Text style={styles.infoValue}>{peleador.altura} m</Text>
            </View>
          </View>

          <View style={styles.recordContainer}>
            <Text style={styles.recordTitle}>Récord:</Text>
            <Text style={styles.recordText}>
              {peleador.victorias}W - {peleador.derrotas}L - {peleador.empates}D
            </Text>
          </View>

          <View style={styles.contactInfo}>
            <Text style={styles.contactText}>📧 {peleador.email}</Text>
            <Text style={styles.contactText}>📱 {peleador.telefono}</Text>
            {peleador.club_nombre && <Text style={styles.contactText}>🏛️ {peleador.club_nombre}</Text>}
          </View>

          <TextInput
            style={styles.notasInput}
            placeholder="Notas del administrador (opcional)..."
            placeholderTextColor="#666"
            value={notasMap[peleador.id] || ''}
            onChangeText={(text) => updateNotas(peleador.id, text)}
            multiline
          />

          <View style={styles.actions}>
            <TouchableOpacity
              style={[styles.actionButton, styles.rejectButton]}
              onPress={() => {
                handleApproval(peleador.id, 'rechazado');
              }}
              disabled={processingId === peleador.id}
            >
              {processingId === peleador.id ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <>
                  <Text style={styles.actionButtonIcon}>❌</Text>
                  <Text style={styles.actionButtonText}>Rechazar</Text>
                </>
              )}
            </TouchableOpacity>

            <TouchableOpacity
              style={[styles.actionButton, styles.approveButton]}
              onPress={() => {
                console.log('🟢 Botón APROBAR presionado para peleador:', peleador.id);
                handleApproval(peleador.id, 'aprobado');
              }}
              disabled={processingId === peleador.id}
            >
              {processingId === peleador.id ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <>
                  <Text style={styles.actionButtonIcon}>✅</Text>
                  <Text style={styles.actionButtonText}>Aprobar</Text>
                </>
              )}
            </TouchableOpacity>
          </View>
        </View>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#1a1a1a',
    padding: 20,
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
  emptyIcon: {
    fontSize: 60,
    marginBottom: 20,
  },
  emptyText: {
    color: '#fff',
    fontSize: 18,
    textAlign: 'center',
  },
  refreshButton: {
    marginTop: 20,
    backgroundColor: '#e74c3c',
    paddingHorizontal: 30,
    paddingVertical: 12,
    borderRadius: 10,
  },
  refreshButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  header: {
    marginBottom: 20,
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
    alignItems: 'flex-start',
    marginBottom: 15,
  },
  fighterName: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
  },
  fighterNickname: {
    fontSize: 14,
    color: '#e74c3c',
    fontStyle: 'italic',
    marginTop: 2,
  },
  badge: {
    backgroundColor: '#f39c12',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 8,
  },
  badgeText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: 'bold',
  },
  infoGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 15,
  },
  infoItem: {
    width: '50%',
    marginBottom: 10,
  },
  infoLabel: {
    fontSize: 12,
    color: '#999',
    marginBottom: 2,
  },
  infoValue: {
    fontSize: 16,
    color: '#fff',
    fontWeight: '600',
  },
  recordContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 15,
  },
  recordTitle: {
    fontSize: 14,
    color: '#999',
    marginRight: 10,
  },
  recordText: {
    fontSize: 16,
    color: '#27ae60',
    fontWeight: 'bold',
  },
  contactInfo: {
    marginBottom: 15,
  },
  contactText: {
    fontSize: 14,
    color: '#ccc',
    marginBottom: 5,
  },
  notasInput: {
    backgroundColor: '#1a1a1a',
    borderRadius: 10,
    padding: 12,
    color: '#fff',
    fontSize: 14,
    marginBottom: 15,
    minHeight: 60,
    borderWidth: 1,
    borderColor: '#444',
  },
  actions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actionButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 10,
    marginHorizontal: 5,
  },
  approveButton: {
    backgroundColor: '#27ae60',
  },
  rejectButton: {
    backgroundColor: '#e74c3c',
  },
  actionButtonIcon: {
    fontSize: 18,
    marginRight: 8,
  },
  actionButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});