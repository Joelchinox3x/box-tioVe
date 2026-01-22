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

interface Inscripcion {
  id: number;
  peleador_id: number;
  evento_id: number;
  estado_pago: string;
  monto_pagado: string;
  fecha_inscripcion: string;
  fecha_pago: string | null;
  metodo_pago: string | null;
  comprobante_pago: string | null;
  notas_admin: string | null;
  peleador_nombre: string;
  peleador_email: string;
  peleador_telefono: string;
  peleador_apodo: string;
  peleador_dni: string;
  evento_titulo: string;
  fecha_evento: string;
  precio_evento: string;
  club_nombre: string | null;
}

export default function PaymentManagement() {
  const [inscripciones, setInscripciones] = useState<Inscripcion[]>([]);
  const [loading, setLoading] = useState(true);
  const [filtro, setFiltro] = useState<'todas' | 'pendientes' | 'pagadas'>('todas');
  const [showModal, setShowModal] = useState(false);
  const [selectedInscripcion, setSelectedInscripcion] = useState<Inscripcion | null>(null);
  const [processing, setProcessing] = useState(false);

  // Formulario de confirmación de pago
  const [formData, setFormData] = useState({
    monto_pagado: '',
    metodo_pago: 'transferencia',
    comprobante_pago: '',
    notas_admin: '',
  });

  useEffect(() => {
    loadInscripciones();
  }, [filtro]);

  const loadInscripciones = async () => {
    try {
      setLoading(true);
      let data;

      if (filtro === 'pendientes') {
        data = await AdminService.getInscripcionesPendientes();
      } else if (filtro === 'pagadas') {
        data = await AdminService.getInscripciones({ estado_pago: 'pagado' });
      } else {
        data = await AdminService.getInscripciones();
      }

      setInscripciones(data.inscripciones || []);
    } catch (error) {
      console.error('Error loading inscriptions:', error);
      Alert.alert('Error', 'No se pudieron cargar las inscripciones');
      setInscripciones([]);
    } finally {
      setLoading(false);
    }
  };

  const handleConfirmarPago = (inscripcion: Inscripcion) => {
    setSelectedInscripcion(inscripcion);
    setFormData({
      monto_pagado: inscripcion.precio_evento,
      metodo_pago: 'transferencia',
      comprobante_pago: '',
      notas_admin: '',
    });
    setShowModal(true);
  };

  const submitConfirmacion = async () => {
    if (!selectedInscripcion) return;

    // Validar monto
    const monto = parseFloat(formData.monto_pagado);
    if (isNaN(monto) || monto <= 0) {
      Alert.alert('Error', 'Ingresa un monto válido');
      return;
    }

    try {
      setProcessing(true);
      await AdminService.confirmarPago(selectedInscripcion.id, {
        monto_pagado: monto,
        metodo_pago: formData.metodo_pago,
        comprobante_pago: formData.comprobante_pago || undefined,
        notas_admin: formData.notas_admin || undefined,
      });

      Alert.alert('Éxito', 'Pago confirmado exitosamente');
      setShowModal(false);
      loadInscripciones();
    } catch (error) {
      Alert.alert('Error', 'No se pudo confirmar el pago');
    } finally {
      setProcessing(false);
    }
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', {
      day: '2-digit',
      month: 'short',
      year: 'numeric'
    });
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#e74c3c" />
        <Text style={styles.loadingText}>Cargando inscripciones...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.title}>Gestión de Pagos</Text>
          <Text style={styles.subtitle}>{inscripciones.length} inscripciones</Text>
        </View>
      </View>

      {/* Filtros */}
      <View style={styles.filterContainer}>
        <TouchableOpacity
          style={[styles.filterButton, filtro === 'todas' && styles.filterButtonActive]}
          onPress={() => setFiltro('todas')}
        >
          <Text style={[styles.filterText, filtro === 'todas' && styles.filterTextActive]}>
            Todas
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.filterButton, filtro === 'pendientes' && styles.filterButtonActive]}
          onPress={() => setFiltro('pendientes')}
        >
          <Text style={[styles.filterText, filtro === 'pendientes' && styles.filterTextActive]}>
            Pendientes
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.filterButton, filtro === 'pagadas' && styles.filterButtonActive]}
          onPress={() => setFiltro('pagadas')}
        >
          <Text style={[styles.filterText, filtro === 'pagadas' && styles.filterTextActive]}>
            Pagadas
          </Text>
        </TouchableOpacity>
      </View>

      {/* Lista de inscripciones */}
      <ScrollView style={styles.list}>
        {inscripciones.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No hay inscripciones para mostrar</Text>
          </View>
        ) : (
          inscripciones.map((inscripcion) => (
            <View key={inscripcion.id} style={styles.card}>
              <View style={styles.cardHeader}>
                <View style={styles.headerLeft}>
                  <Text style={styles.peleadorName}>{inscripcion.peleador_nombre}</Text>
                  {inscripcion.peleador_apodo && (
                    <Text style={styles.peleadorApodo}>"{inscripcion.peleador_apodo}"</Text>
                  )}
                </View>
                <View
                  style={[
                    styles.badge,
                    {
                      backgroundColor:
                        inscripcion.estado_pago === 'pagado' ? '#27ae60' : '#f39c12',
                    },
                  ]}
                >
                  <Text style={styles.badgeText}>
                    {inscripcion.estado_pago === 'pagado' ? 'PAGADO' : 'PENDIENTE'}
                  </Text>
                </View>
              </View>

              <View style={styles.cardBody}>
                <View style={styles.infoRow}>
                  <Text style={styles.infoLabel}>Evento:</Text>
                  <Text style={styles.infoValue}>{inscripcion.evento_titulo}</Text>
                </View>

                <View style={styles.infoRow}>
                  <Text style={styles.infoLabel}>Fecha evento:</Text>
                  <Text style={styles.infoValue}>{formatDate(inscripcion.fecha_evento)}</Text>
                </View>

                <View style={styles.infoRow}>
                  <Text style={styles.infoLabel}>DNI:</Text>
                  <Text style={styles.infoValue}>{inscripcion.peleador_dni}</Text>
                </View>

                <View style={styles.infoRow}>
                  <Text style={styles.infoLabel}>Teléfono:</Text>
                  <Text style={styles.infoValue}>{inscripcion.peleador_telefono}</Text>
                </View>

                {inscripcion.club_nombre && (
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Club:</Text>
                    <Text style={styles.infoValue}>{inscripcion.club_nombre}</Text>
                  </View>
                )}

                <View style={styles.paymentInfo}>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Monto:</Text>
                    <Text style={styles.montoText}>S/ {inscripcion.monto_pagado}</Text>
                  </View>

                  {inscripcion.estado_pago === 'pagado' && inscripcion.fecha_pago && (
                    <>
                      <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Pagado el:</Text>
                        <Text style={styles.infoValue}>{formatDate(inscripcion.fecha_pago)}</Text>
                      </View>
                      <View style={styles.infoRow}>
                        <Text style={styles.infoLabel}>Método:</Text>
                        <Text style={styles.infoValue}>{inscripcion.metodo_pago}</Text>
                      </View>
                    </>
                  )}
                </View>
              </View>

              {inscripcion.estado_pago === 'pendiente' && (
                <TouchableOpacity
                  style={styles.confirmButton}
                  onPress={() => handleConfirmarPago(inscripcion)}
                >
                  <Text style={styles.confirmButtonText}>✓ Confirmar Pago</Text>
                </TouchableOpacity>
              )}
            </View>
          ))
        )}
      </ScrollView>

      {/* Modal de confirmación de pago */}
      <Modal
        visible={showModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Confirmar Pago</Text>
              <TouchableOpacity onPress={() => setShowModal(false)}>
                <Text style={styles.closeButton}>✕</Text>
              </TouchableOpacity>
            </View>

            {selectedInscripcion && (
              <>
                <Text style={styles.modalSubtitle}>
                  {selectedInscripcion.peleador_nombre} - {selectedInscripcion.evento_titulo}
                </Text>

                <View style={styles.formGroup}>
                  <Text style={styles.label}>Monto Pagado (S/)</Text>
                  <TextInput
                    style={styles.input}
                    placeholder="20.00"
                    placeholderTextColor="#666"
                    value={formData.monto_pagado}
                    onChangeText={(text) => setFormData({ ...formData, monto_pagado: text })}
                    keyboardType="decimal-pad"
                  />
                </View>

                <View style={styles.formGroup}>
                  <Text style={styles.label}>Método de Pago</Text>
                  <View style={styles.methodsGrid}>
                    {['efectivo', 'transferencia', 'yape', 'plin', 'deposito'].map((metodo) => (
                      <TouchableOpacity
                        key={metodo}
                        style={[
                          styles.methodButton,
                          formData.metodo_pago === metodo && styles.methodButtonActive,
                        ]}
                        onPress={() => setFormData({ ...formData, metodo_pago: metodo })}
                      >
                        <Text
                          style={[
                            styles.methodText,
                            formData.metodo_pago === metodo && styles.methodTextActive,
                          ]}
                        >
                          {metodo.charAt(0).toUpperCase() + metodo.slice(1)}
                        </Text>
                      </TouchableOpacity>
                    ))}
                  </View>
                </View>

                <View style={styles.formGroup}>
                  <Text style={styles.label}>Comprobante (opcional)</Text>
                  <TextInput
                    style={styles.input}
                    placeholder="URL del comprobante"
                    placeholderTextColor="#666"
                    value={formData.comprobante_pago}
                    onChangeText={(text) => setFormData({ ...formData, comprobante_pago: text })}
                  />
                </View>

                <View style={styles.formGroup}>
                  <Text style={styles.label}>Notas (opcional)</Text>
                  <TextInput
                    style={[styles.input, styles.textArea]}
                    placeholder="Notas adicionales..."
                    placeholderTextColor="#666"
                    value={formData.notas_admin}
                    onChangeText={(text) => setFormData({ ...formData, notas_admin: text })}
                    multiline
                    numberOfLines={3}
                  />
                </View>

                <View style={styles.modalActions}>
                  <TouchableOpacity
                    style={[styles.modalButton, styles.cancelButton]}
                    onPress={() => setShowModal(false)}
                  >
                    <Text style={styles.buttonText}>Cancelar</Text>
                  </TouchableOpacity>

                  <TouchableOpacity
                    style={[styles.modalButton, styles.submitButton]}
                    onPress={submitConfirmacion}
                    disabled={processing}
                  >
                    {processing ? (
                      <ActivityIndicator color="#fff" />
                    ) : (
                      <Text style={styles.buttonText}>Confirmar</Text>
                    )}
                  </TouchableOpacity>
                </View>
              </>
            )}
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
  filterContainer: {
    flexDirection: 'row',
    padding: 15,
    gap: 10,
  },
  filterButton: {
    flex: 1,
    paddingVertical: 10,
    paddingHorizontal: 15,
    borderRadius: 8,
    backgroundColor: '#2c2c2c',
    alignItems: 'center',
  },
  filterButtonActive: {
    backgroundColor: '#e74c3c',
  },
  filterText: {
    color: '#999',
    fontSize: 14,
    fontWeight: '600',
  },
  filterTextActive: {
    color: '#fff',
  },
  list: {
    flex: 1,
    padding: 15,
  },
  emptyContainer: {
    paddingVertical: 40,
    alignItems: 'center',
  },
  emptyText: {
    color: '#666',
    fontSize: 16,
  },
  card: {
    backgroundColor: '#2c2c2c',
    borderRadius: 12,
    padding: 15,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: '#444',
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  headerLeft: {
    flex: 1,
  },
  peleadorName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#fff',
  },
  peleadorApodo: {
    fontSize: 14,
    color: '#e74c3c',
    fontStyle: 'italic',
    marginTop: 2,
  },
  badge: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 6,
  },
  badgeText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: 'bold',
  },
  cardBody: {
    gap: 8,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  infoLabel: {
    fontSize: 13,
    color: '#999',
  },
  infoValue: {
    fontSize: 13,
    color: '#fff',
    fontWeight: '500',
  },
  paymentInfo: {
    marginTop: 10,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#444',
    gap: 8,
  },
  montoText: {
    fontSize: 16,
    color: '#27ae60',
    fontWeight: 'bold',
  },
  confirmButton: {
    marginTop: 12,
    backgroundColor: '#27ae60',
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  confirmButtonText: {
    color: '#fff',
    fontSize: 15,
    fontWeight: 'bold',
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
    marginBottom: 10,
  },
  modalTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#fff',
  },
  modalSubtitle: {
    fontSize: 14,
    color: '#999',
    marginBottom: 20,
  },
  closeButton: {
    fontSize: 28,
    color: '#999',
    fontWeight: 'bold',
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
  methodsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  methodButton: {
    paddingHorizontal: 15,
    paddingVertical: 10,
    borderRadius: 8,
    backgroundColor: '#1a1a1a',
    borderWidth: 1,
    borderColor: '#444',
  },
  methodButtonActive: {
    backgroundColor: '#e74c3c',
    borderColor: '#e74c3c',
  },
  methodText: {
    color: '#999',
    fontSize: 13,
    fontWeight: '600',
  },
  methodTextActive: {
    color: '#fff',
  },
  modalActions: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 10,
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
  submitButton: {
    backgroundColor: '#27ae60',
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});
