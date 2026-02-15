import React, { useState, useEffect, useRef } from 'react';
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
    LayoutChangeEvent,
    Modal,

    KeyboardAvoidingView, // Deprecated but keeping ensuring type safety if referenced elsewhere
} from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-aware-scroll-view';
import { Ionicons } from '@expo/vector-icons';
// CDN Usage via window.imglyBackgroundRemoval
import * as ImagePicker from 'expo-image-picker';
import * as Haptics from 'expo-haptics';
import { useAudioPlayer } from 'expo-audio';
import { useNavigation, CommonActions } from '@react-navigation/native';
import { COLORS, SPACING, BORDER_RADIUS } from '../constants/theme';
import { createShadow } from '../utils/shadows';
import { ScreenHeader } from '../components/common/ScreenHeader';
import { FormSection } from '../components/form/FormSection';
import { FormInput } from '../components/form/FormInput';
import { FormSelect } from '../components/form/FormSelect';
import { PhoneInput } from '../components/form/PhoneInput';

import { EpicFighterSuccessModal } from '../components/EpicFighterSuccessModal';
import { FighterIdentityModal } from '../components/FighterIdentityModal';
import { FighterCard } from '../components/common/FighterCard';
import { ClubSelector } from '../components/form/ClubSelector';
import { fighterService } from '../services/fighterService';
import { clubService, Club } from '../services/clubService';
import { bannerService } from '../services/bannerService';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api from '../services/api';

import { generateDebugFighter } from '../data/dummyFighters';

const SHOW_DEBUG_GENERATOR = true; // 🪄 MODO DEBUG: PONER EN FALSE PARA DESACTIVAR

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

    const navigation = useNavigation();
    const scrollViewRef = useRef<KeyboardAwareScrollView>(null);
    const fieldPositions = useRef<{ [key: string]: number }>({});
    const sectionPositions = useRef<{ [key: string]: number }>({});

    const handleFieldLayout = (name: string) => (event: LayoutChangeEvent) => {
        fieldPositions.current[name] = event.nativeEvent.layout.y;
    };

    const handleSectionLayout = (name: string) => (event: LayoutChangeEvent) => {
        sectionPositions.current[name] = event.nativeEvent.layout.y;
    };

    // Mapeo de campos a sus secciones correspondientes para scroll inteligente
    const fieldToSection: { [key: string]: string } = {
        nombre: 'datos_personales',
        apellidos: 'datos_personales',
        dni: 'datos_personales',
        email: 'contacto',
        telefono: 'contacto',
        apodo: 'caracteristicas',
        genero: 'caracteristicas',
        edad: 'caracteristicas',
        peso: 'caracteristicas',
        altura: 'caracteristicas',
        club_id: 'club'
    };

    const [formData, setFormData] = useState<FormData>({
        nombre: '',
        apellidos: '',
        apodo: '',
        edad: '',
        peso: '',
        altura: '',
        genero: 'masculino', // Default
        email: '',
        telefono: '',
        countryCode: 'PE',
        dni: '',
        club_id: '',
    });

    const [focusedField, setFocusedField] = useState<string | null>(null);
    const [errors, setErrors] = useState<FormErrors>({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [clubs, setClubs] = useState<Club[]>([]);
    const [loadingClubs, setLoadingClubs] = useState(true);
    const [validatingDNI, setValidatingDNI] = useState(false);
    const [validatingEmail, setValidatingEmail] = useState(false);
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [successData, setSuccessData] = useState<{
        nombre: string;
        apellidos: string;
        apodo: string;
        peso: string;
        genero: string;
        email: string;
        dni: string;
        photoUri?: string | null;
        edad?: string;
        altura?: string;
        clubName?: string;
    } | null>(null);
    const [isAutoLoggedIn, setIsAutoLoggedIn] = useState(false);
    const [isAlreadyAuth, setIsAlreadyAuth] = useState(false);
    const [existingFighter, setExistingFighter] = useState<any>(null);
    const [checkingAuth, setCheckingAuth] = useState(true);
    const [currentStep, setCurrentStep] = useState(1);
    const [showIdentityModal, setShowIdentityModal] = useState(false);
    const [imageUploadMode, setImageUploadMode] = useState<'profile' | 'background'>('background');
    const totalSteps = 3;

    // Banner handling
    const [banners, setBanners] = useState<any[]>([]);
    const [currentBannerIndex, setCurrentBannerIndex] = useState(0);

    // Audio Players (expo-audio)
    const punch3Player = useAudioPlayer(require('../../assets/sounds/punch-03.mp3'));
    const punch4Player = useAudioPlayer(require('../../assets/sounds/punch-04.mp3'));
    const bell3Player = useAudioPlayer(require('../../assets/sounds/bell-03.mp3'));

    // Cargar clubs y banners al montar el componente
    useEffect(() => {
        loadClubs();
        checkAuthStatus();
        loadBanners();
    }, []);

    // Rotación de banners
    useEffect(() => {
        if (banners.length <= 1) return;

        const interval = setInterval(() => {
            setCurrentBannerIndex(prev => (prev + 1) % banners.length);
        }, 5000);

        return () => clearInterval(interval);
    }, [banners]);

    const loadBanners = async () => {
        try {
            // Usamos require dinámico o el servicio si ya está importado. 
            // Asumiremos que importaremos bannerService arriba.
            const data = await bannerService.getAll(false);
            setBanners(data);
        } catch (error) {
            console.log('Error loading banners for preview:', error);
        }
    };

    // Mostrar modal cuando se detecta un peleador existente
    useEffect(() => {
        if (existingFighter) {
            setShowIdentityModal(true);
        }
    }, [existingFighter]);

    const checkAuthStatus = async () => {
        try {
            const token = await AsyncStorage.getItem('token');
            const userStr = await AsyncStorage.getItem('user');

            if (token && userStr) {
                setIsAlreadyAuth(true);
                const user = JSON.parse(userStr);

                // Verificar si ya es peleador
                try {
                    const fighterData = await fighterService.getByUserId(user.id);
                    if (fighterData) {
                        console.log('🥊 Usuario ya es peleador:', fighterData);
                        setExistingFighter(fighterData);
                    }
                } catch (err) {
                    // Si da error 404 es que no es peleador, permitimos registro
                    console.log('ℹ️ Usuario logueado pero no es peleador aún');
                }
            }
        } catch (error) {
            console.error('Error verificando auth:', error);
        } finally {
            setCheckingAuth(false);
        }
    };

    // Mostrar modal de éxito cuando successData cambie
    useEffect(() => {
        if (successData) {
            console.log('🎉 MOSTRANDO MODAL DE ÉXITO');
            playSound('bell3');
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
            setShowSuccessModal(true);
        }
    }, [successData]);

    // Foto de perfil (opcional)
    const [photo, setPhoto] = useState<{ uri: string; name?: string; type?: string } | null>(null);
    const [cardPhoto, setCardPhoto] = useState<{ uri: string; name?: string; type?: string } | null>(null);
    const [showImageOptions, setShowImageOptions] = useState(false);
    const [uploadType, setUploadType] = useState<'profile' | 'card'>('profile');
    const [bgOffsetY, setBgOffsetY] = useState(0);
    const [bgOffsetX, setBgOffsetX] = useState(0);
    const [bgScale, setBgScale] = useState(1);
    const [isRemovingBg, setIsRemovingBg] = useState(false);



    const [isLibReady, setIsLibReady] = useState(false);

    // Inject CDN script logic
    useEffect(() => {
        if (Platform.OS === 'web') {
            const scriptId = 'imgly-bg-removal-cdn';

            const checkReady = () => {
                if ((window as any).imglyBackgroundRemoval) {
                    setIsLibReady(true);
                    return true;
                }
                return false;
            };

            if (document.getElementById(scriptId)) {
                checkReady();
            } else {
                const script = document.createElement('script');
                script.id = scriptId;
                script.type = 'module';
                // Use esm.sh for browser-ready ESM bundle
                script.innerHTML = `
          import { removeBackground } from 'https://esm.sh/@imgly/background-removal@1.7.0';
          window.imglyBackgroundRemoval = { removeBackground };
          document.dispatchEvent(new Event('imgly-ready'));
        `;
                document.body.appendChild(script);

                // Listen for the custom event we just dispatched from inside the module
                document.addEventListener('imgly-ready', () => {
                    console.log("IA Library Loaded via ESM");
                    checkReady();
                }, { once: true });
            }
        }
    }, []);


    // NEW: Handle background removal logic
    const handleRemoveBackground = async () => {
        if (!cardPhoto?.uri) return;
        setIsRemovingBg(true);
        try {
            // Use CDN global
            const imgly = (window as any).imglyBackgroundRemoval;
            if (!imgly) throw new Error("Librería IA no cargada. Revisa tu conexión.");

            const imageBlob = await imgly.removeBackground(cardPhoto.uri, {
                debug: true,
                progress: (key: string, current: number, total: number) => {
                    console.log(`Downloading ${key}: ${current} of ${total}`);
                }
            });

            const url = URL.createObjectURL(imageBlob);
            setCardPhoto({ uri: url, name: 'bg-removed.png', type: 'image/png' });
        } catch (e: any) {
            console.error("BG REMOVAL ERROR:", e);
            alert(`Error: ${e.message || 'Desconocido'}`);
        } finally {
            setIsRemovingBg(false);
        }
    };

    // NEW: Handle background selection logic (used by modal)
    const handleBackgroundSelected = async (uri: string) => {
        // Logic to handle background selection
        setCardPhoto({ uri });
        // Deselect all banners since we are using a custom photo
        setBanners(prev => prev.map(b => ({ ...b, selected: false })));
        // Scroll to top of card preview
        scrollViewRef.current?.scrollToPosition(0, 0, true);
    };

    const processImageResult = (result: ImagePicker.ImagePickerResult, type: 'profile' | 'card') => {
        // ... Legacy logic kept for reference but mostly unused now by launchCamera directly ...
    };

    const launchCamera = async () => {
        try {
            // 1. Pedir permisos
            const { status } = await ImagePicker.requestCameraPermissionsAsync();
            if (status !== 'granted') {
                Alert.alert('Permiso denegado', 'Necesitamos acceso a tu cámara para tomar la foto.');
                return;
            }

            // For profile mode: allow editing (square crop might be nice but native camera is fine)
            // For background mode: horizontal preference
            const result = await ImagePicker.launchCameraAsync({
                mediaTypes: ImagePicker.MediaTypeOptions.Images,
                allowsEditing: imageUploadMode === 'background', // Enable editing only for background
                aspect: imageUploadMode === 'background' ? [22, 10] : undefined, // 2.2 Ratio for background card
                quality: 0.8,
            });

            if (!result.canceled && result.assets[0].uri) {
                if (imageUploadMode === 'background') {
                    await handleBackgroundSelected(result.assets[0].uri);
                } else {
                    // Logic for profile photo
                    setPhoto({ uri: result.assets[0].uri });
                    setBanners(prev => prev.map(b => ({ ...b, selected: false }))); // Deselect banners
                }
                setShowImageOptions(false);
            }
        } catch (error) {
            console.error('Error launching camera:', error);
            Alert.alert('Error', 'No pudimos abrir la cámara.');
        }
    };

    const launchGallery = async () => {
        try {
            // 1. Pedir permisos
            const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
            if (status !== 'granted') {
                Alert.alert('Permiso denegado', 'Necesitamos acceso a tu galería.');
                return;
            }

            const result = await ImagePicker.launchImageLibraryAsync({
                mediaTypes: ImagePicker.MediaTypeOptions.Images,
                allowsEditing: imageUploadMode === 'background',
                aspect: imageUploadMode === 'background' ? [22, 10] : undefined,
                quality: 0.8,
            });

            if (!result.canceled && result.assets[0].uri) {
                if (imageUploadMode === 'background') {
                    await handleBackgroundSelected(result.assets[0].uri);
                } else {
                    // Logic for profile photo
                    setPhoto({ uri: result.assets[0].uri });
                    setBanners(prev => prev.map(b => ({ ...b, selected: false })));
                }
                setShowImageOptions(false);
            }
        } catch (error) {
            console.error('Error opening gallery:', error);
            Alert.alert('Error', 'No pudimos abrir la galería.');
        }
    };

    // TRIGGER FOR PROFILE PHOTO (Steps 1)
    const pickProfilePhoto = () => {
        setImageUploadMode('profile');
        setShowImageOptions(true);
    };

    // TRIGGER FOR BACKGROUND PHOTO (Steps 3)
    const pickCardBackground = () => {
        setImageUploadMode('background');
        setShowImageOptions(true);
    };

    const handleCloseSuccessModal = () => {
        // Cerrar el modal
        setShowSuccessModal(false);

        // Limpiar estado de éxito
        setSuccessData(null);

        // Limpiar foto
        setPhoto(null);
        setCardPhoto(null);

        // Si ya estaba logueado, solo cerramos y nos quedamos aquí (o el usuario navegará manualmente)
        if (isAlreadyAuth) {
            console.log('👤 Usuario ya autenticado, registro completado. Modal cerrado.');
            // Opcional: Resetear formulario o navegar a Home, pero el requerimiento dice "se cierra el modal"
            // Reseteamos el form por si quiere registrar otro (aunque raro) o para dejarlo limpio.
            setFormData(prev => ({
                nombre: '', apellidos: '', apodo: '', edad: '', peso: '', altura: '',
                genero: 'masculino', email: '', telefono: '', countryCode: 'PE', dni: '',
                club_id: clubs.find(c => c.nombre.toLowerCase().includes('independiente'))?.id || '',
            }));
            // Reset steps
            setCurrentStep(1);
            return;
        }

        // Limpiar formulario y defaults
        setFormData(prev => ({
            nombre: '', apellidos: '', apodo: '', edad: '', peso: '', altura: '',
            genero: 'masculino', email: '', telefono: '', countryCode: 'PE', dni: '',
            club_id: clubs.find(c => c.nombre.toLowerCase().includes('independiente'))?.id || '',
        }));

        // Navegar según el resultado del auto-login (Solo usuarios nuevos)
        if (isAutoLoggedIn) {
            navigation.navigate('Profile' as never);
        } else {
            navigation.navigate('Login' as never);
        }

        setIsAutoLoggedIn(false);
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

    // Reproducir sonidos
    // Reproducir sonidos
    const playSound = (soundName: 'punch3' | 'punch4' | 'bell3') => {
        try {
            if (soundName === 'punch3') {
                punch3Player.seekTo(0);
                punch3Player.play();
            } else if (soundName === 'punch4') {
                punch4Player.seekTo(0);
                punch4Player.play();
            } else if (soundName === 'bell3') {
                bell3Player.seekTo(0);
                bell3Player.play();
            }
        } catch (error) {
            console.log('Error playing sound:', error);
        }
    };

    // Debounce para validación de Email en tiempo real
    useEffect(() => {
        const timeoutId = setTimeout(async () => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailRegex.test(formData.email)) {
                setValidatingEmail(true);
                try {
                    const result = await fighterService.verificarEmail(formData.email);


                    if (!result.disponible) {
                        setErrors(prev => ({ ...prev, email: 'Este email ya está registrado' }));
                    } else {
                        setErrors(prev => {
                            const newErrors = { ...prev };
                            delete newErrors.email;
                            return newErrors;
                        });
                    }
                } catch (error) {
                    console.error('Error verificando Email:', error);
                } finally {
                    setValidatingEmail(false);
                }
            }
        }, 800);

        return () => clearTimeout(timeoutId);
    }, [formData.email]);

    const loadClubs = async () => {
        try {
            console.log('🔵 Iniciando carga de clubs...');
            setLoadingClubs(true);
            const clubsData = await clubService.getAll();
            console.log('🟢 Clubs cargados:', clubsData);
            setClubs(clubsData || []);

            // Auto-seleccionar "Independiente" por defecto
            if (clubsData) {
                const independiente = clubsData.find(c => c.nombre.toLowerCase().includes('independiente'));
                if (independiente && !formData.club_id) {
                    console.log('✅ Auto-seleccionando club Independiente:', independiente.id);
                    setFormData(prev => ({ ...prev, club_id: independiente.id }));
                }
            }
        } catch (error) {
            console.error('🔴 Error cargando clubs:', error);
            setClubs([]);
            Alert.alert('Error', 'No se pudieron cargar los clubs disponibles');
        } finally {
            setLoadingClubs(false);
        }
    };

    const updateField = (field: keyof FormData, value: string | number) => {
        let processedValue = value;

        // Validación especial para DNI: solo números y máximo 8 dígitos
        if (field === 'dni' && typeof value === 'string') {
            processedValue = value.replace(/\D/g, '').slice(0, 8);
        }

        // Validación especial para Nombres y Apellidos: no permitir números
        if ((field === 'nombre' || field === 'apellidos') && typeof value === 'string') {
            processedValue = value.replace(/[0-9]/g, '');
        }

        // Validación especial para Teléfono: auto-corrección a '9'
        if (field === 'telefono' && typeof value === 'string') {
            const numericValue = value.replace(/\D/g, '').slice(0, 9);
            if (numericValue.length > 1 && !numericValue.startsWith('9')) {
                processedValue = '9';
            } else {
                processedValue = numericValue;
            }
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

    const validateStep = (step: number): boolean => {
        // Bloquear si se está validando (Race condition fix)
        if (validatingEmail || validatingDNI) {
            Alert.alert('Espere un momento', 'Estamos verificando sus datos...');
            return false;
        }

        const newErrors: FormErrors = {};
        const missingFields: string[] = [];

        if (step === 1) {
            if (!isFieldValid('nombre') || formData.nombre.trim().length < 3) {
                newErrors.nombre = getFieldError('nombre') || 'Mínimo 3 letras';
                missingFields.push('Nombre (mín. 3 letras)');
            }
            if (!isFieldValid('apellidos')) {
                newErrors.apellidos = getFieldError('apellidos') || 'Apellido requerido';
                missingFields.push('Apellidos (mín. 3 letras)');
            }
            if (!isFieldValid('dni')) {
                newErrors.dni = getFieldError('dni') || 'DNI inválido';
                missingFields.push('DNI (8 dígitos)');
            }
            if (!isFieldValid('email')) {
                newErrors.email = getFieldError('email') || 'Email inválido';
                missingFields.push('Email correcto');
            } else if (errors.email) {
                // Si hay error de "ya registrado"
                newErrors.email = errors.email;
                missingFields.push('Email ya registrado');
            }
            if (!isFieldValid('telefono')) {
                newErrors.telefono = getFieldError('telefono') || 'Teléfono inválido';
                missingFields.push('Teléfono (9 dígitos)');
            }
        }

        if (step === 2) {
            if (!isFieldValid('genero')) {
                newErrors.genero = 'Selecciona género';
                missingFields.push('Género');
            }
            if (!isFieldValid('edad')) {
                const value = formData.edad.trim();
                newErrors.edad = value ? (getFieldError('edad') || 'Edad inválida') : 'Ingresa tu edad';
                missingFields.push('Edad');
            }
            if (!isFieldValid('peso')) {
                const value = formData.peso.trim();
                newErrors.peso = value ? (getFieldError('peso') || 'Peso inválido') : 'Ingresa tu peso';
                missingFields.push('Peso');
            }
            if (!isFieldValid('altura')) {
                const value = formData.altura.trim();
                newErrors.altura = value ? (getFieldError('altura') || 'Altura inválida') : 'Ingresa tu altura';
                missingFields.push('Altura');
            }
        }

        if (step === 3) {
            if (!isFieldValid('club_id')) {
                newErrors.club_id = 'Selecciona club';
                missingFields.push('Club');
            }
        }

        setErrors(newErrors);

        if (missingFields.length > 0) {
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);

            const errorKeys = Object.keys(newErrors);
            if (errorKeys.length > 0) {
                const firstErrorKey = errorKeys[0];
                const sectionKey = fieldToSection[firstErrorKey];

                // Si el campo tiene una sección asociada, scrolleamos a la sección
                // Si no (caso raro), scrolleamos al campo individual
                const yPos = sectionKey
                    ? (sectionPositions.current[sectionKey] || 0)
                    : (fieldPositions.current[firstErrorKey] || 0);

                scrollViewRef.current?.scrollToPosition(0, Math.max(0, yPos - 0), true);
            }

            return false;
        }

        return true;
    };

    const handleNext = () => {
        if (validateStep(currentStep)) {
            if (currentStep < totalSteps) {
                // Sonidos por paso
                if (currentStep === 1) playSound('punch3');
                if (currentStep === 2) playSound('punch4');

                const nextStep = currentStep + 1;
                setCurrentStep(nextStep);
                Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
                // Si vamos al paso 3 (Card), scrollear al inicio (0) para verlo bien
                scrollViewRef.current?.scrollToPosition(0, nextStep === 3 ? 0 : 255, true);
            } else {
                handleSubmit();
            }
        }
    };

    const handleBack = () => {
        if (currentStep > 1) {
            const prevStep = currentStep - 1;
            setCurrentStep(prevStep);
            Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
            // Si volvemos al paso 3 (Card), scrollear al inicio (0)
            scrollViewRef.current?.scrollToPosition(0, prevStep === 3 ? 0 : 255, true);
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

        // Validar DNI (requerido, solo números, exactamente 8 dígitos)
        if (!formData.dni.trim()) {
            newErrors.dni = 'El DNI es requerido';
        } else if (!/^\d+$/.test(formData.dni.trim())) {
            newErrors.dni = 'Solo se permiten números';
        } else if (formData.dni.trim().length !== 8) {
            newErrors.dni = 'Debe tener 8 dígitos';
        }

        // Validar edad (requerido, 10-60 años)
        if (!formData.edad.trim()) {
            newErrors.edad = 'La edad es requerida';
        } else {
            const edad = parseInt(formData.edad);
            if (isNaN(edad) || edad < 10) {
                newErrors.edad = 'Debes tener al menos 10 años';
            } else if (edad > 60) {
                newErrors.edad = 'Edad máxima: 60 años';
            }
        }

        // Validar peso (requerido, 40-140 kg)
        if (!formData.peso.trim()) {
            newErrors.peso = 'El peso es requerido';
        } else {
            const peso = parseFloat(formData.peso);
            if (isNaN(peso) || peso < 40 || peso > 140) {
                newErrors.peso = 'Peso debe estar entre 40 y 140 kg';
            }
        }

        // Validar altura (requerido, 100-210 cm)
        if (!formData.altura.trim()) {
            newErrors.altura = 'La altura es requerida';
        } else {
            const altura = parseFloat(formData.altura);
            if (isNaN(altura) || altura < 100 || altura > 210) {
                newErrors.altura = 'Altura debe estar entre 100 y 210 cm';
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
        // 1. Validar el paso actual (Step 3)
        if (!validateStep(currentStep)) return;

        // 2. Validar TODO el formulario
        if (!validateForm()) {
            // Encontrar el primer error y navegar a ese paso
            const firstErrorKey = Object.keys(errors)[0] as keyof FormData;

            // Mapeo de campos a pasos
            const fieldToStep: Record<string, number> = {
                'nombre': 1, 'apellidos': 1, 'dni': 1, 'email': 1, 'telefono': 1,
                'genero': 2, 'edad': 2, 'peso': 2, 'altura': 2, 'apodo': 2,
                'club_id': 3
            };

            const targetStep = fieldToStep[firstErrorKey] || 1;

            if (targetStep !== currentStep) {
                setCurrentStep(targetStep);
                // Pequeño delay para que el render del paso ocurra antes de scrollear
                setTimeout(() => {
                    // Intentar scrollear al error (usando lógica de Sección igual que validateStep)
                    const sectionKey = fieldToSection[firstErrorKey];
                    const yPos = sectionKey
                        ? (sectionPositions.current[sectionKey] || 0)
                        : (fieldPositions.current[firstErrorKey] || 0);

                    scrollViewRef.current?.scrollToPosition(0, Math.max(0, yPos - 0), true);
                }, 300);
            }

            Alert.alert('Datos Incompletos', `Hay un error en el Paso ${targetStep} (${firstErrorKey}). Por favor corrígelo.`);
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
            return;
        }

        console.log('🚀 Iniciando envío...');
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

            // 3. AGREGAR FOTO DE PERFIL (Lógica Diferenciada Web vs Móvil)
            if (photo) {
                // En Web, usamos fetch para obtener el blob
                if (Platform.OS === 'web') {
                    try {
                        const response = await fetch(photo.uri);
                        const blob = await response.blob();
                        form.append('foto_perfil', blob, photo.name || 'foto.jpg');
                    } catch (blobErr) {
                        console.error("Error blob web:", blobErr);
                    }
                } else {
                    form.append('foto_perfil', {
                        uri: photo.uri,
                        name: photo.name || 'foto.jpg',
                        type: photo.type || 'image/jpeg',
                    } as any);
                }
            }

            // 4. AGREGAR FOTO DE FONDO (CARD)
            if (cardPhoto) {
                if (Platform.OS === 'web') {
                    try {
                        const response = await fetch(cardPhoto.uri);
                        const blob = await response.blob();
                        form.append('foto_background', blob, cardPhoto.name || 'card_bg.jpg');
                    } catch (blobErr) {
                        console.error("Error blob web card:", blobErr);
                    }
                } else {
                    form.append('foto_background', {
                        uri: cardPhoto.uri,
                        name: cardPhoto.name || 'card_bg.jpg',
                        type: cardPhoto.type || 'image/jpeg',
                    } as any);
                }
            }

            // 5. Enviar usando el servicio
            const response = await fighterService.register(form);

            console.log('✅ Respuesta Servidor:', response);

            // Intentar Auto-Login SOLO SI NO ESTABA YA LOGUEADO
            if (!isAlreadyAuth) {
                try {
                    console.log('🔐 Intentando auto-login...');
                    const loginResponse = await api.post('/usuarios/login', {
                        email: formData.email.trim().toLowerCase(),
                        password: formData.dni.trim()
                    });

                    if (loginResponse.data?.token && loginResponse.data?.usuario) {
                        await AsyncStorage.setItem('user', JSON.stringify(loginResponse.data.usuario));
                        await AsyncStorage.setItem('token', loginResponse.data.token);
                        setIsAutoLoggedIn(true);
                        console.log('✅ Auto-login exitoso');
                    }
                } catch (loginErr) {
                    console.warn('⚠️ Auto-login falló:', loginErr);
                    setIsAutoLoggedIn(false);
                }
            } else {
                console.log('ℹ️ Usuario ya estaba logueado, saltando auto-login.');
            }

            setIsSubmitting(false);
            setSuccessData({
                nombre: formData.nombre,
                apellidos: formData.apellidos,
                apodo: formData.apodo,
                peso: formData.peso,
                genero: formData.genero,
                email: formData.email,
                dni: formData.dni,
                photoUri: cardPhoto?.uri || photo?.uri, // Prioritize card photo for the card background
                edad: formData.edad,
                altura: formData.altura,
                clubName: clubs?.find(c => c.id === formData.club_id)?.nombre
            });

        } catch (error: any) {
            console.error('❌ Error registro:', error);
            setIsSubmitting(false);
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);

            const msg = error.response?.data?.message || 'Error al conectar con el servidor.';
            Alert.alert('Error', msg);
        }
    };

    const toTitleCase = (str: string) => {
        return str.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    };

    const handleBlurField = (field: keyof FormData) => {
        setFocusedField(null);
        if ((field === 'nombre' || field === 'apellidos') && formData[field]) {
            const formattedValue = toTitleCase(formData[field].toString());
            setFormData(prev => ({ ...prev, [field]: formattedValue }));
        }
    };

    // Validaciones en tiempo real para el check azul
    const isFieldValid = (field: keyof FormData): boolean => {
        const value = formData[field]?.toString().trim() || '';

        // Si el valor está vacío, solo es válido si el campo es opcional
        if (!value) {
            if (field === 'apodo') return true;
            return false;
        }

        switch (field) {
            case 'nombre':
                return value.length >= 3 && !/\d/.test(value);
            case 'apellidos':
                return value.length >= 3 && !/\d/.test(value);
            case 'dni':
                return value.length === 8 && /^\d+$/.test(value) && !errors.dni;
            case 'email':
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) && !errors.email;
            case 'telefono':
                const phoneDigits = value.replace(/\D/g, '');
                return phoneDigits.length === 9 && phoneDigits.startsWith('9');
            case 'genero':
                return !!value;
            case 'edad':
                const edadNum = parseInt(value);
                return !isNaN(edadNum) && edadNum >= 10 && edadNum <= 60;
            case 'peso':
                const pesoNum = parseFloat(value);
                return !isNaN(pesoNum) && pesoNum >= 40 && pesoNum <= 140;
            case 'altura':
                const alturaNum = parseFloat(value);
                return !isNaN(alturaNum) && alturaNum >= 100 && alturaNum <= 210;
            case 'club_id':
                return !!value;
            default:
                return false;
        }
    };

    // Errores en tiempo real para el texto rojo
    const getFieldError = (field: keyof FormData): string | undefined => {
        // Primero priorizar errores del estado global (los de "handleNext")
        if (errors[field]) return errors[field];

        const value = formData[field].toString().trim();
        if (!value && focusedField !== field) return undefined; // No mostrar error si está vacío y no enfocado
        if (!value) return undefined;

        switch (field) {
            case 'nombre':
            case 'apellidos':
                if (value.length > 0 && value.length < 3) return 'Mínimo 3 letras';
                if (/\d/.test(value)) return 'No se permiten números';
                return undefined;
            case 'dni':
                if (value.length > 0 && value.length !== 8) return 'DNI debe tener 8 dígitos';
                return undefined;

            case 'email':
                if (value.length > 0 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Formato de email incorrecto';
                return undefined;
            case 'telefono':
                if (value.length > 0) {
                    if (!value.startsWith('9')) {
                        return 'El numero debe de empezar en 9';
                    }
                    if (value.length < 9) {
                        const missing = 9 - value.length;
                        return `Faltan ${missing} dígitos`;
                    }
                }
                return undefined;

            // Validaciones Paso 2
            case 'edad':
                if (!value) return undefined;
                const edad = parseInt(value);
                if (isNaN(edad)) return 'Ingresa un número';
                if (edad < 10) return 'Mínimo 10 años';
                if (edad > 60) return 'Máximo 60 años';
                return undefined;

            case 'peso':
                if (!value) return undefined;
                const peso = parseFloat(value);
                if (isNaN(peso)) return 'Ingresa un número';
                if (peso < 40) return 'Mínimo 40 kg';
                if (peso > 140) return 'Máximo 140 kg';
                return undefined;

            case 'altura':
                if (!value) return undefined;
                const altura = parseFloat(value);
                if (isNaN(altura)) return 'Ingresa un número';
                if (altura < 100) return 'Mínimo 100 cm';
                if (altura > 210) return 'Máximo 210 cm';
                return undefined;

            default:
                return undefined;
        }
    };

    // Mensajes de éxito en tiempo real
    const getFieldSuccess = (field: keyof FormData): string | undefined => {
        if (!isFieldValid(field)) return undefined;

        switch (field) {
            case 'nombre':
                return 'Nombre válido';
            case 'apellidos':
                return 'Apellido válido';
            case 'email':
                return 'Email correcto';
            case 'dni':
                return 'DNI verificado';
            case 'telefono':
                return 'Teléfono válido';
            default:
                return undefined;
        }
    };

    return (
        <SafeAreaView style={styles.container}>
            <StatusBar barStyle="light-content" backgroundColor="#000" />

            <ScreenHeader
                title="EL JAB DORADO"
                subtitle="INSCRIPCIÓN DE PELEADOR"
                slogan="Únete a la élite del boxeo profesional"
            />

            {/* VERIFICANDO AUTH */}
            {checkingAuth ? (
                <View style={styles.loadingContainer}>
                    <ActivityIndicator size="large" color={COLORS.primary} />
                    <Text style={styles.loadingText}>Verificando perfil...</Text>
                </View>
            ) : existingFighter ? (
                // SI YA ES PELEADOR: MOSTRAR SOLO LA CARD
                // SI YA ES PELEADOR: MOSTRAR SOLO LA CARD
                <View style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
                    <FighterIdentityModal
                        visible={showIdentityModal}
                        onClose={() => {
                            console.log('👆 Modal: Closing and navigating to Home');
                            setShowIdentityModal(false);
                            // Small delay to allow modal close animation
                            setTimeout(() => {
                                navigation.navigate('Home' as never);
                            }, 100);
                        }}
                        onEdit={() => {
                            console.log('👆 Modal: Closing and navigating to Profile');
                            setShowIdentityModal(false);
                            // Small delay to allow modal close animation
                            setTimeout(() => {
                                navigation.navigate('Profile' as never);
                            }, 100);
                        }}
                        fighter={{
                            nombre: existingFighter.nombre,
                            apellidos: existingFighter.apellidos,
                            apodo: existingFighter.apodo,
                            peso: existingFighter.peso,
                            genero: existingFighter.genero,
                            photoUri: existingFighter.foto_perfil,
                            clubName: clubs?.find(c => c.id === existingFighter.club_id)?.nombre
                        }}
                    />
                </View>
            ) : (
                // SI NO ES PELEADOR: MOSTRAR EL FORMULARIO COMPLETO
                <KeyboardAwareScrollView
                    style={{ flex: 1 }}
                    contentContainerStyle={{ flexGrow: 1, paddingBottom: 100 }}
                    enableOnAndroid={true}
                    keyboardShouldPersistTaps="handled"
                    extraScrollHeight={210}
                    enableAutomaticScroll={true}
                    enableResetScrollToCoords={false}
                    ref={(ref: any) => { (scrollViewRef as any).current = ref; }}
                    showsVerticalScrollIndicator={false}
                >

                    {/* Barra de Progreso */}
                    <View style={styles.progressContainer}>
                        <View style={styles.progressBackground}>
                            <View
                                style={[
                                    styles.progressBar,
                                    { width: `${(currentStep / totalSteps) * 100}%` }
                                ]}
                            />
                        </View>
                        <View style={styles.stepsTextContainer}>
                            <Text style={styles.stepText}>Paso {currentStep} de {totalSteps}</Text>
                            <Text style={styles.stepTitle}>
                                {currentStep === 1 ? 'Identidad' : currentStep === 2 ? 'Estadísticas' : 'Club y Foto'}
                            </Text>
                        </View>
                    </View>

                    <View style={styles.formContainer}>
                        {/* Vista Previa de la Ficha - Constrained Container */}
                        <View style={{ width: '100%', alignItems: 'center', overflow: 'visible', zIndex: 1 }}>
                            <FighterCard
                                fighter={{
                                    nombre: formData.nombre,
                                    apellidos: formData.apellidos,
                                    apodo: formData.apodo,
                                    peso: formData.peso,
                                    genero: formData.genero,
                                    photoUri: cardPhoto?.uri || (banners.length > 0 ? banners[currentBannerIndex]?.url : undefined),
                                    clubName: clubs?.find(c => c.id === formData.club_id)?.nombre,
                                    edad: formData.edad,
                                    altura: formData.altura
                                }}
                                variant="preview"
                                onUploadBackground={currentStep === 3 || SHOW_DEBUG_GENERATOR ? pickCardBackground : undefined}
                                backgroundOffsetY={bgOffsetY}
                                backgroundOffsetX={bgOffsetX}
                                backgroundScale={bgScale}
                            />
                        </View>

                        {/* Background Adjustment Controls (Step 3 only, Web Only, Custom Photo Only) */}
                        {Platform.OS === 'web' && (currentStep === 3 || SHOW_DEBUG_GENERATOR) && cardPhoto && (
                            <ImageAdjustmentControls
                                isWeb={Platform.OS === 'web'}
                                showDebug={SHOW_DEBUG_GENERATOR}
                                hasPhoto={!!cardPhoto}
                                setBgOffsetX={setBgOffsetX}
                                setBgOffsetY={setBgOffsetY}
                                bgScale={bgScale}
                                setBgScale={setBgScale}
                                onRemoveBackground={handleRemoveBackground}
                                isRemovingBg={isRemovingBg}
                                isLibReady={isLibReady}
                            />
                        )}

                        {/* PASO 1: Identidad y Contacto */}
                        {currentStep === 1 && (
                            <>
                                <View onLayout={handleSectionLayout('datos_personales')}>
                                    <FormSection icon="👤" title="DATOS PERSONALES">
                                        {/* 🪄 DEBUG BUTTON: SOLO VISIBLE SI SHOW_DEBUG_GENERATOR ES TRUE */}
                                        {SHOW_DEBUG_GENERATOR && (
                                            <TouchableOpacity
                                                style={{
                                                    alignSelf: 'flex-end',
                                                    backgroundColor: 'rgba(255, 215, 0, 0.1)',
                                                    padding: 8,
                                                    borderRadius: 8,
                                                    marginBottom: 10,
                                                    flexDirection: 'row',
                                                    gap: 6,
                                                    borderWidth: 1,
                                                    borderColor: 'rgba(255, 215, 0, 0.3)'
                                                }}
                                                onPress={() => {
                                                    const dummy = generateDebugFighter();
                                                    setFormData(prev => ({ ...prev, ...dummy }));
                                                }}
                                            >
                                                <Ionicons name="flash" size={16} color="#FFD700" />
                                                <Text style={{ color: '#FFD700', fontSize: 12, fontWeight: 'bold' }}>GENERAR DATOS</Text>
                                            </TouchableOpacity>
                                        )}
                                        <View onLayout={handleFieldLayout('nombre')}>
                                            <FormInput
                                                label="Nombre"
                                                value={formData.nombre}
                                                onChangeText={(value) => updateField('nombre', value)}
                                                placeholder="Ej: Miguel"
                                                focused={focusedField === 'nombre'}
                                                onFocus={() => setFocusedField('nombre')}
                                                onBlur={() => handleBlurField('nombre')}
                                                error={getFieldError('nombre')}
                                                isValid={isFieldValid('nombre')}
                                                successMessage={getFieldSuccess('nombre')}
                                            />
                                        </View>

                                        <View onLayout={handleFieldLayout('apellidos')}>
                                            <FormInput
                                                label="Apellidos"
                                                value={formData.apellidos}
                                                onChangeText={(value) => updateField('apellidos', value)}
                                                placeholder="Ej: Rodríguez García"
                                                focused={focusedField === 'apellidos'}
                                                onFocus={() => setFocusedField('apellidos')}
                                                onBlur={() => handleBlurField('apellidos')}
                                                error={getFieldError('apellidos')}
                                                isValid={isFieldValid('apellidos')}
                                                successMessage={getFieldSuccess('apellidos')}
                                            />
                                        </View>

                                        <View onLayout={handleFieldLayout('dni')}>
                                            <FormInput
                                                label="DNI"
                                                value={formData.dni}
                                                onChangeText={(value) => updateField('dni', value)}
                                                placeholder="12345678"
                                                keyboardType="numeric"
                                                focused={focusedField === 'dni'}
                                                onFocus={() => setFocusedField('dni')}
                                                onBlur={() => handleBlurField('dni')}
                                                error={getFieldError('dni')}
                                                isValid={isFieldValid('dni')}
                                                successMessage={getFieldSuccess('dni')}
                                            />
                                            {validatingDNI && (
                                                <Text style={styles.validatingText}>Verificando DNI...</Text>
                                            )}
                                        </View>
                                    </FormSection>
                                </View>

                                <View onLayout={handleSectionLayout('contacto')}>
                                    <FormSection icon="📱" title="CONTACTO">
                                        <View onLayout={handleFieldLayout('email')}>
                                            <FormInput
                                                label="Email"
                                                value={formData.email}
                                                onChangeText={(value) => updateField('email', value)}
                                                placeholder="peleador@ejemplo.com"
                                                keyboardType="email-address"
                                                autoCapitalize="none"
                                                focused={focusedField === 'email'}
                                                onFocus={() => setFocusedField('email')}
                                                onBlur={() => handleBlurField('email')}
                                                error={getFieldError('email')}
                                                isValid={isFieldValid('email')}
                                                successMessage={getFieldSuccess('email')}
                                            />
                                            {validatingEmail && (
                                                <Text style={styles.validatingText}>Verificando email...</Text>
                                            )}
                                        </View>

                                        <View onLayout={handleFieldLayout('telefono')}>
                                            <PhoneInput
                                                label="Teléfono"
                                                value={formData.telefono}
                                                onChangeText={(value) => updateField('telefono', value)}
                                                countryCode={formData.countryCode}
                                                onCountryChange={(code) => updateField('countryCode', code)}
                                                placeholder="Ej: 987654321"
                                                focused={focusedField === 'telefono'}
                                                onFocus={() => setFocusedField('telefono')}
                                                onBlur={() => handleBlurField('telefono')}
                                                error={getFieldError('telefono')}
                                                isValid={isFieldValid('telefono')}
                                                successMessage={getFieldSuccess('telefono')}
                                            />
                                        </View>
                                    </FormSection>
                                </View>
                            </>
                        )}

                        {/* PASO 2: Características Físicas */}
                        {currentStep === 2 && (
                            <View onLayout={handleSectionLayout('caracteristicas')}>
                                <FormSection icon="💪" title="CARACTERÍSTICAS FÍSICAS">
                                    <View onLayout={handleFieldLayout('apodo')}>
                                        <FormInput
                                            label="Apodo (opcional)"
                                            value={formData.apodo}
                                            onChangeText={(value) => updateField('apodo', value)}
                                            placeholder='Ej: "El Trueno"'
                                            focused={focusedField === 'apodo'}
                                            onFocus={() => setFocusedField('apodo')}
                                            onBlur={() => handleBlurField('apodo')}
                                            icon="⚡"
                                        />
                                    </View>

                                    <View onLayout={handleFieldLayout('genero')}>
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
                                            error={getFieldError('genero')}
                                        />
                                    </View>

                                    <View style={styles.row}>
                                        <View style={styles.halfWidth} onLayout={handleFieldLayout('edad')}>
                                            <FormInput
                                                label="Edad"
                                                value={formData.edad}
                                                onChangeText={(value) => updateField('edad', value)}
                                                placeholder="25"
                                                keyboardType="numeric"
                                                focused={focusedField === 'edad'}
                                                onFocus={() => setFocusedField('edad')}
                                                onBlur={() => handleBlurField('edad')}
                                                error={getFieldError('edad')}
                                                isValid={isFieldValid('edad')}
                                                successMessage={getFieldSuccess('edad')}
                                            />
                                        </View>
                                        <View style={styles.halfWidth} onLayout={handleFieldLayout('peso')}>
                                            <FormInput
                                                label="Peso (kg)"
                                                value={formData.peso}
                                                onChangeText={(value) => updateField('peso', value)}
                                                placeholder="75.5"
                                                keyboardType="decimal-pad"
                                                focused={focusedField === 'peso'}
                                                onFocus={() => setFocusedField('peso')}
                                                onBlur={() => handleBlurField('peso')}
                                                error={getFieldError('peso')}
                                                isValid={isFieldValid('peso')}
                                                successMessage={getFieldSuccess('peso')}
                                            />
                                        </View>
                                    </View>

                                    <View onLayout={handleFieldLayout('altura')}>
                                        <FormInput
                                            label="Altura (cm)"
                                            value={formData.altura}
                                            onChangeText={(value) => updateField('altura', value)}
                                            placeholder="180"
                                            keyboardType="numeric"
                                            focused={focusedField === 'altura'}
                                            onFocus={() => setFocusedField('altura')}
                                            onBlur={() => handleBlurField('altura')}
                                            error={getFieldError('altura')}
                                            isValid={isFieldValid('altura')}
                                            successMessage={getFieldSuccess('altura')}
                                        />
                                    </View>
                                </FormSection>
                            </View>
                        )}

                        {/* PASO 3: Club y Foto */}
                        {currentStep === 3 && (
                            <>
                                <FormSection icon="📷" title="FOTO DE PERFIL (OPCIONAL)">
                                    <View style={styles.photoContainer}>
                                        <TouchableOpacity
                                            onPress={pickProfilePhoto}
                                            style={[styles.photoWrapper, photo && styles.photoWrapperActive]}
                                            activeOpacity={0.8}
                                        >
                                            {photo ? (
                                                <Image source={{ uri: photo.uri }} style={styles.photoPreview} />
                                            ) : (
                                                <View style={styles.photoPlaceholder}>
                                                    <Ionicons name="person" size={40} color={COLORS.text.tertiary} />
                                                </View>
                                            )}

                                            {/* Overlay de Cámara */}
                                            <View style={styles.cameraOverlay}>
                                                <Ionicons name="camera" size={20} color="#FFF" />
                                            </View>
                                        </TouchableOpacity>

                                        {photo ? (
                                            <View style={styles.photoActions}>
                                                <TouchableOpacity onPress={pickProfilePhoto}>
                                                    <Text style={styles.changePhotoText}>Cambiar foto</Text>
                                                </TouchableOpacity>
                                                <View style={styles.verticalDivider} />
                                                <TouchableOpacity onPress={() => setPhoto(null)}>
                                                    <Text style={styles.removePhotoText}>Eliminar</Text>
                                                </TouchableOpacity>
                                            </View>
                                        ) : (
                                            <Text style={styles.photoHintText}>Toca el círculo para subir tu mejor foto de combate (Máx 5MB)</Text>
                                        )}
                                    </View>
                                </FormSection>

                                <View onLayout={handleSectionLayout('club')}>
                                    <FormSection icon="🥊" title="CLUB / GIMNASIO">
                                        {loadingClubs ? (
                                            <View style={styles.loadingContainer}>
                                                <ActivityIndicator size="large" color={COLORS.primary} />
                                                <Text style={styles.loadingText}>Cargando clubs...</Text>
                                            </View>
                                        ) : (
                                            <View onLayout={handleFieldLayout('club_id')}>
                                                <ClubSelector
                                                    label="Busca tu club o gimnasio"
                                                    value={formData.club_id}
                                                    onValueChange={(value) => updateField('club_id', value)}
                                                    options={clubs || []}
                                                    placeholder="Toca para elegir tu club"
                                                    error={errors.club_id}
                                                />
                                            </View>
                                        )}
                                    </FormSection>
                                </View>
                            </>
                        )}

                        {/* Botones de Navegación */}
                        <View style={styles.navigationButtons}>
                            {currentStep > 1 && (
                                <TouchableOpacity
                                    style={styles.backButton}
                                    onPress={handleBack}
                                    disabled={isSubmitting}
                                >
                                    <Text style={styles.backButtonText}>Anterior</Text>
                                </TouchableOpacity>
                            )}

                            <TouchableOpacity
                                style={[
                                    styles.nextButton,
                                    currentStep === 1 && { width: '100%' }
                                ]}
                                onPress={handleNext}
                                disabled={isSubmitting}
                            >
                                {isSubmitting ? (
                                    <ActivityIndicator color="#fff" />
                                ) : (
                                    <Text style={styles.nextButtonText}>
                                        {currentStep === totalSteps ? 'FINALIZAR REGISTRO' : 'Siguiente'}
                                    </Text>
                                )}
                            </TouchableOpacity>
                        </View>

                        <SponsorFooter />
                    </View>
                </KeyboardAwareScrollView>
            )
            }

            {/* Modal de éxito */}
            <EpicFighterSuccessModal
                visible={showSuccessModal}
                onClose={handleCloseSuccessModal}
                fighterData={{
                    nombre: successData?.nombre || formData.nombre,
                    apellidos: successData?.apellidos || formData.apellidos,
                    apodo: successData?.apodo || formData.apodo,
                    peso: successData?.peso || formData.peso,
                    genero: successData?.genero || formData.genero,
                    photoUri: successData?.photoUri || photo?.uri,
                    edad: successData?.edad || formData.edad,
                    altura: successData?.altura || formData.altura,
                    clubName: successData?.clubName || clubs?.find(c => c.id === formData.club_id)?.nombre
                }}
                email={successData?.email || formData.email}
                dni={successData?.dni || formData.dni}
                isAutoLoggedIn={isAutoLoggedIn}
                backgroundOffsetY={bgOffsetY}
                backgroundOffsetX={bgOffsetX}
                backgroundScale={bgScale}
            />



            {/* MODAL PERSONALIZADO DE SELECCIÓN DE IMAGEN (UNIFICADO) */}
            <FighterImageUploadModal
                visible={showImageOptions}
                onClose={() => setShowImageOptions(false)}
                onCamera={launchCamera}
                onGallery={launchGallery}
                mode={imageUploadMode}
            />

        </SafeAreaView >
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
        paddingBottom: 100, // Padding masivo solicitado para visualización completa de peleadores
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
        paddingVertical: SPACING.lg,
    },
    photoWrapper: {
        width: 120,
        height: 120,
        borderRadius: 60,
        borderWidth: 2,
        borderColor: 'rgba(255,255,255,0.1)',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: SPACING.md,
        backgroundColor: COLORS.surface,
        position: 'relative', // Para el overlay
    },
    photoWrapperActive: {
        borderColor: COLORS.primary,
        borderWidth: 3,
        ...createShadow(COLORS.primary, 0, 0, 0.5, 10, 10),
    },
    cardPhotoWrapper: {
        width: 220, // Rectangular 2.2:1 approx
        height: 100,
        borderRadius: BORDER_RADIUS.md,
        borderWidth: 2,
        borderColor: 'rgba(255,255,255,0.1)',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: SPACING.md,
        backgroundColor: COLORS.surface,
        position: 'relative',
        overflow: 'hidden',
    },
    cardPhotoWrapperActive: {
        borderColor: COLORS.primary,
        borderWidth: 3,
        ...createShadow(COLORS.primary, 0, 0, 0.5, 10, 10),
    },
    photoPreview: {
        width: '100%',
        height: '100%',
        borderRadius: 60,
    },
    photoPlaceholder: {
        width: '100%',
        height: '100%',
        borderRadius: 60,
        alignItems: 'center',
        justifyContent: 'center',
    },
    cameraOverlay: {
        position: 'absolute',
        bottom: 0,
        right: 0,
        backgroundColor: COLORS.primary,
        width: 36,
        height: 36,
        borderRadius: 18,
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 2,
        borderColor: '#000',
    },
    photoActions: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: SPACING.md,
        marginTop: SPACING.xs,
    },
    changePhotoText: {
        color: COLORS.primary,
        fontWeight: '600',
        fontSize: 14,
    },
    removePhotoText: {
        color: COLORS.error,
        fontWeight: '600',
        fontSize: 14,
    },
    verticalDivider: {
        width: 1,
        height: 16,
        backgroundColor: 'rgba(255,255,255,0.2)',
    },
    photoHintText: {
        fontSize: 12,
        color: COLORS.text.secondary,
        textAlign: 'center',
        marginTop: SPACING.xs,
        maxWidth: 200,
    },
    // ESTILOS PARA MODAL Y SELECTOR DE FOTOS
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0, 0, 0, 0.85)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: SPACING.lg,
    },
    modalContent: {
        backgroundColor: COLORS.surface,
        borderRadius: BORDER_RADIUS.lg,
        width: '100%',
        maxWidth: 400,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.1)',
        ...createShadow('#000', 0, 10, 0.5, 20, 10),
    },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: SPACING.lg,
        borderBottomWidth: 1,
        borderBottomColor: 'rgba(255,255,255,0.1)',
        backgroundColor: 'rgba(255,255,255,0.02)',
        borderTopLeftRadius: BORDER_RADIUS.lg,
        borderTopRightRadius: BORDER_RADIUS.lg,
    },
    modalTitle: {
        fontSize: 16,
        fontWeight: '800',
        color: COLORS.primary,
        letterSpacing: 1,
    },
    imageOptionsContainer: {
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        padding: SPACING.xl,
        gap: SPACING.xl,
    },
    imageOptionButton: {
        alignItems: 'center',
        justifyContent: 'center',
        gap: SPACING.sm,
    },
    imageOptionIcon: {
        width: 64,
        height: 64,
        borderRadius: 32,
        backgroundColor: 'rgba(255, 215, 0, 0.1)',
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 1,
        borderColor: COLORS.primary,
        marginBottom: 8,
    },
    imageOptionText: {
        color: COLORS.text.primary,
        fontWeight: '600',
        fontSize: 14,
    },
    verticalDividerLarge: {
        width: 1,
        height: 60,
        backgroundColor: 'rgba(255,255,255,0.1)',
        marginHorizontal: SPACING.lg,
    },
    // ESTILOS EXISTENTES ...
    progressContainer: {
        paddingHorizontal: SPACING.lg,
        paddingVertical: SPACING.md,
        backgroundColor: 'rgba(255, 255, 255, 0.05)',
    },
    progressBackground: {
        height: 4,
        backgroundColor: 'rgba(255, 255, 255, 0.1)',
        borderRadius: 2,
        marginBottom: SPACING.sm,
    },
    progressBar: {
        height: '100%',
        backgroundColor: COLORS.primary,
        borderRadius: 2,
    },
    stepsTextContainer: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    stepText: {
        fontSize: 12,
        color: COLORS.text.tertiary,
        fontWeight: '600',
    },
    stepTitle: {
        fontSize: 14,
        color: COLORS.primary,
        fontWeight: '800',
        textTransform: 'uppercase',
    },
    navigationButtons: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginTop: SPACING.xl,
        gap: SPACING.md,
    },
    backButton: {
        flex: 1,
        backgroundColor: 'transparent',
        borderWidth: 1,
        borderColor: COLORS.text.tertiary,
        paddingVertical: SPACING.md,
        borderRadius: 8,
        alignItems: 'center',
        justifyContent: 'center',
    },
    backButtonText: {
        color: COLORS.text.tertiary,
        fontSize: 16,
        fontWeight: '600',
    },
    nextButton: {
        flex: 2,
        backgroundColor: COLORS.primary,
        paddingVertical: SPACING.md,
        borderRadius: 8,
        alignItems: 'center',
        justifyContent: 'center',
        ...createShadow(COLORS.primary, 0, 4, 0.3, 5, 4),
    },
    nextButtonText: {
        color: '#000',
        fontSize: 16,
        fontWeight: '800',
        textTransform: 'uppercase',
    },
});
