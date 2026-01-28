import React, { useState, useEffect } from 'react';
import {
  View,
  StyleSheet,
  SafeAreaView,
  StatusBar,
  ScrollView,
  Alert,
  Text,
  TouchableOpacity,
} from 'react-native';
import * as Haptics from 'expo-haptics';
import { useNavigation } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../constants/theme';
import { FormSection } from '../components/form/FormSection';
import { FormInput } from '../components/form/FormInput';
import { SubmitButton } from '../components/form/SubmitButton';
import { SuccessModal } from '../components/SuccessModal';
import { ErrorModal } from '../components/ErrorModal';
import { ScreenHeader } from '../components/common/ScreenHeader';
import api from '../services/api';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface FormData {
  email: string;
  password: string;
}

interface FormErrors {
  [key: string]: string;
}

export default function LoginScreen() {
  const navigation = useNavigation();

  const [formData, setFormData] = useState<FormData>({
    email: '',
    password: '',
  });

  const [focusedField, setFocusedField] = useState<string | null>(null);
  const [errors, setErrors] = useState<FormErrors>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [showErrorModal, setShowErrorModal] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [successData, setSuccessData] = useState<{ nombre: string } | null>(null);

  useEffect(() => {
    if (successData) {
      console.log('🎉 MOSTRANDO MODAL DE ÉXITO DE LOGIN');
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      setShowSuccessModal(true);
    }
  }, [successData]);

  const handleCloseSuccessModal = () => {
    setShowSuccessModal(false);
    setSuccessData(null);
    setFormData({ email: '', password: '' });
    navigation.navigate('Profile' as never);
  };

  const updateField = (field: keyof FormData, value: string) => {
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

    if (!formData.email.trim()) {
      newErrors.email = 'El email es requerido';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Email inválido';
    }

    if (!formData.password) {
      newErrors.password = 'La contraseña es requerida';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleLogin = async () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);

    if (!validateForm()) {
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
      return;
    }

    setIsSubmitting(true);

    try {
      const loginData = {
        email: formData.email.trim().toLowerCase(),
        password: formData.password,
      };

      console.log('📤 Enviando login:', loginData);
      const response = await api.post('/usuarios/login', loginData);
      console.log('📥 Respuesta completa:', response);
      console.log('📥 Response data:', response.data);

      // Verificar que la respuesta tenga los datos correctos
      if (!response.data || !response.data.usuario) {
        throw new Error('Respuesta del servidor inválida');
      }

      const usuario = response.data.usuario;
      const token = response.data.token;

      console.log('✅ Usuario recibido:', usuario);
      console.log('✅ Token recibido:', token);

      // Guardar datos del usuario en AsyncStorage
      await AsyncStorage.setItem('user', JSON.stringify(usuario));
      await AsyncStorage.setItem('token', token || 'authenticated');

      setIsSubmitting(false);

      // Activar modal de éxito
      setSuccessData({
        nombre: usuario.nombre,
      });
    } catch (error: any) {
      console.error('Error al iniciar sesión:', error);
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);

      let errorMessage = 'Ocurrió un error al iniciar sesión.';

      if (error.response) {
        if (error.response.status === 401) {
          errorMessage = 'Email o contraseña incorrectos.';
        } else if (error.response.data?.message) {
          errorMessage = error.response.data.message;
        }
      } else if (error.request) {
        errorMessage = 'No se pudo conectar con el servidor.';
      }

      setErrorMessage(errorMessage);
      setShowErrorModal(true);
      // Alert.alert('Error de Inicio de Sesión', errorMessage, [{ text: 'OK' }]);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#000" />
      <ScreenHeader title="INICIAR SESIÓN" />

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        <View style={styles.loginContent}>
          <Ionicons name="log-in" size={64} color={COLORS.primary} style={styles.loginIcon} />
        </View>

        <View style={styles.formContainer}>
          {/* Credenciales */}
          <FormSection icon="🔐" title="CREDENCIALES">
            <FormInput
              label="Email"
              value={formData.email}
              onChangeText={(value) => updateField('email', value)}
              placeholder="tu@email.com"
              keyboardType="email-address"
              autoCapitalize="none"
              focused={focusedField === 'email'}
              onFocus={() => setFocusedField('email')}
              onBlur={() => setFocusedField(null)}
              error={errors.email}
            />

            <FormInput
              label="Contraseña"
              value={formData.password}
              onChangeText={(value) => updateField('password', value)}
              placeholder="Tu contraseña"
              focused={focusedField === 'password'}
              onFocus={() => setFocusedField('password')}
              onBlur={() => setFocusedField(null)}
              error={errors.password}
            />
          </FormSection>

          <SubmitButton
            onPress={handleLogin}
            isLoading={isSubmitting}
            title="INICIAR SESIÓN"
            icon="🔐"
          />

          {/* Botón de registro */}
          <View style={styles.registerSection}>
            <Text style={styles.registerText}>¿No tienes cuenta?</Text>
            <TouchableOpacity
              onPress={() => navigation.navigate('RegisterUser' as never)}
              style={styles.registerButton}
            >
              <Text style={styles.registerButtonText}>CREAR CUENTA</Text>
              <Ionicons name="arrow-forward" size={20} color={COLORS.primary} />
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>

      {/* Modal de éxito */}
      <SuccessModal
        visible={showSuccessModal}
        title="¡Bienvenido! 👋"
        message={successData ? `Hola ${successData.nombre}!\n\nHas iniciado sesión exitosamente.` : ''}
        buttonText="Ir a Mi Perfil"
        onClose={handleCloseSuccessModal}
      />

      <ErrorModal
        visible={showErrorModal}
        title="Error de Acceso"
        message={errorMessage}
        onClose={() => setShowErrorModal(false)}
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
    paddingBottom: 130, // Aumentado por solicitud
  },
  loginContent: {
    alignItems: 'center',
    paddingVertical: SPACING.xl,
  },
  loginIcon: {
    marginBottom: SPACING.md,
  },
  formContainer: {
    paddingHorizontal: SPACING.lg,
  },
  registerSection: {
    marginTop: SPACING.xl,
    padding: SPACING.lg,
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.md,
    alignItems: 'center',
    gap: SPACING.sm,
  },
  registerText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
  },
  registerButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.xs,
    paddingVertical: SPACING.sm,
  },
  registerButtonText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    letterSpacing: 0.5,
  },
});
