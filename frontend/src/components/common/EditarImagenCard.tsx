import React, { useState, useEffect, useRef } from 'react';
import { View, StyleSheet, Dimensions, TouchableOpacity, Text, Platform, ActivityIndicator, Image as RNImage } from 'react-native';
import {
    Canvas,
    Image,
    useImage,
    Rect,
    Path,
    Skia,
    ColorMatrix,
    Paint,
    Group,
    Blur,
    Morphology,
    ImageFormat, // Added Import
} from '@shopify/react-native-skia';
import {
    Gesture,
    GestureDetector,
    GestureHandlerRootView,
} from 'react-native-gesture-handler';
import Animated, {
    useSharedValue,
    useAnimatedStyle,
    useDerivedValue,
} from 'react-native-reanimated';
import { COLORS } from '../../constants/theme';
import { Ionicons } from '@expo/vector-icons';
import * as ImageManipulator from 'expo-image-manipulator';
import * as FileSystem from 'expo-file-system/legacy';
import { useBackgroundRemoval } from '../../hooks/useBackgroundRemoval';
import { BackgroundRemoverWebView } from './BackgroundRemoverWebView';
import { ScrollView, Pressable } from 'react-native';

// --- Matrix Helpers ---
const brightness = (b: number) => [
    1, 0, 0, 0, b,
    0, 1, 0, 0, b,
    0, 0, 1, 0, b,
    0, 0, 0, 1, 0,
];
const contrast = (c: number) => {
    const t = 0.5 * (1 - c);
    return [
        c, 0, 0, 0, t,
        0, c, 0, 0, t,
        0, 0, c, 0, t,
        0, 0, 0, 1, 0,
    ];
};
const saturate = (s: number) => {
    const lumR = 0.3086, lumG = 0.6094, lumB = 0.0820;
    const sr = (1 - s) * lumR, sg = (1 - s) * lumG, sb = (1 - s) * lumB;
    return [
        sr + s, sg, sb, 0, 0,
        sr, sg + s, sb, 0, 0,
        sr, sg, sb + s, 0, 0,
        0, 0, 0, 1, 0,
    ];
};

// Palette for Random Selection
const EFFECT_COLORS = [
    '#FF0000', '#FFFF00', '#0000FF', '#FF8C00', '#00FF00',
    '#A020F0', '#00FFFF', '#FF00FF', '#FFD700', '#C0C0C0',
    '#FFFFFF', '#FF69B4', '#4B0082', '#800000', '#008080'
];

const getRandomColor = () => EFFECT_COLORS[Math.floor(Math.random() * EFFECT_COLORS.length)];


const { width: SCREEN_WIDTH, height: SCREEN_HEIGHT } = Dimensions.get('window');
const CANVAS_HEIGHT = SCREEN_HEIGHT * 0.65;
const CANVAS_WIDTH = SCREEN_WIDTH;

interface EditarImagenCardProps {
    imageUri: string;
    onConfirm: (result: string | any[]) => void;
    onCancel: () => void;
    onChangePhoto?: () => void;
    onLaunchCamera?: () => void;
    onLaunchGallery?: () => void;
    initialSource?: 'camera' | 'gallery';
    existingLayers?: { id: string; uri: string }[];
}

export const EditarImagenCard = ({ imageUri, onConfirm, onCancel, onChangePhoto, onLaunchCamera, onLaunchGallery, initialSource, existingLayers = [] }: EditarImagenCardProps) => {
    // We use a local state for the image to handle updates after cropping "in-place"
    const [currentUri, setCurrentUri] = useState(imageUri);
    const skiaImage = useImage(currentUri);
    const canvasRef = useRef<any>(null);
    const exportRef = useRef<any>(null); // Ref for the clean canvas
    const [loading, setLoading] = useState(false);
    const [isSaving, setIsSaving] = useState(false); // Trigger for snapshot logic

    // Background Removal State
    const { uploadToTempServer } = useBackgroundRemoval();
    const [showWebRemover, setShowWebRemover] = useState(false);
    const [tempImageUrl, setTempImageUrl] = useState<string | null>(null);
    const [isRemovingBg, setIsRemovingBg] = useState(false);

    // Effects State
    const [selectedPreset, setSelectedPreset] = useState<'original' | 'grit' | 'vibrant' | 'bw' | 'cinematic' | 'vintage'>('original');
    const [effectMode, setEffectMode] = useState<'none' | 'aura' | 'neon' | 'border' | 'ghost' | 'glitch'>('none');
    // Randomize initial color
    const [effectColor, setEffectColor] = useState<string>(() => getRandomColor());
    const [activeTab, setActiveTab] = useState<'filters' | 'effects' | 'color'>('filters');

    // --- SESSION TABS LOGIC ---
    // Initialize tabs from existing layers + current new image (if any)
    // --- SESSION TABS LOGIC ---
    type TabState = {
        id: string;
        uri: string | null;
        originalUri: string | null; // Store base image
        label: string;
        source: 'camera' | 'gallery' | 'unknown';
        // Independent Effects State
        preset: 'original' | 'grit' | 'vibrant' | 'bw' | 'cinematic' | 'vintage';
        effectMode: 'none' | 'aura' | 'neon' | 'border' | 'ghost' | 'glitch';
        effectColor: string;
    };

    // Initialize tabs from existing layers + current new image (if any)
    const [sessionTabs, setSessionTabs] = useState<TabState[]>(() => {
        const tabs: TabState[] = [];

        // 1. Add existing layers first
        existingLayers.forEach((layer, index) => {
            tabs.push({
                // Ensure ID is unique. Preference: Layer ID > Generated Unique ID
                id: layer.id || `tab-init-${index}-${Date.now()}`,
                uri: layer.uri,
                originalUri: (layer as any).originalUri || layer.uri, // Load original if available
                label: `FOTO ${index + 1}`, // Label is always visual index
                source: 'unknown' as const,
                // Load existing effects or default
                preset: (layer as any).preset || 'original',
                effectMode: (layer as any).effect || 'none',
                effectColor: (layer as any).effectColor || '#00FFFF'
            });
        });

        // 2. If we are editing a NEW image (imageUri present and not in layers), add it
        if (tabs.length === 0) {
            tabs.push({
                id: `tab-new-${Date.now()}`,
                uri: imageUri,
                originalUri: imageUri,
                label: `FOTO ${tabs.length + 1}`,
                source: 'unknown',
                preset: 'original',
                effectMode: 'none',
                effectColor: getRandomColor()
            });
        } else if (imageUri && !existingLayers.find(l => l.uri === imageUri)) {
            if (tabs.length < 3) {
                tabs.push({
                    id: `tab-extra-${Date.now()}`,
                    uri: imageUri,
                    originalUri: imageUri,
                    label: `FOTO ${tabs.length + 1}`, // Sequential label
                    source: initialSource || 'unknown',
                    preset: 'original', effectMode: 'none', effectColor: getRandomColor()
                });
            }
        }

        return tabs;
    });

    // Set initial active tab to the last one (the new one usually)
    const [activeSessionId, setActiveSessionId] = useState(() => {
        if (sessionTabs.length > 0) return sessionTabs[sessionTabs.length - 1].id;
        return '1';
    });
    const pendingSource = useRef<'camera' | 'gallery' | null>(null);

    const handleAddTab = () => {
        // Rule 1: Max 3 Tabs
        if (sessionTabs.length >= 3) return;

        // Rule 2: Cannot add if last tab has no photo
        const lastTab = sessionTabs[sessionTabs.length - 1];
        if (!lastTab.uri) {
            alert("Primero añade una foto a la pestaña actual.");
            return;
        }

        const newId = (sessionTabs.length + 1).toString();
        setSessionTabs([...sessionTabs, {
            id: newId, uri: null, originalUri: null, label: `FOTO ${newId}`, source: 'unknown',
            preset: 'original', effectMode: 'none', effectColor: getRandomColor()
        }]);
        setActiveSessionId(newId);
    };

    const activeSession = sessionTabs.find(t => t.id === activeSessionId) || sessionTabs[0];

    // Wrap the original onChangePhoto to handle "filling" the empty tab
    const handlePhotoSelect = (forcedSource?: 'camera' | 'gallery') => {
        // If we have a stored source for this session (e.g. Tab 2 was Camera), prioritize
        const effectiveSource = forcedSource || activeSession?.source;

        if (effectiveSource === 'camera' && onLaunchCamera) {
            pendingSource.current = 'camera';
            onLaunchCamera();
        } else if (effectiveSource === 'gallery' && onLaunchGallery) {
            pendingSource.current = 'gallery';
            onLaunchGallery();
        } else {
            // Fallback to generic picker (User decides)
            if (onChangePhoto) onChangePhoto();
        }
    };

    // --- TAB SWITCHING ---
    const handleTabSwitch = async (newId: string) => {
        if (newId === activeSessionId) return;

        setLoading(true);
        // 1. Snapshot/Bake current tab (Keep edits persistent as pixels)
        // Note: Use a more robust check? If we don't save here, we lose the visual edit when switching back unless we store 'originalUri' separate from 'displayUri'.
        // Current architecture: uri IS the display uri. 
        // So we MUST save if we want to keep the "Look".
        const savedUri = await saveSnapshot();

        // 2. Update session state: URI + Current Effects Settings
        setSessionTabs(prev => prev.map(t => t.id === activeSessionId ? {
            ...t,
            uri: savedUri || t.uri,
            // COMMIT: We baked the effect, so we reset the "live" modifiers to avoid double-application
            preset: 'original' as const,
            effectMode: 'none' as const,
            effectColor: '#00FFFF'
        } : t));

        // 3. Switch ID
        setActiveSessionId(newId);
        // Loading false handles in effect or generic finally? handled here for immediate UI response
        // But need to wait for state update?
        // Actually, setActiveSessionId triggers the effect below which loads new state.
        setLoading(false);
    };

    // LOAD STATE ON SWITCH
    useEffect(() => {
        if (activeSession) {
            if (activeSession.uri) setCurrentUri(activeSession.uri);
            else setCurrentUri('');

            // Load Effects
            setSelectedPreset(activeSession.preset);
            setEffectMode(activeSession.effectMode);
            setEffectColor(activeSession.effectColor);
        }
    }, [activeSessionId]);

    // Sync current Uri changes (crop)
    useEffect(() => {
        if (activeSessionId && currentUri) {
            setSessionTabs(tabs => tabs.map(t => t.id === activeSessionId ? { ...t, uri: currentUri } : t));
        }
    }, [currentUri]);

    // Logic: Calculate Matrices
    const presetMatrix = React.useMemo(() => {
        if (selectedPreset === 'grit') return [contrast(1.4), saturate(0.6), brightness(0.1)];
        if (selectedPreset === 'vibrant') return [contrast(1.1), saturate(1.5)];
        if (selectedPreset === 'bw') return [saturate(0)];
        if (selectedPreset === 'cinematic') return [contrast(1.5), saturate(0.8)];
        if (selectedPreset === 'vintage') return [
            // Old Photo: Desaturated, Faded, Slight Warmth
            saturate(0.4),
            contrast(1.2),
            [
                1, 0, 0, 0, 0.05, // Lift R
                0, 1, 0, 0, 0.04, // Lift G
                0, 0, 1, 0, 0.02, // Lift B (Less) -> Warm Fade
                0, 0, 0, 1, 0,
            ]
        ];
        return null;
    }, [selectedPreset]);

    const colorFilter = React.useMemo(() => {
        const hexToRgb = (hex: string) => {
            const clean = hex.replace('#', '');
            const r = parseInt(clean.substring(0, 2), 16) / 255;
            const g = parseInt(clean.substring(2, 4), 16) / 255;
            const b = parseInt(clean.substring(4, 6), 16) / 255;
            return { r, g, b };
        };
        const { r, g, b } = hexToRgb(effectColor);
        return [
            0, 0, 0, 0, r,
            0, 0, 0, 0, g,
            0, 0, 0, 0, b,
            0, 0, 0, 1, 0
        ];
    }, [effectColor]);

    // Derived Render Groups
    const renderFilters = () => {
        if (!presetMatrix) return null;
        return (
            <Paint>
                {presetMatrix.map((m, i) => <ColorMatrix key={i} matrix={m} />)}
            </Paint>
        );
    };

    // Sync prop changes (e.g. Change Photo or New Tab Photo) - UPDATE ACTIVE SESSION
    useEffect(() => {
        if (imageUri) {
            setSessionTabs(prev => prev.map(t => t.id === activeSessionId ? {
                ...t,
                uri: imageUri,
                source: pendingSource.current || t.source // Update source if we just picked one, otherwise keep existing
            } : t));
            setCurrentUri(imageUri);

            // Clear pending source after applying
            if (pendingSource.current) pendingSource.current = null;
        }
    }, [imageUri]);

    // Image Metrics (Calculated on Load)
    const [imgMetrics, setImgMetrics] = useState({ displayedW: 0, displayedH: 0, scale: 1, offsetX: 0, offsetY: 0 });

    // Crop State (Shared Values for 60fps)
    const cropX = useSharedValue(0);
    const cropY = useSharedValue(0);
    const cropW = useSharedValue(0);
    const cropH = useSharedValue(0);
    const imageBounds = useSharedValue({ x: 0, y: 0, w: 0, h: 0 });

    // Initial Setup: Reset & Maximize
    useEffect(() => {
        if (skiaImage) {
            const originalW = skiaImage.width();
            const originalH = skiaImage.height();

            // Calculate "fit contain" dimensions
            const stageRatio = CANVAS_WIDTH / CANVAS_HEIGHT;
            const imgRatio = originalW / originalH;

            let dw, dh;
            if (imgRatio > stageRatio) {
                dw = CANVAS_WIDTH;
                dh = CANVAS_WIDTH / imgRatio;
            } else {
                dh = CANVAS_HEIGHT;
                dw = CANVAS_HEIGHT * imgRatio;
            }

            // Centering offsets
            const offX = (CANVAS_WIDTH - dw) / 2;
            const offY = (CANVAS_HEIGHT - dh) / 2;

            setImgMetrics({
                displayedW: dw,
                displayedH: dh,
                scale: originalW / dw, // Scale factor to map back to pixels
                offsetX: offX,
                offsetY: offY
            });

            // RESET CROP TO FULL IMAGE
            cropX.value = offX;
            cropY.value = offY;
            cropW.value = dw;
            cropH.value = dh;
            imageBounds.value = { x: offX, y: offY, w: dw, h: dh };
        }
    }, [skiaImage]);

    // --- GESTURES ---
    const context = useSharedValue({ x: 0, y: 0, w: 0, h: 0 });

    const dragGesture = Gesture.Pan()
        .onStart(() => {
            context.value = { x: cropX.value, y: cropY.value, w: cropW.value, h: cropH.value };
        })
        .onUpdate((e) => {
            let newX = context.value.x + e.translationX;
            let newY = context.value.y + e.translationY;

            // Clamp Drag within Image Bounds
            const bounds = imageBounds.value;
            const maxX = bounds.x + bounds.w - cropW.value;
            const maxY = bounds.y + bounds.h - cropH.value;

            cropX.value = Math.max(bounds.x, Math.min(newX, maxX));
            cropY.value = Math.max(bounds.y, Math.min(newY, maxY));
        });

    const resizeGesture = Gesture.Pan()
        .onStart(() => {
            context.value = { x: cropX.value, y: cropY.value, w: cropW.value, h: cropH.value };
        })
        .onUpdate((e) => {
            const bounds = imageBounds.value;
            const maxW = bounds.x + bounds.w - cropX.value;
            const maxH = bounds.y + bounds.h - cropY.value;

            cropW.value = Math.max(50, Math.min(context.value.w + e.translationX, maxW));
            cropH.value = Math.max(50, Math.min(context.value.h + e.translationY, maxH));
        });

    const overlayPath = useDerivedValue(() => {
        const p = Skia.Path.Make();
        p.addRect(Skia.XYWHRect(0, 0, CANVAS_WIDTH, CANVAS_HEIGHT));
        p.addRect(Skia.XYWHRect(cropX.value, cropY.value, cropW.value, cropH.value));
        p.setFillType(1); // 0=Winding, 1=EvenOdd
        return p;
    }, [cropX, cropY, cropW, cropH]);


    const handleCropAction = async () => {
        // ... (existing implementation) ... (I should not replace this block's logic, but reusing valid content to ensure match)
        if (!skiaImage) return;
        setLoading(true);
        try {
            // 1. Map Canvas Coordinates -> Original Image Pixels
            // Relative to the displayed image rect
            const relativeX = cropX.value - imgMetrics.offsetX;
            const relativeY = cropY.value - imgMetrics.offsetY;

            // Apply Scale
            const pixelX = Math.max(0, relativeX * imgMetrics.scale);
            const pixelY = Math.max(0, relativeY * imgMetrics.scale);
            const pixelW = Math.min(skiaImage.width() - pixelX, cropW.value * imgMetrics.scale);
            const pixelH = Math.min(skiaImage.height() - pixelY, cropH.value * imgMetrics.scale);

            // 2. The Muscle: ImageManipulator
            const result = await ImageManipulator.manipulateAsync(
                currentUri,
                [{
                    crop: {
                        originX: pixelX,
                        originY: pixelY,
                        width: pixelW,
                        height: pixelH
                    }
                }],
                { compress: 1, format: ImageManipulator.SaveFormat.PNG }
            );

            // 3. Update view with cropped result (don't exit)
            setCurrentUri(result.uri);

        } catch (e) {
            console.error("Crop error:", e);
            alert("Error al recortar");
        } finally {
            setLoading(false);
        }
    };

    const handleRemoveBg = async () => {
        if (!currentUri) return;
        setIsRemovingBg(true);
        try {
            const serverUrl = await uploadToTempServer(currentUri);
            if (serverUrl) {
                setTempImageUrl(serverUrl);
                setShowWebRemover(true);
            } else {
                alert("Error al subir imagen");
            }
        } catch (error) {
            console.error(error);
            alert("Error de conexión");
        } finally {
            setIsRemovingBg(false);
        }
    };

    const saveSnapshot = async (): Promise<string | null> => {
        if (!exportRef.current) return null;
        try {
            await new Promise(r => setTimeout(r, 100)); // Give Skia a moment
            const snapshot = exportRef.current.makeImageSnapshot();
            if (snapshot) {
                const base64 = snapshot.encodeToBase64(ImageFormat.PNG, 100);
                const filename = `edited_${Date.now()}.png`;
                const uri = `${FileSystem.cacheDirectory}${filename}`;
                await FileSystem.writeAsStringAsync(uri, base64, { encoding: FileSystem.EncodingType.Base64 });
                return uri;
            }
        } catch (e) {
            console.error("Snapshot error:", e);
        }
        return null;
    };

    const handleSave = async () => {
        setLoading(true);
        setIsSaving(true);

        try {
            await new Promise(r => setTimeout(r, 100));

            // 1. Save CURRENT active tab to disk (Bake it)
            const finalUri = await saveSnapshot();

            // 2. Update the session list with this new URI and current props
            // (We update props too just in case they changed since last switch)
            const finalTabs = sessionTabs.map(t => t.id === activeSessionId ? {
                ...t,
                uri: finalUri,
                // COMMIT: We baked the effect, so we reset the "live" modifiers
                preset: 'original' as const,
                effectMode: 'none' as const,
                effectColor: '#00FFFF'
            } : t);

            // 3. Filter valid
            const validTabs = finalTabs.filter(t => t.uri);

            // 4. CRITICAL: Update local state so the Editor "knows" about the baked change
            setSessionTabs(finalTabs);

            // 5. Send to parent WITHOUT closing
            onConfirm(validTabs);

            // 5. Update local state to reflect the "baked" uri?
            // saveSnapshot already returns the new baked URI.
            // If we continue editing, we are editing the BAKED image.
            // This implies "Destructive" flow (Apply Filter -> Save -> Image is now filtered forever -> Add MORE filters).
            // Users usually expect "Guardar" to just save what's there.
            // If they want to "Keep editing", they might expect the filters to remain "live".
            // BUT our `saveSnapshot` returns a PNG.
            // If we update `currentUri` to `finalUri`, we lose liveliness.
            // WORKAROUND: We do NOT update `currentUri` locally. We just send `finalUri` to parent.
            // The user continues editing `currentUri` (Raw + Live Effects).
            // BUT wait, `saveSnapshot` implementation currently DOES NOT update `currentUri`.
            // Let's check `saveSnapshot`. It returns a string. It does NOT call `setCurrentUri`.
            // So we are safe! The editor remains "Live".

        } catch (e) {
            console.error("Confirm error:", e);
        } finally {
            setLoading(false);
            setIsSaving(false);
        }
    };

    const resizeHandleStyle = useAnimatedStyle(() => ({
        transform: [
            { translateX: cropX.value + cropW.value - 15 },
            { translateY: cropY.value + cropH.value - 15 }
        ]
    }));

    if (!skiaImage) {
        return <View style={styles.loading}><ActivityIndicator color={COLORS.primary} /></View>;
    }

    return (
        <GestureHandlerRootView style={styles.container}>
            <Pressable onPress={() => { if (activeTab === 'color') setActiveTab('effects'); }} style={styles.header}>
                <TouchableOpacity onPress={onCancel} style={{ padding: 10 }}>
                    <Ionicons name="close" size={24} color="#FFF" />
                </TouchableOpacity>

                <Text style={styles.title}>EDITOR PRO</Text>
                <View style={{ width: 40 }} />
            </Pressable>

            {/* SESSION TABS STRIP */}
            {/* SESSION TABS STRIP */}
            <View style={[styles.galleryStrip, { flexDirection: 'row', alignItems: 'center', paddingRight: 10 }]}>
                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal: 15, alignItems: 'center', gap: 10 }} style={{ flex: 1 }}>
                    {sessionTabs.map((tab) => (
                        <TouchableOpacity
                            key={tab.id}
                            onPress={() => setActiveSessionId(tab.id)}
                            style={[
                                styles.sessionTab,
                                activeSessionId === tab.id && styles.sessionTabActive
                            ]}
                        >
                            <Text style={[styles.sessionTabLabel, activeSessionId === tab.id && { color: '#FFF' }]}>{tab.label}</Text>
                        </TouchableOpacity>
                    ))}

                    {/* Add Button */}
                    {sessionTabs.length < 3 && (
                        <TouchableOpacity
                            style={[
                                styles.addTabBtn,
                                (!sessionTabs[sessionTabs.length - 1].uri) && { opacity: 0.3, backgroundColor: '#111' }
                            ]}
                            onPress={handleAddTab}
                            disabled={!sessionTabs[sessionTabs.length - 1].uri}
                        >
                            <Ionicons name="add" size={20} color="#FFF" />
                        </TouchableOpacity>
                    )}
                </ScrollView>

                {/* Change Photo Button (Right Side) */}
                {onChangePhoto && (activeSession.uri) && ( // Only show if we have a photo to change
                    <TouchableOpacity onPress={() => handlePhotoSelect()} style={styles.changePhotoSmallBtn}>
                        <Ionicons name="camera-reverse" size={20} color="#999" />
                    </TouchableOpacity>
                )}
            </View>

            {/* MAIN AREA SWITCHER */}
            {
                !activeSession?.uri ? (

                    <View style={styles.emptyStateContainer}>
                        <View style={{ gap: 20 }}>
                            <Text style={styles.addPhotoText}>AÑADIR A PESTAÑA</Text>

                            <View style={{ flexDirection: 'row', gap: 30 }}>
                                {/* Camera Option */}
                                <TouchableOpacity style={styles.addPhotoBigBtn} onPress={() => handlePhotoSelect('camera')}>
                                    <View style={styles.addPhotoIconCircle}>
                                        <Ionicons name="camera" size={32} color={COLORS.primary} />
                                    </View>
                                    <Text style={styles.addPhotoSubtext}>CÁMARA</Text>
                                </TouchableOpacity>

                                {/* Gallery Option */}
                                <TouchableOpacity style={styles.addPhotoBigBtn} onPress={() => handlePhotoSelect('gallery')}>
                                    <View style={styles.addPhotoIconCircle}>
                                        <Ionicons name="images" size={32} color={COLORS.primary} />
                                    </View>
                                    <Text style={styles.addPhotoSubtext}>GALERÍA</Text>
                                </TouchableOpacity>
                            </View>
                        </View>
                    </View>
                ) : (
                    // EDITOR CONTENT
                    <View style={styles.canvasContainer}>


                        <GestureDetector gesture={dragGesture}>
                            <Animated.View style={StyleSheet.absoluteFill}>
                                <Canvas style={{ width: CANVAS_WIDTH, height: CANVAS_HEIGHT }} ref={canvasRef}>
                                    {skiaImage && (
                                        <Group>

                                            {effectMode === 'aura' && (
                                                <Group>
                                                    <Paint>
                                                        <Blur blur={30} />
                                                        <ColorMatrix matrix={colorFilter} />
                                                    </Paint>
                                                    <Image
                                                        image={skiaImage} fit="contain"
                                                        x={0} y={0}
                                                        width={CANVAS_WIDTH} height={CANVAS_HEIGHT}
                                                        opacity={1}
                                                    />
                                                </Group>
                                            )}

                                            {effectMode === 'neon' && (
                                                <Group>
                                                    {/* Outer Colored Glow */}
                                                    <Group>
                                                        <Paint>
                                                            <Blur blur={45} />
                                                            <ColorMatrix matrix={colorFilter} />
                                                        </Paint>
                                                        <Image image={skiaImage} fit="contain" x={0} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} />
                                                    </Group>

                                                    {/* Inner White Tube Core */}
                                                    <Group>
                                                        <Paint>
                                                            <Blur blur={10} />
                                                            <ColorMatrix matrix={[
                                                                0, 0, 0, 0, 255,
                                                                0, 0, 0, 0, 255,
                                                                0, 0, 0, 0, 255,
                                                                0, 0, 0, 1, 0
                                                            ]} />
                                                        </Paint>
                                                        <Image image={skiaImage} fit="contain" x={0} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} />
                                                    </Group>
                                                </Group>
                                            )}

                                            {effectMode === 'border' && (
                                                <Group layer>
                                                    <Group origin={{ x: CANVAS_WIDTH / 2, y: CANVAS_HEIGHT / 2 }}>
                                                        <Paint>
                                                            <Morphology radius={4} />
                                                            <ColorMatrix matrix={colorFilter} />
                                                        </Paint>
                                                        <Image image={skiaImage} fit="contain" x={0} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} />
                                                    </Group>
                                                </Group>
                                            )}

                                            {effectMode === 'ghost' && (
                                                <Group>
                                                    <Paint><ColorMatrix matrix={colorFilter} /></Paint>
                                                    <Image
                                                        image={skiaImage} fit="contain"
                                                        x={-20} y={0}
                                                        width={CANVAS_WIDTH} height={CANVAS_HEIGHT}
                                                        opacity={0.6}
                                                    />
                                                    <Image
                                                        image={skiaImage} fit="contain"
                                                        x={20} y={0}
                                                        width={CANVAS_WIDTH} height={CANVAS_HEIGHT}
                                                        opacity={0.6}
                                                    />
                                                </Group>
                                            )}

                                            {effectMode === 'glitch' && (
                                                <Group origin={{ x: CANVAS_WIDTH / 2, y: CANVAS_HEIGHT / 2 }} transform={[{ scale: 1.05 }]}>
                                                    <Group layer={<Paint><ColorMatrix matrix={[1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0]} /></Paint>}>
                                                        <Image image={skiaImage} fit="contain" x={-15} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} opacity={0.8} />
                                                    </Group>
                                                    <Group layer={<Paint><ColorMatrix matrix={[0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0]} /></Paint>}>
                                                        <Image image={skiaImage} fit="contain" x={15} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} opacity={0.8} />
                                                    </Group>
                                                </Group>
                                            )}

                                            <Group layer={renderFilters()}>
                                                <Image
                                                    image={skiaImage}
                                                    fit="contain"
                                                    x={0} y={0}
                                                    width={CANVAS_WIDTH} height={CANVAS_HEIGHT}
                                                />
                                            </Group>



                                        </Group>
                                    )}

                                    {/* UI OVERLAYS (Crop Box, Guides) - Only on Interactive Canvas */}
                                    <Group>
                                        {/* Dark Overlay with Hole */}
                                        <Path path={overlayPath} color="rgba(0,0,0,0.8)" />

                                        {/* Border of Crop Box - Neon Solid Style */}
                                        <Rect x={cropX} y={cropY} width={cropW} height={cropH} style="stroke" strokeWidth={6} color={COLORS.primary} strokeJoin="round" strokeCap="round">
                                            <Blur blur={2} />
                                        </Rect>
                                        <Rect x={cropX} y={cropY} width={cropW} height={cropH} style="stroke" strokeWidth={3} color="#FFFFFF" strokeJoin="round" strokeCap="round" />
                                    </Group>
                                </Canvas>

                                {/* 
                                    HIDDEN EXPORT CANVAS 
                                    - Same Size
                                    - Same Image/Effects
                                    - NO Overlays
                                    - Positioned absolutely but zIndex -1 (invisible to user)
                                */}
                                <Canvas
                                    ref={exportRef}
                                    style={{
                                        position: 'absolute', top: 0, left: 0,
                                        width: CANVAS_WIDTH, height: CANVAS_HEIGHT,
                                        opacity: 0, zIndex: -100 // Hidden but rendered
                                    }}
                                >
                                    {skiaImage && (
                                        <Group>
                                            {/* Reuse the EXACT SAME rendering logic as above */}
                                            {effectMode === 'aura' && (
                                                <Group>
                                                    <Paint>
                                                        <Blur blur={30} />
                                                        <ColorMatrix matrix={colorFilter} />
                                                    </Paint>
                                                    <Image image={skiaImage} fit="contain" x={0} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} opacity={1} />
                                                </Group>
                                            )}

                                            {effectMode === 'neon' && (
                                                <Group>
                                                    <Group>
                                                        <Paint>
                                                            <Blur blur={45} />
                                                            <ColorMatrix matrix={colorFilter} />
                                                        </Paint>
                                                        <Image image={skiaImage} fit="contain" x={0} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} />
                                                    </Group>
                                                    <Group>
                                                        <Paint>
                                                            <Blur blur={10} />
                                                            <ColorMatrix matrix={[0, 0, 0, 0, 255, 0, 0, 0, 0, 255, 0, 0, 0, 0, 255, 0, 0, 0, 1, 0]} />
                                                        </Paint>
                                                        <Image image={skiaImage} fit="contain" x={0} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} />
                                                    </Group>
                                                </Group>
                                            )}

                                            {effectMode === 'border' && (
                                                <Group layer>
                                                    <Group origin={{ x: CANVAS_WIDTH / 2, y: CANVAS_HEIGHT / 2 }}>
                                                        <Paint>
                                                            <Morphology radius={4} />
                                                            <ColorMatrix matrix={colorFilter} />
                                                        </Paint>
                                                        <Image image={skiaImage} fit="contain" x={0} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} />
                                                    </Group>
                                                </Group>
                                            )}

                                            {effectMode === 'ghost' && (
                                                <Group>
                                                    <Paint><ColorMatrix matrix={colorFilter} /></Paint>
                                                    <Image image={skiaImage} fit="contain" x={-20} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} opacity={0.6} />
                                                    <Image image={skiaImage} fit="contain" x={20} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} opacity={0.6} />
                                                </Group>
                                            )}

                                            {effectMode === 'glitch' && (
                                                <Group origin={{ x: CANVAS_WIDTH / 2, y: CANVAS_HEIGHT / 2 }} transform={[{ scale: 1.05 }]}>
                                                    <Group layer={<Paint><ColorMatrix matrix={[1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0]} /></Paint>}>
                                                        <Image image={skiaImage} fit="contain" x={-15} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} opacity={0.8} />
                                                    </Group>
                                                    <Group layer={<Paint><ColorMatrix matrix={[0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0]} /></Paint>}>
                                                        <Image image={skiaImage} fit="contain" x={15} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} opacity={0.8} />
                                                    </Group>
                                                </Group>
                                            )}

                                            <Group layer={renderFilters()}>
                                                <Image image={skiaImage} fit="contain" x={0} y={0} width={CANVAS_WIDTH} height={CANVAS_HEIGHT} />
                                            </Group>
                                        </Group>
                                    )}
                                </Canvas>
                            </Animated.View>
                        </GestureDetector>

                        {/* Resizer Handle */}
                        <GestureDetector gesture={resizeGesture}>
                            <Animated.View style={[styles.resizeHandle, resizeHandleStyle]}>
                                <Ionicons name="resize" size={18} color="#000" />
                            </Animated.View>
                        </GestureDetector>

                        {loading && (
                            <View style={StyleSheet.absoluteFillObject}>
                                <View style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', alignItems: 'center' }}>
                                    <ActivityIndicator size="large" color={COLORS.primary} />
                                    <Text style={{ color: '#FFF', marginTop: 10 }}>Procesando...</Text>
                                </View>
                            </View>
                        )}
                    </View>
                )
            }

            {/* --- NEW TABBED UI CONTROLS --- */}
            <View style={{ backgroundColor: '#121212', borderTopLeftRadius: 30, borderTopRightRadius: 30, marginTop: -10, paddingBottom: 10, paddingTop: 10 }}>

                {/* TABS HEADER */}
                <View style={{ flexDirection: 'row', alignItems: 'center', borderBottomWidth: 1, borderBottomColor: '#222', paddingHorizontal: 10 }}>
                    <View style={{ flex: 1, flexDirection: 'row', justifyContent: 'space-around' }}>
                        <TouchableOpacity
                            onPress={() => setActiveTab('filters')}
                            style={{ paddingVertical: 12, borderBottomWidth: 2, borderBottomColor: activeTab === 'filters' ? COLORS.primary : 'transparent', minWidth: 60, alignItems: 'center' }}
                        >
                            <Text style={{ color: activeTab === 'filters' ? COLORS.primary : '#666', fontWeight: 'bold', fontSize: 11, letterSpacing: 0.5 }}>FILTROS</Text>
                        </TouchableOpacity>
                        <TouchableOpacity
                            onPress={() => setActiveTab('effects')}
                            style={{ paddingVertical: 12, borderBottomWidth: 2, borderBottomColor: activeTab === 'effects' ? COLORS.primary : 'transparent', minWidth: 60, alignItems: 'center' }}
                        >
                            <Text style={{ color: activeTab === 'effects' ? COLORS.primary : '#666', fontWeight: 'bold', fontSize: 11, letterSpacing: 0.5 }}>EFECTOS</Text>
                        </TouchableOpacity>
                        <TouchableOpacity
                            onPress={() => setActiveTab('color')}
                            style={{ paddingVertical: 12, borderBottomWidth: 2, borderBottomColor: activeTab === 'color' ? COLORS.primary : 'transparent', minWidth: 60, alignItems: 'center' }}
                        >
                            <Text style={{ color: activeTab === 'color' ? COLORS.primary : '#666', fontWeight: 'bold', fontSize: 11, letterSpacing: 0.5 }}>COLOR</Text>
                        </TouchableOpacity>
                    </View>

                    {/* RESET BUTTON - Absolute right or flex end? Flex end is safer */}
                    <TouchableOpacity
                        onPress={() => {
                            if (activeSession.originalUri) {
                                setCurrentUri(activeSession.originalUri);
                                setSelectedPreset('original');
                                setEffectMode('none');
                                // Update State immediately
                                setSessionTabs(prev => prev.map(t => t.id === activeSessionId ? {
                                    ...t,
                                    uri: activeSession.originalUri, // Revert to original
                                    preset: 'original' as const,
                                    effectMode: 'none' as const
                                } : t));
                            }
                        }}
                        style={{
                            marginLeft: 10,
                            padding: 6,
                            backgroundColor: '#222',
                            borderRadius: 6,
                            borderWidth: 1,
                            borderColor: '#333'
                        }}
                    >
                        <Ionicons name="refresh" size={14} color="#666" />
                    </TouchableOpacity>
                </View>

                {/* TAB CONTENT AREA */}
                <View style={{ height: 50, justifyContent: 'center', paddingTop: 10 }}>

                    {/* 1. FILTERS TAB */}
                    {activeTab === 'filters' && (
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal: 20, gap: 10, alignItems: 'center' }}>
                            {([
                                { id: 'grit', label: 'GRIT', icon: 'contrast' },
                                { id: 'vibrant', label: 'VIVO', icon: 'sunny' },
                                { id: 'bw', label: 'B&N', icon: 'moon' },
                                { id: 'cinematic', label: 'CINE', icon: 'film' },
                                { id: 'vintage', label: 'ANTIGUO', icon: 'hourglass' }
                            ] as const).map(p => (
                                <Pressable
                                    key={p.id}
                                    style={[
                                        styles.presetChip,
                                        selectedPreset === p.id && { borderColor: COLORS.primary, backgroundColor: 'rgba(0,255,255,0.1)' }
                                    ]}
                                    onPress={() => setSelectedPreset(selectedPreset === p.id ? 'original' : p.id)}
                                >
                                    <View style={{ width: 24, height: 24, borderRadius: 6, backgroundColor: '#333', justifyContent: 'center', alignItems: 'center' }}>
                                        <Ionicons name={p.icon as any} size={14} color={selectedPreset === p.id ? COLORS.primary : '#888'} />
                                    </View>
                                    <Text style={[styles.presetLabel, selectedPreset === p.id && { color: COLORS.primary }]}>{p.label}</Text>
                                </Pressable>
                            ))}
                        </ScrollView>
                    )}

                    {/* 2. EFFECTS TAB */}
                    {activeTab === 'effects' && (
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal: 20, gap: 10, alignItems: 'center' }}>
                            {['aura', 'neon', 'border', 'ghost', 'glitch'].map((mode) => (
                                <Pressable
                                    key={mode}
                                    style={[
                                        styles.presetChip,
                                        effectMode === mode && { borderColor: effectColor, backgroundColor: 'rgba(255,255,255,0.1)' }
                                    ]}
                                    onPress={() => setEffectMode(effectMode === mode ? 'none' : mode as any)}
                                >
                                    {/* Icon Box */}
                                    <View style={{ width: 24, height: 24, borderRadius: 6, backgroundColor: '#333', justifyContent: 'center', alignItems: 'center' }}>
                                        <Ionicons
                                            name={mode === 'aura' ? 'sparkles' : mode === 'neon' ? 'flash' : mode === 'border' ? 'scan' : mode === 'ghost' ? 'copy' : 'barcode'}
                                            size={14}
                                            color={effectMode === mode ? effectColor : "#888"}
                                        />
                                    </View>
                                    <Text style={[styles.presetLabel, effectMode === mode && { color: '#FFF' }]}>
                                        {mode.toUpperCase()}
                                    </Text>
                                </Pressable>
                            ))}
                        </ScrollView>
                    )}

                    {/* 3. COLOR TAB */}
                    {activeTab === 'color' && (
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal: 20, gap: 12, alignItems: 'center' }}>
                            {/* Primary, Secondary, Tertiary & Neutrals */}
                            {EFFECT_COLORS.map(c => (
                                <Pressable
                                    key={c}
                                    onPress={() => {
                                        setEffectColor(c);
                                        setActiveTab('effects'); // Auto-switch to Effects 
                                    }}
                                    style={{
                                        width: 40, height: 40, borderRadius: 20, backgroundColor: c,
                                        borderWidth: effectColor === c ? 3 : 0, borderColor: '#FFF',
                                        shadowColor: c, shadowOpacity: 0.5, shadowRadius: 10, elevation: 5
                                    }}
                                />
                            ))}
                        </ScrollView>
                    )}

                </View>
            </View>

            <View style={styles.footer}>
                <TouchableOpacity style={styles.btnAction} onPress={handleCropAction}>
                    <Ionicons name="crop" size={20} color="#000" />
                    <Text style={styles.btnTextArgs}>CORTAR</Text>
                </TouchableOpacity>

                <TouchableOpacity style={[styles.btnAction, isRemovingBg && { opacity: 0.5 }]} onPress={handleRemoveBg} disabled={isRemovingBg}>
                    {isRemovingBg ? <ActivityIndicator size="small" color="#000" /> : <Ionicons name="sparkles" size={20} color="#000" />}
                    <Text style={styles.btnTextArgs}>{isRemovingBg ? "IA..." : "QUITAR FONDO"}</Text>
                </TouchableOpacity>

                <TouchableOpacity style={styles.btnFinish} onPress={handleSave}>
                    <Ionicons name="save" size={24} color="#FFF" />
                    <Text style={styles.btnTextWhite}>GUARDAR</Text>
                </TouchableOpacity>
            </View>

            {/* WEBVIEW PROCESSOR */}
            <BackgroundRemoverWebView
                visible={showWebRemover}
                imageUrl={tempImageUrl}
                onClose={() => setShowWebRemover(false)}
                onImageProcessed={async (remoteUrl: string) => {
                    try {
                        if (remoteUrl.startsWith('data:')) {
                            const filename = `bg_removed_${Date.now()}.png`;
                            const localPath = `${FileSystem.cacheDirectory}${filename}`;
                            const base64Part = remoteUrl.split(',')[1];
                            await FileSystem.writeAsStringAsync(localPath, base64Part, {
                                encoding: FileSystem.EncodingType.Base64
                            });
                            setCurrentUri(localPath);
                        } else {
                            // Fallback if URL
                            setCurrentUri(remoteUrl);
                        }
                    } catch (err) {
                        console.error("Error saving processed image:", err);
                        alert("Error al guardar imagen");
                    } finally {
                        setShowWebRemover(false);
                    }
                }}
            />
        </GestureHandlerRootView >
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#000' },
    loading: { flex: 1, backgroundColor: '#000', justifyContent: 'center', alignItems: 'center' },
    header: {
        height: 45, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
        backgroundColor: '#111', paddingHorizontal: 10,
        borderBottomWidth: 1, borderBottomColor: '#222'
    },
    title: { color: COLORS.primary, fontWeight: '900', letterSpacing: 1, fontSize: 16 },
    galleryStrip: { height: 50, backgroundColor: '#0A0A0A', borderBottomWidth: 1, borderBottomColor: '#1A1A1A' },
    sessionTab: { paddingVertical: 6, paddingHorizontal: 12, borderRadius: 6, backgroundColor: '#151515', borderWidth: 1, borderColor: '#333' },
    sessionTabActive: { backgroundColor: '#333', borderColor: COLORS.primary },
    sessionTabLabel: { color: '#666', fontSize: 11, fontWeight: 'bold' },
    addTabBtn: { width: 30, height: 28, borderRadius: 6, backgroundColor: '#222', justifyContent: 'center', alignItems: 'center', borderWidth: 1, borderColor: '#333' },
    changePhotoSmallBtn: { width: 32, height: 32, borderRadius: 16, backgroundColor: '#1A1A1A', justifyContent: 'center', alignItems: 'center', borderWidth: 1, borderColor: '#333', marginLeft: 8 },

    emptyStateContainer: { flex: 1, backgroundColor: '#111', justifyContent: 'center', alignItems: 'center' },
    addPhotoBigBtn: { alignItems: 'center', gap: 10 },
    addPhotoIconCircle: { width: 70, height: 70, borderRadius: 35, backgroundColor: 'rgba(255, 215, 0, 0.1)', justifyContent: 'center', alignItems: 'center', borderWidth: 1, borderColor: COLORS.primary },
    addPhotoText: { color: COLORS.primary, fontWeight: 'bold', fontSize: 16, letterSpacing: 1 },
    addPhotoSubtext: { color: '#666', fontSize: 12 },

    canvasContainer: { flex: 1, width: CANVAS_WIDTH, overflow: 'hidden', backgroundColor: '#111', justifyContent: 'center' },
    resizeHandle: {
        position: 'absolute', width: 28, height: 28, backgroundColor: COLORS.primary,
        borderRadius: 15, justifyContent: 'center', alignItems: 'center',
        top: 0, left: 0
    },

    footer: { flexDirection: 'row', justifyContent: 'space-evenly', alignItems: 'center', paddingBottom: 10, paddingTop: 0, backgroundColor: '#111' },
    btnAction: {
        flexDirection: 'row', paddingVertical: 4, paddingHorizontal: 12, borderRadius: 12, backgroundColor: COLORS.primary,
        alignItems: 'center', justifyContent: 'center', minWidth: 80, gap: 6
    },
    btnFinish: {
        flexDirection: 'row', paddingVertical: 4, paddingHorizontal: 12, borderRadius: 12, backgroundColor: '#222',
        alignItems: 'center', justifyContent: 'center', minWidth: 80, gap: 6, borderWidth: 1, borderColor: '#444'
    },
    btnTextArgs: { color: '#000', fontWeight: '900', fontSize: 10 },
    btnTextWhite: { color: '#FFF', fontWeight: '900', fontSize: 10 },

    // Effects UI
    filterScroll: { marginBottom: 5, maxHeight: 40 },
    filterScrollContent: { gap: 8, paddingHorizontal: 15 },
    presetChip: {
        flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
        paddingVertical: 4, paddingHorizontal: 12,
        borderTopLeftRadius: 5, borderTopRightRadius: 20, borderBottomLeftRadius: 20, borderBottomRightRadius: 5,
        backgroundColor: '#252525', borderWidth: 1, borderColor: '#3A3A3A', // Lighter background for better contrast
        minWidth: 90, gap: 8
    },
    presetChipActive: { borderColor: COLORS.primary, backgroundColor: 'rgba(0, 255, 255, 0.15)' }, // Subtle cyan glow
    presetLabel: { color: '#999', fontSize: 10, fontWeight: 'bold' },

    effectsScroll: { maxHeight: 40 },
    effectsScrollContent: { gap: 4, paddingHorizontal: 5, alignItems: 'center' },
    effectChip: { flexDirection: 'row', alignItems: 'center', gap: 3, paddingVertical: 6, paddingHorizontal: 8, borderRadius: 20, borderWidth: 1, borderColor: '#333', backgroundColor: '#111' },
    effectLabel: { color: '#666', fontSize: 10, fontWeight: 'bold' },
    colorTrigger: { flexDirection: 'row', alignItems: 'center', gap: 4, padding: 4, paddingRight: 6, borderRadius: 15, borderWidth: 1, borderColor: '#333', backgroundColor: '#111' },
    colorPreview: { width: 22, height: 22, borderRadius: 11 },

    floatingPalette: {
        position: 'absolute', bottom: 120, left: 20, right: 20,
        backgroundColor: '#151515', borderRadius: 20, padding: 15,
        borderWidth: 1, borderColor: '#333', elevation: 10, zIndex: 100
    },
    paletteHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    paletteTitle: { color: '#FFF', fontSize: 10, fontWeight: 'bold', letterSpacing: 1 },
    colorGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12, justifyContent: 'center' },
    colorCircle: { width: 36, height: 36, borderRadius: 18, borderWidth: 2, borderColor: 'transparent' },
    colorCircleActive: { borderColor: '#FFF', transform: [{ scale: 1.1 }] },
});
