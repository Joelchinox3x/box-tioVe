import React, { useState } from 'react';
import { 
  SafeAreaView, 
  ScrollView, 
  StyleSheet, 
  View, 
  Text,
  TextInput,
  TouchableOpacity,
  Image,
  Alert,
  Platform,
  KeyboardAvoidingView
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import DateTimePicker from '@react-native-community/datetimepicker';
import { Picker } from '@react-native-picker/picker';
import boxApi from '../api/boxApi';

interface FormData {
  nombre: string;
  apellidos: string;
  apodo: string;
  fecha_nacimiento: Date;
  email: string;
  telefono: string;
  peso_actual: string;
  altura: string;
  club: string;
  estilo: 'fajador' | 'estilista';
  experiencia: string;
  victorias: string;
  derrotas: string;
  empates: string;
  foto_perfil: string | null;
  documento_identidad: string;
  certificado_medico: boolean;
  aceptar_terminos: boolean;
}

interface FormErrors {
  [key: string]: string;
}

export default function FighterFormScreen({ navigation }: any) {
  const [step, setStep] = useState(1); // Paso del formulario (1-3)
  const [loading, setLoading] = useState(false);
  const [showDatePicker, setShowDatePicker] = useState(false);
  
  const [formData, setFormData] = useState<FormData>({
    nombre: '',
    apellidos: '',
    apodo: '',
    fecha_nacimiento: new Date(2000, 0, 1),
    email: '',
    telefono: '',
    peso_actual: '',
    altura: '',
    club: '',
    estilo: 'fajador',
    experiencia: '',
    victorias: '0',
    derrotas: '0',
    empates: '0',
    foto_perfil: null,
    documento_identidad: '',
    certificado_medico: false,
    aceptar_terminos: false,
  });

  const [errors, setErrors] = useState<FormErrors>({});

  // Validación por pasos
  const validateStep = (currentStep: number): boolean => {
    const newErrors: FormErrors = {};

    if (currentStep === 1) {
      if (!formData.nombre.trim()) newErrors.nombre = 'El nombre es requerido';
      if (!formData.apellidos.trim()) newErrors.apellidos = 'Los apellidos son requeridos';
      if (!formData.apodo.trim()) newErrors.apodo = 'El apodo es requerido';
      if (!formData.email.trim()) {
        newErrors.email = 'El email es requerido';
      } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
        newErrors.email = 'Email inválido';
      }
      if (!formData.telefono.trim()) {
        newErrors.telefono = 'El teléfono es requerido';
      } else if (!/^\d{9}$/.test(formData.telefono)) {
        newErrors.telefono = 'Debe ser un número de 9 dígitos';
      }
      if (!formData.documento_identidad.trim()) {
        newErrors.documento_identidad = 'El DNI es requerido';
      } else if (!/^\d{8}$/.test(formData.documento_identidad)) {
        newErrors.documento_identidad = 'Debe ser un DNI válido (8 dígitos)';
      }
      
      const age = new Date().getFullYear() - formData.fecha_nacimiento.getFullYear();
      if (age < 18) {
        newErrors.fecha_nacimiento = 'Debes ser mayor de 18 años';
      }
    }

    if (currentStep === 2) {
      if (!formData.peso_actual) {
        newErrors.peso_actual = 'El peso es requerido';
      } else if (parseFloat(formData.peso_actual) < 45 || parseFloat(formData.peso_actual) > 150) {
        newErrors.peso_actual = 'Peso inválido (45-150 kg)';
      }
      
      if (!formData.altura) {
        newErrors.altura = 'La altura es requerida';
      } else if (parseFloat(formData.altura) < 1.40 || parseFloat(formData.altura) > 2.20) {
        newErrors.altura = 'Altura inválida (1.40-2.20 m)';
      }
      
      if (!formData.club.trim()) newErrors.club = 'El club/gimnasio es requerido';
      if (!formData.experiencia.trim()) newErrors.experiencia = 'La experiencia es requerida';
    }

    if (currentStep === 3) {
      if (!formData.foto_perfil) newErrors.foto_perfil = 'La foto es requerida';
      if (!formData.certificado_medico) {
        newErrors.certificado_medico = 'Debes aceptar tener certificado médico';
      }
      if (!formData.aceptar_terminos) {
        newErrors.aceptar_terminos = 'Debes aceptar los términos';
      }
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleNext = () => {
    if (validateStep(step)) {
      setStep(step + 1);
    }
  };

  const handleBack = () => {
    setStep(step - 1);
  };

  const handleImagePick = async () => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    
    if (status !== 'granted') {
      Alert.alert('Permiso denegado', 'Necesitamos acceso a tu galería');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [1, 1],
      quality: 0.8,
    });

    if (!result.canceled) {
      setFormData({ ...formData, foto_perfil: result.assets[0].uri });
      setErrors({ ...errors, foto_perfil: '' });
    }
  };

  const handleSubmit = async () => {
    if (!validateStep(3)) return;

    setLoading(true);

    try {
      // Crear FormData para enviar archivo
      const formDataToSend = new FormData();
      
      // Datos del peleador
      formDataToSend.append('nombre', formData.nombre);
      formDataToSend.append('apellidos', formData.apellidos);
      formDataToSend.append('apodo', formData.apodo);
      formDataToSend.append('fecha_nacimiento', formData.fecha_nacimiento.toISOString().split('T')[0]);
      formDataToSend.append('email', formData.email);
      formDataToSend.append('telefono', formData.telefono);
      formDataToSend.append('peso_actual', formData.peso_actual);
      formDataToSend.append('altura', formData.altura);
      formDataToSend.append('club', formData.club);
      formDataToSend.append('estilo', formData.estilo);
      formDataToSend.append('experiencia', formData.experiencia);
      formDataToSend.append('victorias', formData.victorias);
      formDataToSend.append('derrotas', formData.derrotas);
      formDataToSend.append('empates', formData.empates);
      formDataToSend.append('documento_identidad', formData.documento_identidad);

      // Foto de perfil
      if (formData.foto_perfil) {
        const uriParts = formData.foto_perfil.split('.');
        const fileType = uriParts[uriParts.length - 1];
        
        formDataToSend.append('foto_perfil', {
          uri: formData.foto_perfil,
          name: `perfil.${fileType}`,
          type: `image/${fileType}`,
        } as any);
      }

      const response = await boxApi.post('/inscripciones', formDataToSend, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      setLoading(false);

      if (response.data.success) {
        Alert.alert(
          '¡Inscripción Exitosa! 🥊',
          'Tu solicitud ha sido enviada. Te notificaremos cuando sea aprobada.',
          [
            {
              text: 'Ir al Inicio',
              onPress: () => navigation.navigate('Home'),
            },
          ]
        );
      }
    } catch (error: any) {
      setLoading(false);
      Alert.alert(
        'Error',
        error.response?.data?.message || 'Ocurrió un error al enviar la inscripción'
      );
    }
  };

  const updateField = (field: keyof FormData, value: any) => {
    setFormData({ ...formData, [field]: value });
    if (errors[field]) {
      setErrors({ ...errors, [field]: '' });
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <KeyboardAvoidingView 
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.keyboardView}
      >
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()}>
            <Ionicons name="arrow-back" size={24} color="#FFD700" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Inscripción de Peleador</Text>
          <View style={{ width: 24 }} />
        </View>

        {/* Progress Bar */}
        <View style={styles.progressContainer}>
          <View style={styles.progressBar}>
            <View style={[styles.progressFill, { width: `${(step / 3) * 100}%` }]} />
          </View>
          <Text style={styles.progressText}>Paso {step} de 3</Text>
        </View>

        <ScrollView 
          showsVerticalScrollIndicator={false}
          contentContainerStyle={styles.scrollContent}
        >
          {/* PASO 1: Datos Personales */}
          {step === 1 && (
            <View style={styles.stepContainer}>
              <Text style={styles.stepTitle}>📋 Datos Personales</Text>
              
              <InputField
                label="Nombre *"
                placeholder="Ej: Carlos"
                value={formData.nombre}
                onChangeText={(text) => updateField('nombre', text)}
                error={errors.nombre}
                autoCapitalize="words"
              />

              <InputField
                label="Apellidos *"
                placeholder="Ej: Mendoza García"
                value={formData.apellidos}
                onChangeText={(text) => updateField('apellidos', text)}
                error={errors.apellidos}
                autoCapitalize="words"
              />

              <InputField
                label='Apodo / Alias *'
                placeholder='Ej: "El Rayo"'
                value={formData.apodo}
                onChangeText={(text) => updateField('apodo', text)}
                error={errors.apodo}
              />

              <View style={styles.inputContainer}>
                <Text style={styles.label}>Fecha de Nacimiento *</Text>
                <TouchableOpacity 
                  style={[styles.dateButton, errors.fecha_nacimiento && styles.inputError]}
                  onPress={() => setShowDatePicker(true)}
                >
                  <Ionicons name="calendar" size={20} color="#FFD700" />
                  <Text style={styles.dateText}>
                    {formData.fecha_nacimiento.toLocaleDateString('es-ES')}
                  </Text>
                </TouchableOpacity>
                {errors.fecha_nacimiento && (
                  <Text style={styles.errorText}>{errors.fecha_nacimiento}</Text>
                )}
              </View>

              {showDatePicker && (
                <DateTimePicker
                  value={formData.fecha_nacimiento}
                  mode="date"
                  display="default"
                  maximumDate={new Date()}
                  onChange={(event, selectedDate) => {
                    setShowDatePicker(false);
                    if (selectedDate) {
                      updateField('fecha_nacimiento', selectedDate);
                    }
                  }}
                />
              )}

              <InputField
                label="DNI *"
                placeholder="12345678"
                value={formData.documento_identidad}
                onChangeText={(text) => updateField('documento_identidad', text.replace(/\D/g, ''))}
                error={errors.documento_identidad}
                keyboardType="numeric"
                maxLength={8}
              />

              <InputField
                label="Email *"
                placeholder="ejemplo@correo.com"
                value={formData.email}
                onChangeText={(text) => updateField('email', text.toLowerCase())}
                error={errors.email}
                keyboardType="email-address"
                autoCapitalize="none"
              />

              <InputField
                label="Teléfono / WhatsApp *"
                placeholder="987654321"
                value={formData.telefono}
                onChangeText={(text) => updateField('telefono', text.replace(/\D/g, ''))}
                error={errors.telefono}
                keyboardType="phone-pad"
                maxLength={9}
              />
            </View>
          )}

          {/* PASO 2: Datos Deportivos */}
          {step === 2 && (
            <View style={styles.stepContainer}>
              <Text style={styles.stepTitle}>🥊 Datos Deportivos</Text>

              <InputField
                label="Peso Actual (kg) *"
                placeholder="75.5"
                value={formData.peso_actual}
                onChangeText={(text) => updateField('peso_actual', text)}
                error={errors.peso_actual}
                keyboardType="decimal-pad"
              />

              <InputField
                label="Altura (m) *"
                placeholder="1.75"
                value={formData.altura}
                onChangeText={(text) => updateField('altura', text)}
                error={errors.altura}
                keyboardType="decimal-pad"
              />

              <InputField
                label="Club / Gimnasio *"
                placeholder="Ej: Boxeo Los Campeones"
                value={formData.club}
                onChangeText={(text) => updateField('club', text)}
                error={errors.club}
                autoCapitalize="words"
              />

              <View style={styles.inputContainer}>
                <Text style={styles.label}>Estilo de Pelea *</Text>
                <View style={styles.pickerContainer}>
                  <Picker
                    selectedValue={formData.estilo}
                    onValueChange={(value) => updateField('estilo', value)}
                    style={styles.picker}
                    dropdownIconColor="#FFD700"
                  >
                    <Picker.Item label="🔥 Fajador (Agresivo)" value="fajador" />
                    <Picker.Item label="🎯 Estilista (Técnico)" value="estilista" />
                  </Picker>
                </View>
              </View>

              <InputField
                label="Años de Experiencia *"
                placeholder="Ej: 5"
                value={formData.experiencia}
                onChangeText={(text) => updateField('experiencia', text.replace(/\D/g, ''))}
                error={errors.experiencia}
                keyboardType="numeric"
              />

              <Text style={styles.sectionLabel}>Récord Deportivo</Text>
              <View style={styles.recordRow}>
                <View style={styles.recordInput}>
                  <InputField
                    label="Victorias"
                    placeholder="0"
                    value={formData.victorias}
                    onChangeText={(text) => updateField('victorias', text.replace(/\D/g, ''))}
                    keyboardType="numeric"
                  />
                </View>
                <View style={styles.recordInput}>
                  <InputField
                    label="Derrotas"
                    placeholder="0"
                    value={formData.derrotas}
                    onChangeText={(text) => updateField('derrotas', text.replace(/\D/g, ''))}
                    keyboardType="numeric"
                  />
                </View>
                <View style={styles.recordInput}>
                  <InputField
                    label="Empates"
                    placeholder="0"
                    value={formData.empates}
                    onChangeText={(text) => updateField('empates', text.replace(/\D/g, ''))}
                    keyboardType="numeric"
                  />
                </View>
              </View>
            </View>
          )}

          {/* PASO 3: Documentos y Confirmación */}
          {step === 3 && (
            <View style={styles.stepContainer}>
              <Text style={styles.stepTitle}>📸 Foto y Confirmación</Text>

              <View style={styles.photoSection}>
                <Text style={styles.label}>Foto de Perfil *</Text>
                <TouchableOpacity 
                  style={styles.photoButton}
                  onPress={handleImagePick}
                >
                  {formData.foto_perfil ? (
                    <Image 
                      source={{ uri: formData.foto_perfil }} 
                      style={styles.photoPreview}
                    />
                  ) : (
                    <View style={styles.photoPlaceholder}>
                      <Ionicons name="camera" size={48} color="#666" />
                      <Text style={styles.photoPlaceholderText}>
                        Toca para subir foto
                      </Text>
                    </View>
                  )}
                </TouchableOpacity>
                {errors.foto_perfil && (
                  <Text style={styles.errorText}>{errors.foto_perfil}</Text>
                )}
              </View>

              <View style={styles.checkboxContainer}>
                <TouchableOpacity
                  style={styles.checkbox}
                  onPress={() => updateField('certificado_medico', !formData.certificado_medico)}
                >
                  <Ionicons
                    name={formData.certificado_medico ? 'checkbox' : 'square-outline'}
                    size={24}
                    color={formData.certificado_medico ? '#4CAF50' : '#666'}
                  />
                  <Text style={styles.checkboxText}>
                    Declaro que cuento con certificado médico vigente *
                  </Text>
                </TouchableOpacity>
                {errors.certificado_medico && (
                  <Text style={styles.errorText}>{errors.certificado_medico}</Text>
                )}
              </View>

              <View style={styles.checkboxContainer}>
                <TouchableOpacity
                  style={styles.checkbox}
                  onPress={() => updateField('aceptar_terminos', !formData.aceptar_terminos)}
                >
                  <Ionicons
                    name={formData.aceptar_terminos ? 'checkbox' : 'square-outline'}
                    size={24}
                    color={formData.aceptar_terminos ? '#4CAF50' : '#666'}
                  />
                  <Text style={styles.checkboxText}>
                    Acepto los términos y condiciones del torneo *
                  </Text>
                </TouchableOpacity>
                {errors.aceptar_terminos && (
                  <Text style={styles.errorText}>{errors.aceptar_terminos}</Text>
                )}
              </View>

              <View style={styles.infoBox}>
                <Ionicons name="information-circle" size={24} color="#FFD700" />
                <Text style={styles.infoText}>
                  Tu inscripción será revisada por el equipo organizador. 
                  Te notificaremos vía email cuando sea aprobada.
                </Text>
              </View>
            </View>
          )}
        </ScrollView>

        {/* Botones de navegación */}
        <View style={styles.buttonsContainer}>
          {step > 1 && (
            <TouchableOpacity 
              style={styles.backButton}
              onPress={handleBack}
            >
              <Ionicons name="arrow-back" size={20} color="#FFD700" />
              <Text style={styles.backButtonText}>Atrás</Text>
            </TouchableOpacity>
          )}
          
          {step < 3 ? (
            <TouchableOpacity 
              style={styles.nextButton}
              onPress={handleNext}
            >
              <Text style={styles.nextButtonText}>Siguiente</Text>
              <Ionicons name="arrow-forward" size={20} color="#000" />
            </TouchableOpacity>
          ) : (
            <TouchableOpacity 
              style={[styles.submitButton, loading && styles.submitButtonDisabled]}
              onPress={handleSubmit}
              disabled={loading}
            >
              {loading ? (
                <Text style={styles.submitButtonText}>Enviando...</Text>
              ) : (
                <>
                  <Text style={styles.submitButtonText}>ENVIAR INSCRIPCIÓN</Text>
                  <Ionicons name="checkmark-circle" size={24} color="#000" />
                </>
              )}
            </TouchableOpacity>
          )}
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

// Componente auxiliar para inputs
const InputField = ({
  label,
  placeholder,
  value,
  onChangeText,
  error,
  keyboardType = 'default',
  autoCapitalize = 'sentences',
  maxLength,
}: any) => (
  <View style={styles.inputContainer}>
    <Text style={styles.label}>{label}</Text>
    <TextInput
      style={[styles.input, error && styles.inputError]}
      placeholder={placeholder}
      placeholderTextColor="#666"
      value={value}
      onChangeText={onChangeText}
      keyboardType={keyboardType}
      autoCapitalize={autoCapitalize}
      maxLength={maxLength}
    />
    {error && <Text style={styles.errorText}>{error}</Text>}
  </View>
);

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  keyboardView: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#333',
  },
  headerTitle: {
    color: '#FFD700',
    fontSize: 18,
    fontWeight: 'bold',
  },
  progressContainer: {
    padding: 15,
  },
  progressBar: {
    height: 6,
    backgroundColor: '#333',
    borderRadius: 3,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#FFD700',
  },
  progressText: {
    color: '#888',
    fontSize: 12,
    marginTop: 8,
    textAlign: 'center',
  },
  scrollContent: {
    padding: 15,
    paddingBottom: 100,
  },
  stepContainer: {
    gap: 15,
  },
  stepTitle: {
    color: '#FFD700',
    fontSize: 20,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  inputContainer: {
    marginBottom: 15,
  },
  label: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
    marginBottom: 8,
  },
  input: {
    backgroundColor: '#1a1a1a',
    borderWidth: 1,
    borderColor: '#333',
    borderRadius: 8,
    padding: 12,
    color: '#fff',
    fontSize: 15,
  },
  inputError: {
    borderColor: '#FF3B30',
  },
  errorText: {
    color: '#FF3B30',
    fontSize: 12,
    marginTop: 4,
  },
  dateButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#1a1a1a',
    borderWidth: 1,
    borderColor: '#333',
    borderRadius: 8,
    padding: 12,
    gap: 10,
  },
  dateText: {
    color: '#fff',
    fontSize: 15,
  },
  pickerContainer: {
    backgroundColor: '#1a1a1a',
    borderWidth: 1,
    borderColor: '#333',
    borderRadius: 8,
    overflow: 'hidden',
  },
  picker: {
    color: '#fff',
  },
  sectionLabel: {
    color: '#FFD700',
    fontSize: 16,
    fontWeight: 'bold',
    marginTop: 10,
    marginBottom: 5,
  },
  recordRow: {
    flexDirection: 'row',
    gap: 10,
  },
  recordInput: {
    flex: 1,
  },
  photoSection: {
    alignItems: 'center',
    marginVertical: 20,
  },
  photoButton: {
    width: 200,
    height: 200,
    borderRadius: 100,
    overflow: 'hidden',
  },
  photoPreview: {
    width: '100%',
    height: '100%',
  },
  photoPlaceholder: {
    width: '100%',
    height: '100%',
    backgroundColor: '#1a1a1a',
    borderWidth: 2,
    borderColor: '#333',
    borderStyle: 'dashed',
    justifyContent: 'center',
    alignItems: 'center',
    borderRadius: 100,
  },
  photoPlaceholderText: {
    color: '#666',
    fontSize: 12,
    marginTop: 10,
  },
  checkboxContainer: {
    marginVertical: 10,
  },
  checkbox: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
  },
  checkboxText: {
    color: '#ccc',
    fontSize: 14,
    flex: 1,
    lineHeight: 20,
  },
  infoBox: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#1a1a1a',
    padding: 15,
    borderRadius: 10,
    gap: 12,
    marginTop: 20,
    borderWidth: 1,
    borderColor: '#FFD700',
  },
  infoText: {
    color: '#ccc',
    fontSize: 13,
    lineHeight: 20,
    flex: 1,
  },
  buttonsContainer: {
    flexDirection: 'row',
    padding: 15,
    gap: 10,
    borderTopWidth: 1,
    borderTopColor: '#333',
    backgroundColor: '#000',
  },
  backButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#1a1a1a',
    paddingVertical: 15,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#FFD700',
    gap: 8,
  },
  backButtonText: {
    color: '#FFD700',
    fontSize: 16,
    fontWeight: 'bold',
  },
  nextButton: {
    flex: 2,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFD700',
    paddingVertical: 15,
    borderRadius: 10,
    gap: 8,
  },
  nextButtonText: {
    color: '#000',
    fontSize: 16,
    fontWeight: 'bold',
  },
  submitButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#4CAF50',
    paddingVertical: 15,
    borderRadius: 10,
    gap: 8,
  },
  submitButtonDisabled: {
    backgroundColor: '#666',
  },
  submitButtonText: {
    color: '#000',
    fontSize: 16,
    fontWeight: 'bold',
  },
});