import React, { useState, useEffect } from 'react';
import {
  View,
  StyleSheet,
  SafeAreaView,
  StatusBar,
  ScrollView,
  Alert,
  ActivityIndicator,
  Text,
  Image,
  TouchableOpacity,
  Platform,
} from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import * as Haptics from 'expo-haptics';
import { useNavigation } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../constants/theme';
import { ScreenHeader } from '../components/common/ScreenHeader';
import { FormSection } from '../components/form/FormSection';
import { FormInput } from '../components/form/FormInput';
import { FormSelect } from '../components/form/FormSelect';
import { PhoneInput } from '../components/form/PhoneInput';
import { SubmitButton } from '../components/form/SubmitButton';
import { ClubSelector } from '../components/form/ClubSelector';
import { SuccessModal } from '../components/SuccessModal';
import { clubService, Club } from '../services/clubService';
import api from '../services/api';

interface FormData {
  nombre: string;
  apellidos: string;
  email: string;
  password: string;
  confirmPassword: string;
  telefono: string;
  countryCode: string;
  club_id: string | number;
}

interface FormErrors {
  [key: string]: string;
}

export default function RegisterUserScreen() {
  const navigation = useNavigation();

  const [formData, setFormData] = useState<FormData>({
    nombre: '',
    apellidos: '',
    email: '',
    password: '',
    confirmPassword: '',
    telefono: '',
    countryCode: 'PE',
    club_id: '',
  });

  const [focusedField, setFocusedField] = useState<string | null>(null);
  const [errors, setErrors] = useState<FormErrors>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [clubs, setClubs] = useState<Club[]>([]);
  const [loadingClubs, setLoadingClubs] = useState(true);
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [successData, setSuccessData] = useState<{ nombre: string; email: string } | null>(null);
  const [photo, setPhoto] = useState<{ uri: string; name?: string; type?: string } | null>(null);

  useEffect(() => {
    loadClubs();
  }, []);

  // Mostrar modal de éxito cuando successData cambie
  useEffect(() => {
    if (successData) {
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      setShowSuccessModal(true);
    }
  }, [successData]);

  const handleCloseSuccessModal = () => {
    setShowSuccessModal(false);
    setSuccessData(null);
    setPhoto(null);
    setFormData({
      nombre: '',
      apellidos: '',
      email: '',
      password: '',
      confirmPassword: '',
      telefono: '',
      countryCode: 'PE',
      club_id: '',
    });
    navigation.navigate('Home' as never);
  };

  const pickImage = async () => {
    try {
      const permissionResult = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (!permissionResult.granted) {
        Alert.alert('Permiso requerido', 'Se requiere permiso para acceder a tu galería.');
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ['images'],
        allowsEditing: true,
        quality: 0.7,
        aspect: [1, 1],
      });

      if (!result.canceled && result.assets && result.assets.length > 0) {
        const asset = result.assets[0];
        const uri = asset.uri;

        const uriParts = uri.split('.');
        const fileType = uriParts[uriParts.length - 1];

        const name = `profile_${Date.now()}.${fileType}`;
        const type = `image/${fileType === 'jpg' ? 'jpeg' : fileType}`;

        setPhoto({ uri, name, type });
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      }
    } catch (err) {
      console.error('Error al seleccionar imagen:', err);
      Alert.alert('Error', 'No se pudo seleccionar la imagen.');
    }
  };

  const loadClubs = async () => {
    try {
      setLoadingClubs(true);
      const clubsData = await clubService.getAll();
      setClubs(clubsData || []);
    } catch (error) {
      console.error('Error cargando clubs:', error);
      setClubs([]);
      Alert.alert('Error', 'No se pudieron cargar los clubs disponibles');
    } finally {
      setLoadingClubs(false);
    }
  };

  const updateField = (field: keyof FormData, value: string | number) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    if (errors[field]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
  };

  const validateForm = (): boolean => {
    const newErrors: FormErrors = {};

    // Validar nombre
    if (!formData.nombre.trim()) {
      newErrors.nombre = 'El nombre es requerido';
    }

    // Validar email
    if (!formData.email.trim()) {
      newErrors.email = 'El email es requerido';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Email inválido';
    }

    // Validar contraseña
    if (!formData.password) {
      newErrors.password = 'La contraseña es requerida';
    } else if (formData.password.length < 6) {
      newErrors.password = 'Mínimo 6 caracteres';
    }

    // Validar confirmación de contraseña
    if (!formData.confirmPassword) {
      newErrors.confirmPassword = 'Confirma tu contraseña';
    } else if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Las contraseñas no coinciden';
    }

    // Validar teléfono
    if (!formData.telefono.trim()) {
      newErrors.telefono = 'El teléfono es requerido';
    } else if (formData.countryCode === 'PE') {
      const phoneDigits = formData.telefono.replace(/\D/g, '');
      if (phoneDigits.length !== 9) {
        newErrors.telefono = 'Debe tener 9 dígitos';
      } else if (!phoneDigits.startsWith('9')) {
        newErrors.telefono = 'Debe empezar con 9';
      }
    }

    // Validar club
    if (!formData.club_id) {
      newErrors.club_id = 'Selecciona tu club favorito';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);

    if (!validateForm()) {
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
      Alert.alert(
        'Campos requeridos',
        'Por favor completa todos los campos marcados en rojo.',
        [{ text: 'OK' }]
      );
      return;
    }

    setIsSubmitting(true);

    try {
      const COUNTRY_CODES: { [key: string]: string } = {
        'PE': '+51',
        'AR': '+54',
        'CL': '+56',
        'CO': '+57',
        'MX': '+52',
        'EC': '+593',
        'BO': '+591',
        'VE': '+58',
        'US': '+1',
        'ES': '+34',
      };

      const dialCode = COUNTRY_CODES[formData.countryCode] || '+51';
      const fullPhone = `${dialCode}${formData.telefono.trim()}`;

      // Crear FormData para soportar subida de foto
      const form = new FormData();
      form.append('nombre', formData.nombre.trim());
      form.append('apellidos', formData.apellidos.trim() || '');
      form.append('email', formData.email.trim().toLowerCase());
      form.append('password', formData.password);
      form.append('telefono', fullPhone);
      form.append('club_id', String(formData.club_id));
      form.append('tipo_id', '3'); // Espectador

      // Agregar foto si existe
      if (photo) {
        if (Platform.OS === 'web') {
          const response = await fetch(photo.uri);
          const blob = await response.blob();
          form.append('foto_perfil', blob, photo.name || 'foto.jpg');
        } else {
          form.append('foto_perfil', {
            uri: photo.uri,
            name: photo.name || 'foto.jpg',
            type: photo.type || 'image/jpeg',
          } as any);
        }
      }

      const response = await api.post('/usuarios/registro', form, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      // Detener el loading
      setIsSubmitting(false);

      // Guardar los datos de éxito - esto disparará el useEffect que muestra el modal
      setSuccessData({
        nombre: formData.nombre,
        email: formData.email,
      });
    } catch (error: any) {
      console.error('Error al registrar espectador:', error);
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);

      let errorMessage = 'Ocurrió un error al procesar tu registro. Intenta nuevamente.';

      if (error.response) {
        if (error.response.status === 409) {
          errorMessage = 'Este email ya está registrado.';
        } else if (error.response.data?.message) {
          errorMessage = error.response.data.message;
        }
      } else if (error.request) {
        errorMessage = 'No se pudo conectar con el servidor.';
      }

      Alert.alert('Error de Registro', errorMessage, [{ text: 'OK' }]);
      setIsSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#000" />
      <ScreenHeader title="CREAR CUENTA" />

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >

        <View style={styles.formContainer}>
          {/* Foto de Perfil */}
          <FormSection icon="📸" title="FOTO DE PERFIL (OPCIONAL)">
            <View style={styles.photoSection}>
              <TouchableOpacity
                style={styles.photoButton}
                onPress={pickImage}
                activeOpacity={0.7}
              >
                {photo ? (
                  <Image source={{ uri: photo.uri }} style={styles.photoPreview} />
                ) : (
                  <View style={styles.photoPlaceholder}>
                    <Ionicons name="camera" size={40} color={COLORS.primary} />
                    <Text style={styles.photoPlaceholderText}>Toca para seleccionar</Text>
                  </View>
                )}
              </TouchableOpacity>
              {photo && (
                <TouchableOpacity
                  style={styles.removePhotoButton}
                  onPress={() => {
                    setPhoto(null);
                    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
                  }}
                >
                  <Ionicons name="close-circle" size={24} color={COLORS.error} />
                  <Text style={styles.removePhotoText}>Eliminar foto</Text>
                </TouchableOpacity>
              )}
            </View>
          </FormSection>

          {/* Datos Personales */}
          <FormSection icon="👤" title="DATOS PERSONALES">
            <FormInput
              label="Nombre"
              value={formData.nombre}
              onChangeText={(value) => updateField('nombre', value)}
              placeholder="Ej: Juan"
              focused={focusedField === 'nombre'}
              onFocus={() => setFocusedField('nombre')}
              onBlur={() => setFocusedField(null)}
              error={errors.nombre}
            />

            <FormInput
              label="Apellidos (opcional)"
              value={formData.apellidos}
              onChangeText={(value) => updateField('apellidos', value)}
              placeholder="Ej: Pérez García"
              focused={focusedField === 'apellidos'}
              onFocus={() => setFocusedField('apellidos')}
              onBlur={() => setFocusedField(null)}
            />
          </FormSection>

          {/* Contacto */}
          <FormSection icon="📱" title="CONTACTO">
            <FormInput
              label="Email"
              value={formData.email}
              onChangeText={(value) => updateField('email', value)}
              placeholder="espectador@ejemplo.com"
              keyboardType="email-address"
              autoCapitalize="none"
              focused={focusedField === 'email'}
              onFocus={() => setFocusedField('email')}
              onBlur={() => setFocusedField(null)}
              error={errors.email}
            />

            <PhoneInput
              label="Teléfono"
              value={formData.telefono}
              onChangeText={(value) => updateField('telefono', value)}
              countryCode={formData.countryCode}
              onCountryChange={(code) => updateField('countryCode', code)}
              placeholder="Ej: 987654321"
              focused={focusedField === 'telefono'}
              onFocus={() => setFocusedField('telefono')}
              onBlur={() => setFocusedField(null)}
              error={errors.telefono}
            />
          </FormSection>

          {/* Seguridad */}
          <FormSection icon="🔒" title="SEGURIDAD">
            <FormInput
              label="Contraseña"
              value={formData.password}
              onChangeText={(value) => updateField('password', value)}
              placeholder="Mínimo 6 caracteres"
              focused={focusedField === 'password'}
              onFocus={() => setFocusedField('password')}
              onBlur={() => setFocusedField(null)}
              error={errors.password}
            />

            <FormInput
              label="Confirmar Contraseña"
              value={formData.confirmPassword}
              onChangeText={(value) => updateField('confirmPassword', value)}
              placeholder="Repite tu contraseña"
              focused={focusedField === 'confirmPassword'}
              onFocus={() => setFocusedField('confirmPassword')}
              onBlur={() => setFocusedField(null)}
              error={errors.confirmPassword}
            />
          </FormSection>

          {/* Club Favorito */}
          <FormSection icon="❤️" title="CLUB FAVORITO">
            {loadingClubs ? (
              <View style={styles.loadingContainer}>
                <ActivityIndicator size="large" color={COLORS.primary} />
                <Text style={styles.loadingText}>Cargando clubs...</Text>
              </View>
            ) : (
              <ClubSelector
                label="¿A qué club apoyas?"
                value={formData.club_id}
                onValueChange={(value) => updateField('club_id', value)}
                options={clubs || []}
                placeholder="Selecciona tu club favorito"
                error={errors.club_id}
              />
            )}
          </FormSection>

          <SubmitButton
            onPress={handleSubmit}
            isLoading={isSubmitting}
            title="CREAR CUENTA"
            icon="👤"
            style={{ marginTop: -SPACING.md }}
          />
        </View>
      </ScrollView>

      {/* Modal de éxito */}
      <SuccessModal
        visible={showSuccessModal}
        title="¡Cuenta Creada! 🎉"
        message={
          successData
            ? `¡Bienvenido ${successData.nombre}!\n\n` +
            `Tu cuenta ha sido creada exitosamente.\n\n` +
            `📧 Email: ${successData.email}\n` +
            `🔑 Tu contraseña ha sido guardada de forma segura\n\n` +
            `Ya puedes comprar entradas y disfrutar de los eventos de boxeo.\n\n` +
            `¡Te esperamos en El Jab Dorado!`
            : ''
        }
        buttonText="Ir al Inicio"
        onClose={handleCloseSuccessModal}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 130,
  },
  header: {
    padding: SPACING.xl,
    backgroundColor: COLORS.primary,
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: TYPOGRAPHY.fontSize.xxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    marginBottom: SPACING.sm,
    textAlign: 'center',
  },
  headerSubtitle: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.inverse,
    textAlign: 'center',
    opacity: 0.9,
  },
  formContainer: {
    paddingHorizontal: SPACING.lg,
    paddingTop: SPACING.lg,
  },
  loadingContainer: {
    padding: SPACING.xl,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingText: {
    marginTop: SPACING.md,
    fontSize: 14,
    color: COLORS.text.secondary,
  },
  photoSection: {
    alignItems: 'center',
    gap: SPACING.md,
  },
  photoButton: {
    width: 150,
    height: 150,
    borderRadius: 75,
    overflow: 'hidden',
    borderWidth: 3,
    borderColor: COLORS.primary,
    borderStyle: 'dashed',
  },
  photoPreview: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover',
  },
  photoPlaceholder: {
    width: '100%',
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    gap: SPACING.xs,
  },
  photoPlaceholderText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.secondary,
    textAlign: 'center',
  },
  removePhotoButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.xs,
    padding: SPACING.sm,
  },
  removePhotoText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.error,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
  },
});
