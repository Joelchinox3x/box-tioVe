import { useState, useEffect, useRef, useCallback } from 'react';
import { Alert, LayoutChangeEvent, Platform, View } from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import * as Haptics from 'expo-haptics';
import { useAudioPlayer } from 'expo-audio';
import * as ImagePicker from 'expo-image-picker';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { KeyboardAwareScrollView } from 'react-native-keyboard-aware-scroll-view';
import { captureRef } from 'react-native-view-shot';

import { fighterService } from '../../services/fighterService';
import { clubService, Club } from '../../services/clubService';
import { bannerService } from '../../services/bannerService';
import { cardTemplateService } from '../../services/cardTemplateService';
import * as FileSystem from 'expo-file-system';
import { FormData, FormErrors } from './types';
import api from '../../services/api';
import { generateDebugFighter } from '../../data/dummyFighters';
import { AdminService } from '../../services/AdminService';
import { useBackgroundRemoval } from '../../hooks/useBackgroundRemoval';
import { Config, toAbsoluteUrl } from '../../config/config';

export interface FighterLayer {
    id: string;
    uri: string;
    // Transform props
    x: number;
    y: number;
    scale: number;
    rotation: number;
    flipX: boolean;
    // New Visual Props
    preset?: string;
    effect?: string;
    effectColor?: string;
    originalUri?: string; // New: Store original for non-destructive edits
}

export const useFighterForm = (fighterCardRef?: React.RefObject<View>) => {
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

    // Mapeo de campos a sus secciones
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

    const initialFormData: FormData = {
        nombre: '', apellidos: '', apodo: '', edad: '', peso: '', altura: '',
        genero: 'masculino', email: '', telefono: '', countryCode: 'PE', dni: '', club_id: '',
    };

    const [formData, setFormData] = useState<FormData>(initialFormData);

    const [focusedField, setFocusedField] = useState<string | null>(null);
    const [errors, setErrors] = useState<FormErrors>({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [clubs, setClubs] = useState<Club[]>([]);
    const [loadingClubs, setLoadingClubs] = useState(true);
    const [validatingDNI, setValidatingDNI] = useState(false);
    const [validatingEmail, setValidatingEmail] = useState(false);
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [successData, setSuccessData] = useState<any>(null);
    const [isAutoLoggedIn, setIsAutoLoggedIn] = useState(false);
    const [isAlreadyAuth, setIsAlreadyAuth] = useState(false);
    const [existingFighter, setExistingFighter] = useState<any>(null);
    const [checkingAuth, setCheckingAuth] = useState(true);
    const [currentStep, setCurrentStep] = useState(1);
    const totalSteps = 3;
    const [showIdentityModal, setShowIdentityModal] = useState(false);

    // Image State
    const [photo, setPhoto] = useState<{ uri: string; name?: string; type?: string } | null>(null);

    // Multi-Layer State (Replaces cardPhoto & single bg vars)
    const [fighterLayers, setFighterLayers] = useState<FighterLayer[]>([]);

    const [showImageOptions, setShowImageOptions] = useState(false);
    // imageUploadMode still useful for knowing if we are adding to card or profile? 
    // Actually we can keep it, 'background' now means 'add to layers'
    const [imageUploadMode, setImageUploadMode] = useState<'profile' | 'background'>('background');
    const [lastImageSource, setLastImageSource] = useState<'camera' | 'gallery' | null>(null);

    const { removeBackground, isProcessing: isRemovingBg, isLibReady } = useBackgroundRemoval();

    // Templates State
    const [backgroundTemplates, setBackgroundTemplates] = useState<any[]>([]);
    const [borderTemplates, setBorderTemplates] = useState<any[]>([]);
    const [selectedBorder, setSelectedBorder] = useState<string | null>(null);
    const [selectedBackground, setSelectedBackground] = useState<string | null>(null);
    const [stickerTemplates, setStickerTemplates] = useState<any[]>([]);
    const [selectedStickers, setSelectedStickers] = useState<string[]>([]);

    // Banners
    const [banners, setBanners] = useState<any[]>([]);
    const [currentBannerIndex, setCurrentBannerIndex] = useState(0);
    const [companyLogoUri, setCompanyLogoUri] = useState<string | null>(null);
    const [adjustmentFocus, setAdjustmentFocus] = useState<'photo' | string>('photo');
    const [stickerTransforms, setStickerTransforms] = useState<Record<string, { x: number, y: number, scale: number, rotation: number, flipX: boolean }>>({});
    const [isManualSelection, setIsManualSelection] = useState(false);

    // Helper to get current adjustment values based on focus
    const getCurrentLayer = () => {
        if (adjustmentFocus === 'photo') return null; // Legacy/Fallback
        return fighterLayers.find(l => l.id === adjustmentFocus);
    };

    const currentOffsetX = getCurrentLayer()?.x ?? 0;
    const currentOffsetY = getCurrentLayer()?.y ?? 0;
    const currentScale = getCurrentLayer()?.scale ?? 1;
    const currentRotation = getCurrentLayer()?.rotation ?? 0;
    const currentFlipX = getCurrentLayer()?.flipX ?? false;

    // Layer Management
    const addFighterLayer = (uri: string, preset: string = 'original', effect: string = 'none', effectColor: string = '#00FFFF') => {
        if (fighterLayers.length >= 3) return;

        // Stop auto-banners
        setIsManualSelection(true);

        // If it's the first manual photo, pick a random bg and border if none selected
        if (fighterLayers.length === 0) {
            if (backgroundTemplates.length > 0 && !selectedBackground) {
                const randBg = backgroundTemplates[Math.floor(Math.random() * backgroundTemplates.length)].url;
                setSelectedBackground(randBg);
            }
            if (borderTemplates.length > 0 && !selectedBorder) {
                const randBorder = borderTemplates[Math.floor(Math.random() * borderTemplates.length)].url;
                setSelectedBorder(randBorder);
            }
        }

        const newLayer: FighterLayer = {
            id: `layer-${Date.now()}`,
            uri,
            x: 0, y: 0, scale: 1, rotation: 0, flipX: false,
            preset,
            effect,
            effectColor
        };
        setFighterLayers(prev => [...prev, newLayer]);
        setAdjustmentFocus(newLayer.id); // Auto-focus new layer
    };

    const removeFighterLayer = (id: string) => {
        setFighterLayers(prev => prev.filter(l => l.id !== id));
        if (adjustmentFocus === id) setAdjustmentFocus('photo'); // Fallback or clear
    };

    const updateFighterLayer = (id: string, updates: Partial<FighterLayer>) => {
        setFighterLayers(prev => prev.map(l => l.id === id ? { ...l, ...updates } : l));
    };

    // Unified Update Helpers (Proxies to updateFighterLayer or StickerTransforms)
    // Unified Updates for Layers AND Stickers
    const updateOffsetX = (val: number | ((prev: number) => number)) => {
        const isLayer = fighterLayers.some(l => l.id === adjustmentFocus);
        if (isLayer) {
            setFighterLayers(prev => prev.map(l => {
                if (l.id !== adjustmentFocus) return l;
                const newVal = typeof val === 'function' ? val(l.x) : val;
                return { ...l, x: newVal };
            }));
        } else {
            setStickerTransforms(prev => ({
                ...prev,
                [adjustmentFocus]: {
                    ...prev[adjustmentFocus],
                    x: typeof val === 'function' ? val(prev[adjustmentFocus]?.x || 0) : val
                }
            }));
        }
    };

    const updateOffsetY = (val: number | ((prev: number) => number)) => {
        const isLayer = fighterLayers.some(l => l.id === adjustmentFocus);
        if (isLayer) {
            setFighterLayers(prev => prev.map(l => {
                if (l.id !== adjustmentFocus) return l;
                const newVal = typeof val === 'function' ? val(l.y) : val;
                return { ...l, y: newVal };
            }));
        } else {
            setStickerTransforms(prev => ({
                ...prev,
                [adjustmentFocus]: {
                    ...prev[adjustmentFocus],
                    y: typeof val === 'function' ? val(prev[adjustmentFocus]?.y || 0) : val
                }
            }));
        }
    };

    const updateScale = (val: number | ((prev: number) => number)) => {
        const isLayer = fighterLayers.some(l => l.id === adjustmentFocus);
        if (isLayer) {
            setFighterLayers(prev => prev.map(l => {
                if (l.id !== adjustmentFocus) return l;
                const newVal = typeof val === 'function' ? val(l.scale) : val;
                return { ...l, scale: newVal };
            }));
        } else {
            setStickerTransforms(prev => ({
                ...prev,
                [adjustmentFocus]: {
                    ...prev[adjustmentFocus],
                    scale: typeof val === 'function' ? val(prev[adjustmentFocus]?.scale || 1) : val
                }
            }));
        }
    };

    const updateRotation = (val: number | ((prev: number) => number)) => {
        const isLayer = fighterLayers.some(l => l.id === adjustmentFocus);
        if (isLayer) {
            setFighterLayers(prev => prev.map(l => {
                if (l.id !== adjustmentFocus) return l;
                const newVal = typeof val === 'function' ? val(l.rotation) : val;
                return { ...l, rotation: newVal };
            }));
        } else {
            setStickerTransforms(prev => ({
                ...prev,
                [adjustmentFocus]: {
                    ...prev[adjustmentFocus],
                    rotation: typeof val === 'function' ? val(prev[adjustmentFocus]?.rotation || 0) : val
                }
            }));
        }
    };

    const updateFlipX = (val: boolean | ((prev: boolean) => boolean)) => {
        const isLayer = fighterLayers.some(l => l.id === adjustmentFocus);
        if (isLayer) {
            setFighterLayers(prev => prev.map(l => {
                if (l.id !== adjustmentFocus) return l;
                const newVal = typeof val === 'function' ? val(l.flipX) : val;
                return { ...l, flipX: newVal };
            }));
        } else {
            setStickerTransforms(prev => ({
                ...prev,
                [adjustmentFocus]: {
                    ...prev[adjustmentFocus],
                    flipX: typeof val === 'function' ? val(prev[adjustmentFocus]?.flipX || false) : val
                }
            }));
        }
    };

    // NOTE: Because we are inside a hook, I will expose these helpers properly later or 
    // keep utilizing the unified 'ImageAdjustmentControls' props.
    // For now, let's keep the return clean.
    const [isImageCropperVisible, setIsImageCropperVisible] = useState(false);
    const [pendingImageUri, setPendingImageUri] = useState<string | null>(null);

    // Audio
    const punch3Player = useAudioPlayer(require('../../../assets/sounds/punch-03.mp3'));
    const punch4Player = useAudioPlayer(require('../../../assets/sounds/punch-04.mp3'));
    const bell3Player = useAudioPlayer(require('../../../assets/sounds/bell-03.mp3'));

    const playSound = (soundName: 'punch3' | 'punch4' | 'bell3') => {
        try {
            if (soundName === 'punch3') { punch3Player.seekTo(0); punch3Player.play(); }
            else if (soundName === 'punch4') { punch4Player.seekTo(0); punch4Player.play(); }
            else if (soundName === 'bell3') { bell3Player.seekTo(0); bell3Player.play(); }
        } catch (error) { console.log('Error playing sound:', error); }
    };

    // EFFECTS - Re-run on every screen focus (Tab Navigator keeps screens alive)
    useFocusEffect(
        useCallback(() => {
            setCheckingAuth(true);
            setCurrentStep(1);
            setExistingFighter(null);
            setShowIdentityModal(false);
            loadClubs();
            checkAuthStatus();
            loadBanners();
            loadTemplates();
            loadBranding();
        }, [])
    );

    useEffect(() => {
        if (banners.length === 0 || isManualSelection) return;
        // Al entrar, mostrar un banner aleatorio una sola vez (sin rotación automática)
        const randomIndex = Math.floor(Math.random() * banners.length);
        setCurrentBannerIndex(randomIndex);
        setSelectedBackground(banners[randomIndex].url);
    }, [banners, isManualSelection]);

    // Restore Default Banner Background Behavior
    useEffect(() => {
        if (!isManualSelection && banners.length > 0 && banners[currentBannerIndex]) {
            setSelectedBackground(banners[currentBannerIndex].url);
        }
    }, [currentBannerIndex, banners, isManualSelection]);

    useEffect(() => {
        if (existingFighter) setShowIdentityModal(true);
    }, [existingFighter]);

    useEffect(() => {
        if (successData) {
            playSound('bell3');
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
            setShowSuccessModal(true);
        }
    }, [successData]);

    // Validation Effects (DNI & Email)
    useEffect(() => {
        const timeoutId = setTimeout(async () => {
            if (formData.dni.trim().length >= 8) {
                setValidatingDNI(true);
                try {
                    const result = await fighterService.verificarDNI(formData.dni.trim());
                    if (!result.disponible) setErrors(prev => ({ ...prev, dni: 'Este DNI ya está registrado' }));
                    else setErrors(prev => { const n = { ...prev }; delete n.dni; return n; });
                } catch (e) { console.error('Error verifying DNI', e); } finally { setValidatingDNI(false); }
            }
        }, 800);
        return () => clearTimeout(timeoutId);
    }, [formData.dni]);

    useEffect(() => {
        if (isAlreadyAuth) return;
        const timeoutId = setTimeout(async () => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailRegex.test(formData.email)) {
                setValidatingEmail(true);
                try {
                    const result = await fighterService.verificarEmail(formData.email);
                    if (!result.disponible) setErrors(prev => ({ ...prev, email: 'Este email ya está registrado' }));
                    else setErrors(prev => { const n = { ...prev }; delete n.email; return n; });
                } catch (e) { console.error('Error verifying Email', e); } finally { setValidatingEmail(false); }
            }
        }, 800);
        return () => clearTimeout(timeoutId);
    }, [formData.email, isAlreadyAuth]);

    const loadTemplates = async () => {
        const bgs = await cardTemplateService.getBackgrounds();
        const borders = await cardTemplateService.getBorders();
        const stickers = await cardTemplateService.getStickers();

        setBackgroundTemplates(bgs);
        setBorderTemplates(borders);
        setStickerTemplates(stickers);
    };

    const loadBanners = async () => {
        try { const data = await bannerService.getAll(false); setBanners(data); }
        catch (e) { console.log('Error loading banners', e); }
    };

    const loadBranding = async () => {
        try {
            const data = await AdminService.getActiveLogos();
            if (data.success && data.logos.card) {
                setCompanyLogoUri(data.logos.card.url);
            }
        } catch (e) {
            console.log('Error loading branding', e);
        }
    };

    const checkAuthStatus = async () => {
        try {
            const token = await AsyncStorage.getItem('token');
            const userStr = await AsyncStorage.getItem('user');
            if (!token || !userStr) {
                setIsAlreadyAuth(false);
                setIsAutoLoggedIn(false);
                setExistingFighter(null);
                setFormData(initialFormData);
                setPhoto(null);
                setFighterLayers([]);
                setSelectedBackground(null);
                setSelectedBorder(null);
                setSelectedStickers([]);
                setStickerTransforms({});
                setCheckingAuth(false);
                return;
            }
            setIsAlreadyAuth(true);
            const user = JSON.parse(userStr);
            console.log('👤 [AUTH] Usuario logueado:', { id: user.id, nombre: user.nombre, tipo_id: user.tipo_id });

            // Verificar si ya es peleador
            let isFighter = false;
            try {
                const response = await fighterService.getByUserId(user.id);
                if (response.success && response.peleador) {
                    setExistingFighter(response.peleador);
                    isFighter = true;
                }
            } catch (err) {
                console.log('👤 [AUTH] No es peleador, preparando pre-llenado...');
            }

            // Si NO es peleador → pre-llenar form con datos existentes
            if (!isFighter) {
                const phoneRaw = (user.telefono || '').replace(/\D/g, '');
                const phoneClean = phoneRaw.replace(/^(51|54|52|1)/, '');

                console.log('📝 [PREFILL] Datos:', { nombre: user.nombre, apellidos: user.apellidos, email: user.email, telefono: phoneClean });

                setFormData(prev => ({
                    ...prev,
                    nombre: user.nombre || prev.nombre,
                    apellidos: user.apellidos || prev.apellidos,
                    email: user.email || prev.email,
                    telefono: phoneClean || prev.telefono,
                    club_id: user.club_id || prev.club_id,
                }));

                if (user.foto_perfil) {
                    const photoUrl = user.foto_perfil.startsWith('http')
                        ? user.foto_perfil
                        : `${Config.BASE_URL}/${user.foto_perfil}`;
                    setPhoto({ uri: photoUrl, name: 'foto_perfil.jpg', type: 'image/jpeg' });
                    console.log('📷 [PREFILL] Foto cargada:', photoUrl);
                }
            }
        } catch (e) { console.error('Auth check error', e); } finally { setCheckingAuth(false); }
    };

    const loadClubs = async () => {
        setLoadingClubs(true);
        try {
            const clubsData = await clubService.getAll();
            setClubs(clubsData || []);
            if (clubsData) {
                const independiente = clubsData.find(c => c.nombre.toLowerCase().includes('independiente'));
                if (independiente && !formData.club_id) setFormData(prev => ({ ...prev, club_id: independiente.id }));
            }
        } catch (e) { Alert.alert('Error', 'No se pudieron cargar los clubs'); } finally { setLoadingClubs(false); }
    };

    const toTitleCase = (str: string) => {
        return str.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    };

    const updateField = (field: keyof FormData, value: string | number) => {
        let processedValue = value;
        if (field === 'dni' && typeof value === 'string') processedValue = value.replace(/\D/g, '').slice(0, 8);
        if ((field === 'nombre' || field === 'apellidos') && typeof value === 'string') processedValue = value.replace(/[0-9]/g, '');
        if (field === 'telefono' && typeof value === 'string') {
            const numericValue = value.replace(/\D/g, '').slice(0, 9);
            if (numericValue.length > 1 && !numericValue.startsWith('9')) processedValue = '9';
            else processedValue = numericValue;
        }
        setFormData(prev => ({ ...prev, [field]: processedValue }));
        if (errors[field]) setErrors(prev => { const n = { ...prev }; delete n[field]; return n; });
    };

    const handleBlurField = (field: keyof FormData) => {
        setFocusedField(null);
        if ((field === 'nombre' || field === 'apellidos') && formData[field]) {
            const formattedValue = toTitleCase(formData[field].toString());
            setFormData(prev => ({ ...prev, [field]: formattedValue }));
        }
        validateField(field);
    };

    const isFieldValid = (field: keyof FormData) => {
        if (!formData[field]) return false;
        if (errors[field]) return false;
        switch (field) {
            case 'nombre': case 'apellidos': return String(formData[field]).trim().length >= 3;
            case 'dni': return String(formData[field]).length === 8 && !validatingDNI;
            case 'email': return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(formData[field])) && !validatingEmail;
            case 'telefono': return String(formData[field]).length === 9 && String(formData[field]).startsWith('9');
            case 'edad': { const val = parseInt(String(formData[field])); return !isNaN(val) && val >= 10 && val <= 60; }
            case 'peso': { const val = parseFloat(String(formData[field])); return !isNaN(val) && val >= 40 && val <= 140; }
            case 'altura': { const val = parseInt(String(formData[field])); return !isNaN(val) && val >= 100 && val <= 210; }
            case 'apodo': return true; // Optional in backup
            default: return String(formData[field]).length > 0;
        }
    };

    const getFieldError = (field: keyof FormData) => errors[field];
    const getFieldSuccess = (field: keyof FormData): string | undefined => {
        if (!isFieldValid(field)) return undefined;

        switch (field) {
            case 'nombre': return 'Nombre válido';
            case 'apellidos': return 'Apellido válido';
            case 'email': return 'Email correcto';
            case 'dni': return 'DNI verificado';
            case 'telefono': return 'Teléfono válido';
            case 'edad': return 'Edad válida';
            case 'peso': return 'Peso válido';
            case 'altura': return 'Altura válida';
            case 'apodo': return 'Apodo válido';
            default: return undefined;
        }
    };

    const validateField = (field: keyof FormData) => {
        if (!isFieldValid(field)) {
            let errorMsg = 'Error';
            switch (field) {
                case 'nombre': errorMsg = 'Mínimo 3 letras'; break;
                case 'apellidos': errorMsg = 'Apellido requerido'; break;
                case 'dni': errorMsg = 'DNI inváido (8 dígitos)'; break;
                case 'email': errorMsg = 'Email inválido'; break;
                case 'telefono': errorMsg = 'Celular 9 dígitos (inicia con 9)'; break;
                case 'apodo': errorMsg = ''; break; // Optional
                case 'edad': errorMsg = '10-60 años'; break;
                case 'peso': errorMsg = '40-140 kg'; break;
                case 'altura': errorMsg = '100-210 cm'; break;
                case 'club_id': errorMsg = 'Selecciona club'; break;
            }
            if (errorMsg) setErrors(prev => ({ ...prev, [field]: errorMsg }));
        } else {
            setErrors(prev => { const n = { ...prev }; delete n[field]; return n; });
        }
    };

    const validateStep = (step: number): boolean => {
        if (validatingEmail || validatingDNI) { return false; } // Silent return to match backup
        const newErrors: FormErrors = {};
        const missingFields: string[] = [];

        if (step === 1) {
            if (!isFieldValid('nombre')) { newErrors.nombre = 'Mínimo 3 letras'; missingFields.push('Nombre'); }
            if (!isFieldValid('apellidos')) { newErrors.apellidos = 'Apellido requerido'; missingFields.push('Apellidos'); }
            if (!isFieldValid('dni')) { newErrors.dni = 'DNI inváido'; missingFields.push('DNI'); }
            if (!isFieldValid('email')) { newErrors.email = 'Email inválido'; missingFields.push('Email'); }
            if (!isFieldValid('telefono')) { newErrors.telefono = 'Celular 9 dígitos'; missingFields.push('Celular'); }
        } else if (step === 2) {
            // Apodo is optional, removed check
            if (!isFieldValid('edad')) { newErrors.edad = '10-60 años'; missingFields.push('Edad'); }
            if (!isFieldValid('peso')) { newErrors.peso = '40-140 kg'; missingFields.push('Peso'); }
            if (!isFieldValid('altura')) { newErrors.altura = '100-210 cm'; missingFields.push('Altura'); }
            if (!formData.genero) { newErrors.genero = 'Selecciona género'; missingFields.push('Género'); }
        } else if (step === 3) {
            if (!isFieldValid('club_id')) { newErrors.club_id = 'Selecciona club'; missingFields.push('Club'); }
        }

        if (Object.keys(newErrors).length > 0) {
            setErrors(prev => ({ ...prev, ...newErrors }));
            // Alert removed to match backup behavior

            // Scroll to first error
            const errorKeys = Object.keys(newErrors);
            if (errorKeys.length > 0) {
                const firstErrorKey = errorKeys[0];
                const sectionKey = fieldToSection[firstErrorKey];

                const yPos = sectionKey
                    ? (sectionPositions.current[sectionKey] || 0)
                    : (fieldPositions.current[firstErrorKey] || 0);

                // Add a small offset/padding
                scrollViewRef.current?.scrollToPosition(0, Math.max(0, yPos - 50), true);
            }

            return false;
        }
        return true;
    };

    const handleNext = () => {
        if (validateStep(currentStep)) {
            if (currentStep < totalSteps) {
                // Determine next step
                const nextStep = currentStep + 1;

                // Play sound based on step (from backup)
                if (currentStep === 1) playSound('punch3');
                if (currentStep === 2) playSound('punch4');

                setCurrentStep(nextStep);
                Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);

                // Scroll logic from backup: Step 3 shows card at top, others have header offset
                scrollViewRef.current?.scrollToPosition(0, nextStep === 3 ? 0 : 255, true);
            } else {
                handleSubmit();
            }
        } else {
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
        }
    };

    const handleBack = () => {
        if (currentStep > 1) {
            const prevStep = currentStep - 1;
            setCurrentStep(prevStep);
            Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);

            // Scroll logic from backup
            scrollViewRef.current?.scrollToPosition(0, prevStep === 3 ? 0 : 255, true);
        }
    };

    // ... (rest of image/background functions remain via context, just fixing these functions) ...

    const fillDebugData = () => {
        const dummy = generateDebugFighter();

        // Randomly pick a club if available from the loaded clubs
        let selectedClubId = formData.club_id;
        if (clubs && clubs.length > 0) {
            const randomIndex = Math.floor(Math.random() * clubs.length);
            selectedClubId = String(clubs[randomIndex].id);
        }

        setFormData(prev => ({
            ...prev,
            ...dummy,
            club_id: selectedClubId
        }));
        setErrors({}); // Clear all errors
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
    };


    const handleRemoveBackground = async () => {
        const layer = getCurrentLayer();
        if (!layer?.uri) return;
        try {
            const url = await removeBackground(layer.uri);
            if (url) {
                updateFighterLayer(layer.id, { uri: url });
            }
        } catch (e: any) { Alert.alert('Error', e.message); }
    };

    const handleBackgroundSelected = async (uri: string) => {
        // Legacy function, might not be needed or refactored to addLayer
        // But for consistency:
        addFighterLayer(uri);
        // setIsManualSelection(true); // Maybe not needed
        setIsManualSelection(true);

        // Auto-randomize background and border for a "wow" effect when first choosing personal photo
        if (backgroundTemplates.length > 0) {
            const randBg = backgroundTemplates[Math.floor(Math.random() * backgroundTemplates.length)].url;
            setSelectedBackground(randBg);
        }
        if (borderTemplates.length > 0) {
            const randBorder = borderTemplates[Math.floor(Math.random() * borderTemplates.length)].url;
            setSelectedBorder(randBorder);
        }

        setBanners(prev => prev.map(b => ({ ...b, selected: false })));
        scrollViewRef.current?.scrollToPosition(0, 0, true);
    };

    const toggleSticker = (url: string) => {
        setIsManualSelection(true); // Stop auto-banners
        setSelectedStickers(prev => {
            const isSelected = prev.includes(url);
            if (isSelected) {
                if (adjustmentFocus === url) setAdjustmentFocus('photo'); // fallback
                return prev.filter(s => s !== url);
            } else {
                // Limit to 3 stickers maximum
                if (prev.length >= 3) {
                    Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
                    return prev;
                }
                if (!stickerTransforms[url]) {
                    setStickerTransforms(current => ({
                        ...current,
                        [url]: { x: 0, y: 0, scale: 1, rotation: 0, flipX: false }
                    }));
                }
                setAdjustmentFocus(url);
                return [...prev, url];
            }
        });
        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    };

    const launchCamera = async (explicitMode?: 'profile' | 'background') => {
        const { status } = await ImagePicker.requestCameraPermissionsAsync();
        if (status !== 'granted') return Alert.alert('Error', 'Se requiere cámara');
        const result = await ImagePicker.launchCameraAsync({
            mediaTypes: ['images'],
            allowsEditing: false,
            quality: 0.8,
        });
        if (!result.canceled && result.assets[0].uri) {
            const uri = result.assets[0].uri;
            const mode = explicitMode || imageUploadMode;

            // Universal Flow: Use WebImageCropper for both Web and Native
            setPendingImageUri(uri);
            setImageUploadMode(mode);
            setLastImageSource('camera');
            setIsImageCropperVisible(true);
            setShowImageOptions(false);
        }
    };

    const launchGallery = async (explicitMode?: 'profile' | 'background') => {
        const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
        if (status !== 'granted') return Alert.alert('Error', 'Se requiere galería');
        const result = await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ['images'],
            allowsEditing: false,
            quality: 0.8,
        });
        if (!result.canceled && result.assets[0].uri) {
            const uri = result.assets[0].uri;
            const mode = explicitMode || imageUploadMode;

            // Universal Flow: Use WebImageCropper for both Web and Native
            setPendingImageUri(uri);
            setImageUploadMode(mode);
            setLastImageSource('gallery');
            setIsImageCropperVisible(true);
            setShowImageOptions(false);
        }
    };

    const handleImageCropConfirm = async (result: string | any[], preset: string = 'original', effect: string = 'none', effectColor: string = '#00FFFF', shouldClose: boolean = true) => {
        if (imageUploadMode === 'background') {
            if (Array.isArray(result)) {
                // MULTI-TAB SYNC STRATEGY
                const newLayers = [...fighterLayers]; // Clone current

                result.forEach((tab) => {
                    const existingIndex = newLayers.findIndex(l => l.id === tab.id);
                    // Extract styles if present in tab object, otherwise fall back to defaults
                    const tabPreset = tab.preset || 'original';
                    const tabEffect = tab.effectMode || 'none';
                    const tabColor = tab.effectColor || '#00FFFF';

                    if (existingIndex !== -1) {
                        // UPDATE existing
                        newLayers[existingIndex] = {
                            ...newLayers[existingIndex],
                            uri: tab.uri,
                            originalUri: tab.originalUri || newLayers[existingIndex].originalUri || tab.uri,
                            preset: tabPreset,
                            effect: tabEffect,
                            effectColor: tabColor
                        };
                    } else {
                        // ADD new
                        newLayers.push({
                            id: tab.id || `layer-${Date.now()}-${Math.random()}`,
                            uri: tab.uri,
                            originalUri: tab.originalUri || tab.uri, // Initialize originalUri same as uri
                            x: 0, y: 0, scale: 1, rotation: 0, flipX: false,
                            preset: tabPreset,
                            effect: tabEffect,
                        });
                    }
                });

                setFighterLayers(newLayers);

                // FORCE FOCUS if currently invalid (legacy 'photo')
                // This fixes the issue where controls don't work after initial save
                if (adjustmentFocus === 'photo' && newLayers.length > 0) {
                    setAdjustmentFocus(newLayers[0].id);
                }
            } else {
                // LEGACY / WEB / SINGLE ADD
                addFighterLayer(result, preset, effect, effectColor);
            }
        } else {
            // Profile Photo Logic (Unchanged)
            const uri = Array.isArray(result) ? result[0].uri : result;
            setPhoto({ uri });
            setBanners(prev => prev.map(b => ({ ...b, selected: false })));
        }

        if (shouldClose) {
            setIsImageCropperVisible(false);
            setPendingImageUri(null);
        }
    };

    const validateForm = (): boolean => {
        // Simple check to ensure all required fields are present
        // (Detailed validation is done in steps, this is a final safety check)
        const required = ['nombre', 'apellidos', 'email', 'dni', 'telefono', 'genero', 'edad', 'peso', 'altura', 'club_id'];
        for (const field of required) {
            if (!formData[field as keyof FormData]) return false;
        }
        return true;
    };

    const handleSubmit = async () => {
        // 1. Validar el paso actual (Step 3)
        if (!validateStep(currentStep)) return;

        // 2. Validar TODO el formulario
        if (!validateForm()) {
            Alert.alert('Datos Incompletos', 'Por favor revisa que todos los campos estén llenos.');
            return;
        }

        console.log('🚀 Iniciando envío...');
        setIsSubmitting(true);

        try {
            let bakedUri = null;
            let webBakedBlob: Blob | null = null;

            const normalizeAssetUrl = (url?: string | null) => {
                if (!url) return null;
                if (url.startsWith('blob:') || url.startsWith('data:')) return url;
                const absolute = url.startsWith('http://') || url.startsWith('https://')
                    ? url
                    : toAbsoluteUrl(url);
                return absolute.replace(/^http:\/\//, 'https://');
            };

            // 🔥 CAPTURAR LA IMAGEN DE LA TARJETA (QUEMAR)
            console.log('🔍 [CAPTURE] Verificando fighterCardRef:', {
                refExists: !!fighterCardRef,
                currentExists: fighterCardRef?.current ? true : false,
                currentType: fighterCardRef?.current?.constructor?.name
            });

            // 1. Preparar datos base
            const alturaMetros = parseFloat(formData.altura) / 100;

            const COUNTRY_CODES: any = { 'PE': '+51', 'AR': '+54', 'MX': '+52', 'US': '+1' };
            const dialCode = COUNTRY_CODES[formData.countryCode] || '+51';
            const fullPhone = `${dialCode}${formData.telefono.replace(/\D/g, '')}`;

            // 2. Crear FormData
            const form = new global.FormData(); // Force global FormData for RN

            // Si es espectador existente, enviar usuario_id para upgrade
            if (isAlreadyAuth) {
                try {
                    const userStr = await AsyncStorage.getItem('user');
                    if (userStr) {
                        const user = JSON.parse(userStr);
                        form.append('usuario_id', String(user.id));
                        console.log('🔄 [UPGRADE] Enviando usuario_id:', user.id);
                    }
                } catch (e) {
                    console.error('Error getting user for upgrade:', e);
                }
            }

            // Agregar campos de texto
            form.append('nombre', formData.nombre.trim());
            form.append('apellidos', formData.apellidos.trim() || '');
            form.append('email', formData.email.trim().toLowerCase());
            if (!isAlreadyAuth) {
                form.append('password', formData.dni.trim());
            }
            form.append('telefono', fullPhone);
            form.append('apodo', formData.apodo.trim() || formData.nombre.trim().split(' ')[0]);
            form.append('edad', formData.edad.trim());
            form.append('peso_actual', String(parseFloat(formData.peso)));
            form.append('altura', String(alturaMetros));
            form.append('genero', formData.genero);
            form.append('documento_identidad', formData.dni.trim());
            form.append('club_id', String(formData.club_id));
            form.append('estilo', 'fajador');
            form.append('experiencia_anos', '0');

            // 3. AGREGAR FOTO DE PERFIL (Lógica Diferenciada Web vs Móvil)
            if (photo) {
                if (Platform.OS === 'web') {
                    try {
                        const response = await fetch(photo.uri);
                        const blob = await response.blob();
                        form.append('foto_perfil', blob, photo.name || 'foto.jpg');
                    } catch (blobErr) { console.error("Error blob web:", blobErr); }
                } else {
                    form.append('foto_perfil', {
                        uri: photo.uri,
                        name: photo.name || 'foto.jpg',
                        type: photo.type || 'image/jpeg',
                    } as any);
                }
            }

            // 4. AGREGAR IMAGENES DE CAPAS (UNIVERSAL JSON)
            for (const layer of fighterLayers) {
                if (!layer.uri) continue;

                if (Platform.OS === 'web') {
                    if (layer.uri.startsWith('blob:') || layer.uri.startsWith('data:')) {
                        try {
                            const response = await fetch(layer.uri);
                            const blob = await response.blob();
                            form.append(`layer_file_${layer.id}`, blob, `layer_${layer.id}.png`);
                        } catch (e) {
                            console.error('Error subiendo layer web:', e);
                        }
                    }
                    // Si ya es URL https/http, no es necesario adjuntar
                } else if (layer.uri.startsWith('file://')) {
                    form.append(`layer_file_${layer.id}`, {
                        uri: layer.uri,
                        name: `layer_${layer.id}.png`,
                        type: 'image/png',
                    } as any);
                }
            }

            // Construir JSON de composición (con URIs locales que el backend reemplazará)
            const composition_json = {
                version: "1.1",
                background: { url: normalizeAssetUrl(selectedBackground) },
                border: { url: normalizeAssetUrl(selectedBorder) },
                layers: fighterLayers.map(l => ({
                    id: l.id,
                    uri: l.uri,
                    x: l.x,
                    y: l.y,
                    scale: l.scale,
                    rotation: l.rotation,
                    flipX: l.flipX,
                    preset: l.preset,
                    effect: l.effect,
                    effectColor: l.effectColor
                })),
                stickers: selectedStickers.map(s => ({
                    url: normalizeAssetUrl(s),
                    transform: stickerTransforms[s] || { x: 0, y: 0, scale: 1, rotation: 0, flipX: false }
                })),
                companyLogo: { url: normalizeAssetUrl(companyLogoUri) }
            };
            form.append('composition_json', JSON.stringify(composition_json));

            // 🔥 CAPTURAR LA IMAGEN DE LA TARJETA (WEB + NATIVO)
            if (Platform.OS === 'web' && fighterCardRef?.current) {
                try {
                    const { toBlob } = await import('html-to-image');
                    await new Promise(resolve => setTimeout(resolve, 120));
                    const node = fighterCardRef.current as unknown as HTMLElement;
                    const blob = await toBlob(node, {
                        cacheBust: true,
                        pixelRatio: 2,
                        backgroundColor: 'transparent',
                    });
                    if (blob) {
                        webBakedBlob = blob;
                        console.log('✅ [CAPTURE] Imagen web capturada exitosamente');
                    } else {
                        console.warn('⚠️ [CAPTURE] html-to-image no retornó blob');
                    }
                } catch (captureError: any) {
                    console.error('❌ [CAPTURE] Error al capturar web:', captureError?.message || captureError);
                }
            } else if (Platform.OS !== 'web' && fighterCardRef && fighterCardRef.current) {
                try {
                    console.log('📸 [CAPTURE] Intentando capturar imagen de la tarjeta...');
                    console.log('📸 [CAPTURE] Ref current:', fighterCardRef.current);

                    // Pequeño delay para asegurar renderizado
                    await new Promise(resolve => setTimeout(resolve, 100));
                    console.log('⏱️ [CAPTURE] Delay completado, iniciando captura...');

                    const uri = await captureRef(fighterCardRef, {
                        format: 'png',
                        quality: 1.0,
                        result: 'tmpfile',
                    });
                    bakedUri = uri;
                    console.log('✅ [CAPTURE] Imagen capturada exitosamente:', bakedUri);
                } catch (captureError: any) {
                    console.error('❌ [CAPTURE] Error al capturar imagen:', captureError);
                    console.error('❌ [CAPTURE] Error message:', captureError?.message);
                    console.error('❌ [CAPTURE] Error name:', captureError?.name);
                    // Continuar sin imagen quemada
                }
            } else {
                console.warn('⚠️ [CAPTURE] No se pudo capturar - ref no disponible:', {
                    hasRef: !!fighterCardRef,
                    hasCurrent: !!fighterCardRef?.current
                });
            }

            // 5. AGREGAR IMAGEN "QUEMADA" (BAKED)
            if (Platform.OS === 'web' && webBakedBlob) {
                form.append('baked_image', webBakedBlob, `card_${formData.dni}.png`);
            } else if (bakedUri) {
                form.append('baked_image', {
                    uri: bakedUri,
                    name: `card_${formData.dni}.png`,
                    type: 'image/png',
                } as any);
            }

            // 6. Enviar usando el servicio
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
                // Upgrade: actualizar datos del usuario en AsyncStorage (tipo_id=2)
                try {
                    const userStr = await AsyncStorage.getItem('user');
                    if (userStr) {
                        const user = JSON.parse(userStr);
                        user.tipo_id = 2;
                        user.nombre = formData.nombre.trim();
                        user.apellidos = formData.apellidos.trim();
                        user.telefono = fullPhone;
                        await AsyncStorage.setItem('user', JSON.stringify(user));
                        console.log('✅ AsyncStorage actualizado con tipo_id=2');
                    }
                } catch (e) {
                    console.warn('⚠️ Error actualizando AsyncStorage:', e);
                }
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
                photoUri: fighterLayers.length > 0 ? fighterLayers[0].uri : photo?.uri,
                edad: formData.edad,
                altura: formData.altura,
                clubName: clubs?.find(c => c.id === formData.club_id)?.nombre,
                bakedUrl: response.baked_url ? `${Config.BASE_URL}/${response.baked_url}` : null
            });

        } catch (error: any) {
            console.error('❌ Error registro:', error);
            setIsSubmitting(false);
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);

            const msg = error.response?.data?.message || 'Error al conectar con el servidor.';
            Alert.alert('Error', msg);
        }
    };

    const handleCloseSuccessModal = () => {
        setShowSuccessModal(false); setSuccessData(null); setPhoto(null); setFighterLayers([]);
        if (isAlreadyAuth) {
            // Espectador upgradeó a peleador → navegar a perfil
            (navigation as any).navigate('Profile');
        } else {
            (navigation as any).navigate(isAutoLoggedIn ? 'Profile' : 'Login');
            setIsAutoLoggedIn(false);
        }
    };

    return {
        navigation, scrollViewRef, fieldPositions, sectionPositions, handleFieldLayout, handleSectionLayout,
        formData, updateField, focusedField, setFocusedField, errors, isSubmitting, clubs, loadingClubs,
        isFieldValid, getFieldError, getFieldSuccess, validateField,
        currentStep, totalSteps, handleNext, handleBack,
        photo, showImageOptions, setShowImageOptions, imageUploadMode, setImageUploadMode,
        isRemovingBg, isLibReady,
        fighterLayers, addFighterLayer, removeFighterLayer, updateFighterLayer,
        currentOffsetX, currentOffsetY, currentScale, currentRotation, currentFlipX,
        updateOffsetX, updateOffsetY, updateScale, updateRotation, updateFlipX,

        adjustmentFocus, setAdjustmentFocus, stickerTransforms, setStickerTransforms,
        backgroundTemplates, borderTemplates, stickerTemplates, selectedBorder, setSelectedBorder, selectedBackground, setSelectedBackground, selectedStickers,
        handleRemoveBackground, launchCamera, launchGallery, pickProfilePhoto: () => { setImageUploadMode('profile'); setShowImageOptions(true); },
        pickCardBackground: () => {
            setImageUploadMode('background');
            setShowImageOptions(true);
        },
        setCardBackgroundUrl: (url: string) => {
            const isDeselecting = selectedBackground === url;
            setSelectedBackground(prev => prev === url ? null : url);
            setIsManualSelection(true); // Always stop banners when explicitly picking a background
            Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        },
        toggleSticker,
        banners, currentBannerIndex, isManualSelection,
        isImageCropperVisible, setIsImageCropperVisible, pendingImageUri, setPendingImageUri, handleImageCropConfirm,
        handleSubmit, showSuccessModal, successData, handleCloseSuccessModal,
        checkingAuth, existingFighter, showIdentityModal, setShowIdentityModal,
        isAlreadyAuth, isAutoLoggedIn, handleBlurField,
        fillDebugData, companyLogoUri,
        clearProfilePhoto: () => setPhoto(null),
        randomizeDesign: () => {
            setIsManualSelection(true);
            if (backgroundTemplates.length > 0) {
                const randBg = backgroundTemplates[Math.floor(Math.random() * backgroundTemplates.length)].url;
                setSelectedBackground(randBg);
            }
            if (borderTemplates.length > 0) {
                const randBorder = borderTemplates[Math.floor(Math.random() * borderTemplates.length)].url;
                setSelectedBorder(randBorder);
            }
        },
        toggleBorder: (url: string) => {
            setIsManualSelection(true); // Stop auto-banners
            setSelectedBorder(prev => prev === url ? null : url);
            Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        },
        resetDesign: () => {
            setFighterLayers([]);
            setSelectedBackground(null);
            setSelectedBorder(null);
            setSelectedStickers([]);
            setStickerTransforms({});
            setIsManualSelection(false);
            setAdjustmentFocus('photo');
            if (banners.length > 0) {
                const randomIndex = Math.floor(Math.random() * banners.length);
                setCurrentBannerIndex(randomIndex);
                setSelectedBackground(banners[randomIndex].url);
            }
            Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
        },
        lastImageSource
    };
};
