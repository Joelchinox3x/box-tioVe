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
import { COLORS, SPACING } from '../constants/theme';
import { FighterFormHeader } from '../components/form/FighterFormHeader';
import { FormSection } from '../components/form/FormSection';
import { FormInput } from '../components/form/FormInput';
import { FormSelect } from '../components/form/FormSelect';
import { PhoneInput } from '../components/form/PhoneInput';
import { SubmitButton } from '../components/form/SubmitButton';
import { SponsorFooter } from '../components/form/SponsorFooter';
import { SuccessModal } from '../components/SuccessModal';
import { fighterService } from '../services/fighterService';
import { clubService, Club } from '../services/clubService';

interface FormData {
  nombre: string;
  apellidos: string;
  apodo: string;
  edad: string;
  peso: string;
  altura: string;
  genero: string;
  email: string;
  telefono: string;
  countryCode: string;
  dni: string;
  club_id: string | number;
}

interface FormErrors {
  [key: string]: string;
}

export default function FighterFormScreen() {
  console.log('🎯 FighterFormScreen montado');

  const navigation = useNavigation();

  const [formData, setFormData] = useState<FormData>({
    nombre: 'Miguel',
    apellidos: 'Rodríguez García',
    apodo: 'El Trueno',
    edad: '25',
    peso: '75.5',
    altura: '180',
    genero: 'masculino',
    email: '', // Lo dejas vacío para que lo llenes tú
    telefono: '987654321',
    countryCode: 'PE', // Perú por defecto
    dni: '99887766',
    club_id: '',
  });

  const [focusedField, setFocusedField] = useState<string | null>(null);
  const [errors, setErrors] = useState<FormErrors>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [clubs, setClubs] = useState<Club[]>([]);
  const [loadingClubs, setLoadingClubs] = useState(true);
  const [validatingDNI, setValidatingDNI] = useState(false);
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [successData, setSuccessData] = useState<{ nombre: string; email: string; dni: string } | null>(null);

  // Cargar clubs al montar el componente
  useEffect(() => {
    console.log('⚡ useEffect ejecutado - cargando clubs');
    loadClubs();
  }, []);

  // Mostrar modal de éxito cuando successData cambie
  useEffect(() => {
    if (successData) {
      console.log('🎉 MOSTRANDO MODAL DE ÉXITO');
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      setShowSuccessModal(true);
    }
  }, [successData]);

  // Foto de perfil (opcional)
  const [photo, setPhoto] = useState<{ uri: string; name?: string; type?: string } | null>(null);

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
        quality: 0.7, // Bajamos un poco la calidad para que suba más rápido
        aspect: [1, 1], // Forzamos formato cuadrado para perfil
      });

      if (!result.canceled && result.assets && result.assets.length > 0) {
        const asset = result.assets[0];
        const uri = asset.uri;

        // Obtener extensión y tipo MIME de forma segura
        const uriParts = uri.split('.');
        const fileType = uriParts[uriParts.length - 1];
        
        // Generar nombre único y tipo correcto
        const name = `profile_${Date.now()}.${fileType}`;
        const type = `image/${fileType === 'jpg' ? 'jpeg' : fileType}`;

        setPhoto({ uri, name, type });
        console.log('📸 Foto preparada:', { uri, name, type });
      }
    } catch (err) {
      console.error('Error al seleccionar imagen:', err);
      Alert.alert('Error', 'No se pudo seleccionar la imagen.');
    }
  };

  const handleCloseSuccessModal = () => {
    // Cerrar el modal
    setShowSuccessModal(false);

    // Limpiar estado de éxito
    setSuccessData(null);

    // Limpiar foto
    setPhoto(null);

    // Limpiar formulario
    setFormData({
      nombre: '',
      apellidos: '',
      apodo: '',
      edad: '',
      peso: '',
      altura: '',
      genero: '',
      email: '',
      telefono: '',
      countryCode: 'PE',
      dni: '',
      club_id: '',
    });

    // Navegar al Home
    navigation.navigate('Home' as never);
  };

  // Validar DNI cuando cambia (con debounce)
  useEffect(() => {
    const timeoutId = setTimeout(async () => {
      if (formData.dni.trim().length >= 8) {
        setValidatingDNI(true);
        try {
          const result = await fighterService.verificarDNI(formData.dni.trim());
          if (!result.disponible) {
            setErrors(prev => ({ ...prev, dni: 'Este DNI ya está registrado' }));
          } else {
            // Limpiar error de DNI si estaba
            setErrors(prev => {
              const newErrors = { ...prev };
              delete newErrors.dni;
              return newErrors;
            });
          }
        } catch (error) {
          console.error('Error verificando DNI:', error);
        } finally {
          setValidatingDNI(false);
        }
      }
    }, 800); // Esperar 800ms después de que el usuario deje de escribir

    return () => clearTimeout(timeoutId);
  }, [formData.dni]);

  const loadClubs = async () => {
    try {
      console.log('🔵 Iniciando carga de clubs...');
      setLoadingClubs(true);
      const clubsData = await clubService.getAll();
      console.log('🟢 Clubs cargados:', clubsData);
      setClubs(clubsData || []);
      console.log('✅ Clubs set en estado:', clubsData?.length || 0);
    } catch (error) {
      console.error('🔴 Error cargando clubs:', error);
      setClubs([]);
      Alert.alert('Error', 'No se pudieron cargar los clubs disponibles');
    } finally {
      setLoadingClubs(false);
      console.log('🏁 Carga de clubs finalizada');
    }
  };

  const updateField = (field: keyof FormData, value: string | number) => {
    let processedValue = value;

    // Validación especial para DNI: solo números y máximo 10 dígitos
    if (field === 'dni' && typeof value === 'string') {
      // Eliminar todo lo que no sea número
      processedValue = value.replace(/\D/g, '');
      // Limitar a 10 dígitos
      processedValue = processedValue.slice(0, 10);
    }

    setFormData(prev => ({ ...prev, [field]: processedValue }));
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

    // Validar nombre completo (requerido)
    if (!formData.nombre.trim()) {
      newErrors.nombre = 'El nombre completo es requerido';
    }

    // Apodo es OPCIONAL - no validamos

    // Validar email (requerido y formato)
    if (!formData.email.trim()) {
      newErrors.email = 'El email es requerido';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Email inválido';
    }

    // Validar DNI (requerido, solo números, máximo 10 dígitos)
    if (!formData.dni.trim()) {
      newErrors.dni = 'El DNI es requerido';
    } else if (!/^\d+$/.test(formData.dni.trim())) {
      newErrors.dni = 'Solo se permiten números';
    } else if (formData.dni.trim().length > 10) {
      newErrors.dni = 'Máximo 10 dígitos';
    }

    // Validar edad (requerido, 12+ años)
    if (!formData.edad.trim()) {
      newErrors.edad = 'La edad es requerida';
    } else {
      const edad = parseInt(formData.edad);
      if (isNaN(edad) || edad < 12) {
        newErrors.edad = 'Debes tener al menos 12 años';
      } else if (edad > 100) {
        newErrors.edad = 'Edad máxima: 100 años';
      }
    }

    // Validar peso (requerido, 30-140 kg)
    if (!formData.peso.trim()) {
      newErrors.peso = 'El peso es requerido';
    } else {
      const peso = parseFloat(formData.peso);
      if (isNaN(peso) || peso < 30 || peso > 140) {
        newErrors.peso = 'Peso debe estar entre 30 y 140 kg';
      }
    }

    // Validar altura (requerido, 130-220 cm)
    if (!formData.altura.trim()) {
      newErrors.altura = 'La altura es requerida';
    } else {
      const altura = parseFloat(formData.altura);
      if (isNaN(altura) || altura < 130 || altura > 220) {
        newErrors.altura = 'Altura debe estar entre 130 y 220 cm';
      }
    }

    // Validar género (requerido)
    if (!formData.genero) {
      newErrors.genero = 'Debes seleccionar tu género';
    }

    // Validar teléfono (requerido)
    if (!formData.telefono.trim()) {
      newErrors.telefono = 'El teléfono es requerido';
    } else {
      // Validación específica para Perú
      if (formData.countryCode === 'PE') {
        const phoneDigits = formData.telefono.replace(/\D/g, '');
        if (phoneDigits.length !== 9) {
          newErrors.telefono = 'Debe tener 9 dígitos';
        } else if (!phoneDigits.startsWith('9')) {
          newErrors.telefono = 'Debe empezar con 9';
        }
      }
    }

    // Validar club (requerido)
    if (!formData.club_id) {
      newErrors.club_id = 'Debes seleccionar un club';
    }

    console.log('🔍 Validation errors found:', newErrors);
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    console.log('🚀 Iniciando envío...');
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);

    const isValid = validateForm();

    if (!isValid) {
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
      Alert.alert('Campos requeridos', 'Por favor completa los campos marcados en rojo.');
      return;
    }

    setIsSubmitting(true);

    try {
      // 1. Preparar datos base
      const today = new Date();
      const birthYear = today.getFullYear() - parseInt(formData.edad);
      const fechaNacimiento = `${birthYear}-01-01`;
      const alturaMetros = parseFloat(formData.altura) / 100;

      const COUNTRY_CODES: any = { 'PE': '+51', 'AR': '+54', 'MX': '+52', 'US': '+1' };
      const dialCode = COUNTRY_CODES[formData.countryCode] || '+51';
      const fullPhone = `${dialCode}${formData.telefono.trim()}`;

      // 2. Crear FormData
      const form = new FormData();

      // Agregar campos de texto
      form.append('nombre', formData.nombre.trim());
      form.append('apellidos', formData.apellidos.trim() || '');
      form.append('email', formData.email.trim().toLowerCase());
      form.append('password', formData.dni.trim());
      form.append('telefono', fullPhone);
      form.append('apodo', formData.apodo.trim() || formData.nombre.trim().split(' ')[0]);
      form.append('fecha_nacimiento', fechaNacimiento);
      form.append('peso_actual', String(parseFloat(formData.peso)));
      form.append('altura', String(alturaMetros));
      form.append('genero', formData.genero);
      form.append('documento_identidad', formData.dni.trim());
      form.append('club_id', String(formData.club_id));
      form.append('estilo', 'fajador');
      form.append('experiencia_anos', '0');

      // 3. AGREGAR FOTO (Lógica Diferenciada Web vs Móvil)
      if (photo) {
        if (Platform.OS === 'web') {
          // --- WEB: Convertir URI a BLOB ---
          console.log('🌐 Detectado entorno WEB: Convirtiendo a Blob...');
          const response = await fetch(photo.uri);
          const blob = await response.blob();
          
          // En web, append toma 3 argumentos: nombre_campo, blob, nombre_archivo
          form.append('foto_perfil', blob, photo.name || 'foto.jpg');
        
        } else {
          // --- MÓVIL: Enviar objeto {uri, type, name} ---
          console.log('📱 Detectado entorno MÓVIL: Enviando objeto nativo...');
          form.append('foto_perfil', {
            uri: photo.uri,
            name: photo.name || 'foto.jpg',
            type: photo.type || 'image/jpeg',
          } as any);
        }
      }

      // 4. Enviar usando el servicio (que ya tiene fetch configurado)
      // Como estamos enviando FormData SIEMPRE (aunque no haya foto, es mejor ser consistente)
      const response = await fighterService.register(form);

      console.log('✅ Respuesta Servidor:', response);
      
      setIsSubmitting(false);
      setSuccessData({
        nombre: formData.nombre,
        email: formData.email,
        dni: formData.dni,
      });

    } catch (error: any) {
      console.error('❌ Error registro:', error);
      setIsSubmitting(false);
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);

      const msg = error.response?.data?.message || 'Error al conectar con el servidor.';
      Alert.alert('Error', msg);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        <FighterFormHeader />

        <View style={styles.formContainer}>
          {/* Datos Personales */}
          <FormSection icon="👤" title="DATOS PERSONALES">
            <FormInput
              label="Nombre"
              value={formData.nombre}
              onChangeText={(value) => updateField('nombre', value)}
              placeholder="Ej: Miguel"
              focused={focusedField === 'nombre'}
              onFocus={() => setFocusedField('nombre')}
              onBlur={() => setFocusedField(null)}
              error={errors.nombre}
            />

            <FormInput
              label="Apellidos (opcional)"
              value={formData.apellidos}
              onChangeText={(value) => updateField('apellidos', value)}
              placeholder="Ej: Rodríguez García"
              focused={focusedField === 'apellidos'}
              onFocus={() => setFocusedField('apellidos')}
              onBlur={() => setFocusedField(null)}
              error={errors.apellidos}
            />

            <FormInput
              label="Apodo (opcional)"
              value={formData.apodo}
              onChangeText={(value) => updateField('apodo', value)}
              placeholder='Ej: "El Trueno"'
              focused={focusedField === 'apodo'}
              onFocus={() => setFocusedField('apodo')}
              onBlur={() => setFocusedField(null)}
              icon="⚡"
            />

            <View>
              <FormInput
                label="DNI"
                value={formData.dni}
                onChangeText={(value) => updateField('dni', value)}
                placeholder="12345678"
                keyboardType="numeric"
                focused={focusedField === 'dni'}
                onFocus={() => setFocusedField('dni')}
                onBlur={() => setFocusedField(null)}
                error={errors.dni}
              />
              {validatingDNI && (
                <Text style={styles.validatingText}>Verificando DNI...</Text>
              )}
            </View>
          </FormSection>

          {/* Características Físicas */}
          <FormSection icon="💪" title="CARACTERÍSTICAS FÍSICAS">
            <FormSelect
              label="Género"
              value={formData.genero}
              onValueChange={(value) => updateField('genero', value)}
              options={[
                { label: 'Masculino', value: 'masculino' },
                { label: 'Femenino', value: 'femenino' },
              ]}
              placeholder="Selecciona tu género"
              icon="⚧"
              error={errors.genero}
            />

            <View style={styles.row}>
              <View style={styles.halfWidth}>
                <FormInput
                  label="Edad"
                  value={formData.edad}
                  onChangeText={(value) => updateField('edad', value)}
                  placeholder="25"
                  keyboardType="numeric"
                  focused={focusedField === 'edad'}
                  onFocus={() => setFocusedField('edad')}
                  onBlur={() => setFocusedField(null)}
                  error={errors.edad}
                />
              </View>
              <View style={styles.halfWidth}>
                <FormInput
                  label="Peso (kg)"
                  value={formData.peso}
                  onChangeText={(value) => updateField('peso', value)}
                  placeholder="75.5"
                  keyboardType="decimal-pad"
                  focused={focusedField === 'peso'}
                  onFocus={() => setFocusedField('peso')}
                  onBlur={() => setFocusedField(null)}
                  error={errors.peso}
                />
              </View>
            </View>

            <FormInput
              label="Altura (cm)"
              value={formData.altura}
              onChangeText={(value) => updateField('altura', value)}
              placeholder="180"
              keyboardType="numeric"
              focused={focusedField === 'altura'}
              onFocus={() => setFocusedField('altura')}
              onBlur={() => setFocusedField(null)}
              error={errors.altura}
            />
          </FormSection>

          {/* Contacto */}
          <FormSection icon="📱" title="CONTACTO">
            <FormInput
              label="Email"
              value={formData.email}
              onChangeText={(value) => updateField('email', value)}
              placeholder="peleador@ejemplo.com"
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

          {/* Foto de Perfil */}
          <FormSection icon="📷" title="FOTO DE PERFIL (OPCIONAL)">
            <View style={styles.photoContainer}>
              {photo ? (
                <Image source={{ uri: photo.uri }} style={styles.photoPreview} />
              ) : (
                <View style={styles.photoPlaceholder}>
                  <Text style={styles.photoPlaceholderText}>Sin foto</Text>
                </View>
              )}

              <TouchableOpacity onPress={pickImage} style={styles.photoButton}>
                <Text style={styles.photoButtonText}>{photo ? 'Cambiar foto' : 'Seleccionar foto'}</Text>
              </TouchableOpacity>

              {photo && (
                <TouchableOpacity onPress={() => setPhoto(null)} style={styles.removePhotoButton}>
                  <Text style={styles.removePhotoButtonText}>Eliminar foto</Text>
                </TouchableOpacity>
              )}

              <Text style={styles.photoHintText}>Formatos: JPG/PNG/WebP — Máx 5MB</Text>
            </View>
          </FormSection>

          {/* Club */}
          <FormSection icon="🥊" title="CLUB / GIMNASIO">
            {loadingClubs ? (
              <View style={styles.loadingContainer}>
                <ActivityIndicator size="large" color={COLORS.primary} />
                <Text style={styles.loadingText}>Cargando clubs...</Text>
              </View>
            ) : (
              <FormSelect
                label="Selecciona tu club"
                value={formData.club_id}
                onValueChange={(value) => updateField('club_id', value)}
                options={(clubs || []).map(club => ({
                  label: club.nombre,
                  value: club.id
                }))}
                placeholder="Elige tu club o gimnasio"
                icon="🏛️"
                error={errors.club_id}
              />
            )}
          </FormSection>

          <SubmitButton
            onPress={handleSubmit}
            isLoading={isSubmitting}
          />

          <SponsorFooter />
        </View>
      </ScrollView>

      {/* Modal de éxito */}
      <SuccessModal
        visible={showSuccessModal}
        title="¡Registro Exitoso! 🥊"
        message={
          successData
            ? `¡Bienvenido ${successData.nombre}!\n\n` +
              `Tu cuenta ha sido creada exitosamente.\n\n` +
              `📧 Email: ${successData.email}\n` +
              `🔑 Tu contraseña es tu DNI: ${successData.dni}\n\n` +
              `Podrás completar tu perfil de peleador más adelante.\n\n` +
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
    paddingBottom: SPACING.xxl,
  },
  formContainer: {
    paddingHorizontal: SPACING.lg,
  },
  row: {
    flexDirection: 'row',
    gap: SPACING.md,
  },
  halfWidth: {
    flex: 1,
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
  validatingText: {
    marginTop: -SPACING.md,
    marginBottom: SPACING.md,
    fontSize: 12,
    color: COLORS.primary,
    fontStyle: 'italic',
    paddingLeft: SPACING.xs,
  },
  photoContainer: {
    alignItems: 'center',
    gap: SPACING.md,
    paddingVertical: SPACING.md,
  },
  photoPreview: {
    width: 96,
    height: 96,
    borderRadius: 48,
    marginBottom: SPACING.xs,
  },
  photoPlaceholder: {
    width: 96,
    height: 96,
    borderRadius: 48,
    backgroundColor: COLORS.surface,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: SPACING.xs,
  },
  photoPlaceholderText: {
    color: COLORS.text.secondary,
    fontSize: 12,
  },
  photoButton: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.md,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    minWidth: 150,
  },
  photoButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  removePhotoButton: {
    backgroundColor: 'transparent',
    borderWidth: 1,
    borderColor: COLORS.error || '#EF4444',
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.sm,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    minWidth: 150,
  },
  removePhotoButtonText: {
    color: COLORS.error || '#EF4444',
    fontSize: 13,
    fontWeight: '500',
  },
  photoHintText: {
    fontSize: 12,
    color: COLORS.text.secondary,
    marginTop: SPACING.xs,
    textAlign: 'center',
  },
});
