import React, { useState, useEffect, useRef } from 'react';
import { View, Text, Modal, Image, Animated, StyleSheet, Dimensions, PanResponder, ActivityIndicator, Platform, Pressable, useWindowDimensions, ScrollView, UIManager } from 'react-native';
import * as ImageManipulator from 'expo-image-manipulator';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS, SPACING, SHADOWS } from '../../../constants/theme';
import { useBackgroundRemoval } from '../../../hooks/useBackgroundRemoval';
import { BackgroundRemoverWebView } from '../../../components/common/BackgroundRemoverWebView';
import * as Haptics from 'expo-haptics';
import {
    ColorMatrix,
    concatColorMatrices,
    contrast,
    saturate,
    brightness
} from 'react-native-color-matrix-image-filters';

const { width: SCREEN_WIDTH, height: SCREEN_HEIGHT } = Dimensions.get('window');

// Safety Check: Detect if the native module for filters is actually linked/available (prevents Red Screen in Expo Go)
const HAS_NATIVE_FILTERS = Platform.OS !== 'web' && !!UIManager.getViewManagerConfig('CMIFColorMatrixImageFilter');

interface ImageCropperProps {
    visible: boolean;
    imageUri: string | null;
    onClose: () => void;
    onCrop: (uri: string, preset: string, effect: string, effectColor: string) => void;
    onChangePhoto?: () => void;
}

// For Native, we will use the palette row instead of HexColorPicker for now to avoid DOM issues
// import { HexColorPicker } from "react-colorful"; // REMOVED FOR NATIVE

// Helper types
type Point = { x: number; y: number };
type Size = { width: number; height: number };
type Rect = Point & Size;

export const ImageCropper: React.FC<ImageCropperProps> = ({
    visible,
    imageUri,
    onClose,
    onCrop,
    onChangePhoto
}) => {
    const { width: WINDOW_WIDTH, height: WINDOW_HEIGHT } = useWindowDimensions();

    // State
    const [currentImageUri, setCurrentImageUri] = useState<string | null>(imageUri);
    const [ready, setReady] = useState(false);
    const { removeBackground, uploadToTempServer, isProcessing: isRemovingBg, isLibReady } = useBackgroundRemoval();
    const insets = useSafeAreaInsets();

    // WebView Modal State
    const [showWebRemover, setShowWebRemover] = useState(false);
    const [tempImageUrl, setTempImageUrl] = useState<string | null>(null);

    // Dimensions
    const [imageSize, setImageSize] = useState<Size>({ width: 0, height: 0 });
    const [displayImageSize, setDisplayImageSize] = useState<Size>({ width: 0, height: 0 });

    // REFS for logic (Stale Closure Fix & Performance)
    const displayImageSizeRef = useRef<Size>({ width: 0, height: 0 });
    const cropRef = useRef<Rect>({ x: 0, y: 0, width: 100, height: 100 });

    // ANIMATED VALUES for Crop Box (Smoothness on Native)
    const animX = useRef(new Animated.Value(0)).current;
    const animY = useRef(new Animated.Value(0)).current;
    const animW = useRef(new Animated.Value(100)).current;
    const animH = useRef(new Animated.Value(100)).current;

    // Filter & Effects State
    const [selectedPreset, setSelectedPreset] = useState<'original' | 'grit' | 'vibrant' | 'bw' | 'cinematic'>('original');
    const [effectMode, setEffectMode] = useState<'none' | 'aura' | 'neon' | 'ghost' | 'glitch'>('none');
    const [effectColor, setEffectColor] = useState<string>('#00FFFF');
    const [showColorPicker, setShowColorPicker] = useState(false);
    const gridOpacity = useRef(new Animated.Value(0)).current;

    // PRESET MATRICES (Parity with Web CSS filters)
    const getFilterMatrix = (preset: string): any => {
        if (preset === 'original') return undefined;
        try {
            // FIX: concatColorMatrices takes REST arguments, not an array
            if (preset === 'grit') return concatColorMatrices(contrast(1.4), saturate(0.6), brightness(0.9));
            if (preset === 'vibrant') return concatColorMatrices(contrast(1.1), saturate(1.5), brightness(1.1));
            if (preset === 'bw') return concatColorMatrices(contrast(1.2), saturate(0), brightness(1));
            if (preset === 'cinematic') return concatColorMatrices(contrast(1.5), saturate(0.8), brightness(0.85));
        } catch (e) {
            console.error('Filter matrix error:', e);
        }
        return undefined;
    };

    const STAGE_WIDTH = WINDOW_WIDTH > 600 ? 500 : Math.min(WINDOW_WIDTH, 600);
    const STAGE_HEIGHT = Math.max(300, WINDOW_HEIGHT - 320);

    // Sync Refs with Animated Values for Logic
    useEffect(() => {
        const lx = animX.addListener(({ value }) => { cropRef.current.x = value; });
        const ly = animY.addListener(({ value }) => { cropRef.current.y = value; });
        const lw = animW.addListener(({ value }) => { cropRef.current.width = value; });
        const lh = animH.addListener(({ value }) => { cropRef.current.height = value; });
        return () => {
            animX.removeListener(lx); animY.removeListener(ly);
            animW.removeListener(lw); animH.removeListener(lh);
        };
    }, []);

    useEffect(() => { displayImageSizeRef.current = displayImageSize; }, [displayImageSize]);

    // Initialization
    useEffect(() => {
        if (visible) {
            setSelectedPreset('original');
            setEffectMode('none');
            setEffectColor('#00FFFF');
            if (imageUri) setCurrentImageUri(imageUri);
        }
    }, [visible, imageUri]);

    useEffect(() => {
        const uriToLoad = currentImageUri || imageUri;
        if (visible && uriToLoad) {
            setReady(false);
            (async () => {
                try {
                    const info = await ImageManipulator.manipulateAsync(uriToLoad, [], {});
                    const { width: w, height: h } = info;
                    setImageSize({ width: w, height: h });

                    const stageRatio = STAGE_WIDTH / STAGE_HEIGHT;
                    const imgRatio = w / h;
                    let dw, dh;
                    if (imgRatio > stageRatio) {
                        dw = STAGE_WIDTH; dh = STAGE_WIDTH / imgRatio;
                    } else {
                        dh = STAGE_HEIGHT; dw = STAGE_HEIGHT * imgRatio;
                    }
                    setDisplayImageSize({ width: dw, height: dh });

                    // Initial Crop Box (Full Fit)
                    animX.setValue(0);
                    animY.setValue(0);
                    animW.setValue(dw);
                    animH.setValue(dh);
                    cropRef.current = { x: 0, y: 0, width: dw, height: dh };
                    setReady(true);
                } catch (err) {
                    console.error("Size detection error:", err);
                    onClose();
                }
            })();
        }
    }, [visible, currentImageUri, imageUri]);

    const lastCropStart = useRef<Rect>({ x: 0, y: 0, width: 0, height: 0 });

    // -------------------------------------------------------------
    // SINGLE PAN RESPONDER (Professional Universal Controller)
    // -------------------------------------------------------------
    const activeHandle = useRef<'MOVE' | 'TL' | 'TR' | 'BL' | 'BR' | 'NONE'>('NONE');

    const mainResponder = useRef(
        PanResponder.create({
            onStartShouldSetPanResponder: () => true,
            onMoveShouldSetPanResponder: () => true,
            onPanResponderGrant: (e, gesture) => {
                // We use locationX/Y relative to the stage
                const { locationX: lx, locationY: ly } = e.nativeEvent;
                const { x, y, width, height } = cropRef.current;
                const threshold = 60; // Pro Precision: much larger hit-box for easier grabbing

                lastCropStart.current = { ...cropRef.current };
                Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
                Animated.timing(gridOpacity, { toValue: 0.8, duration: 200, useNativeDriver: true }).start();

                // Corner Detection logic
                if (lx < x + threshold && ly < y + threshold) activeHandle.current = 'TL';
                else if (lx > x + width - threshold && ly < y + threshold) activeHandle.current = 'TR';
                else if (lx < x + threshold && ly > y + height - threshold) activeHandle.current = 'BL';
                else if (lx > x + width - threshold && ly > y + height - threshold) activeHandle.current = 'BR';
                else if (lx >= x && lx <= x + width && ly >= y && ly <= y + height) activeHandle.current = 'MOVE';
                else activeHandle.current = 'NONE';
            },
            onPanResponderMove: (e, gesture) => {
                if (activeHandle.current === 'NONE') return;

                const prev = lastCropStart.current;
                const { width: dw, height: dh } = displayImageSizeRef.current;
                const min = 50;
                let nx = prev.x, ny = prev.y, nw = prev.width, nh = prev.height;

                if (activeHandle.current === 'MOVE') {
                    nx = Math.max(0, Math.min(dw - prev.width, prev.x + gesture.dx));
                    ny = Math.max(0, Math.min(dh - prev.height, prev.y + gesture.dy));
                } else {
                    // RESIZE LOGIC
                    if (activeHandle.current === 'BR') {
                        nw = Math.max(min, Math.min(dw - nx, prev.width + gesture.dx));
                        nh = Math.max(min, Math.min(dh - ny, prev.height + gesture.dy));
                    } else if (activeHandle.current === 'BL') {
                        const dx = Math.min(prev.width - min, Math.max(-prev.x, gesture.dx));
                        nx = prev.x + dx;
                        nw = prev.width - dx;
                        nh = Math.max(min, Math.min(dh - ny, prev.height + gesture.dy));
                    } else if (activeHandle.current === 'TR') {
                        const dy = Math.min(prev.height - min, Math.max(-prev.y, gesture.dy));
                        ny = prev.y + dy;
                        nh = prev.height - dy;
                        nw = Math.max(min, Math.min(dw - nx, prev.width + gesture.dx));
                    } else if (activeHandle.current === 'TL') {
                        const dx = Math.min(prev.width - min, Math.max(-prev.x, gesture.dx));
                        const dy = Math.min(prev.height - min, Math.max(-prev.y, gesture.dy));
                        nx = prev.x + dx;
                        nw = prev.width - dx;
                        ny = prev.y + dy;
                        nh = prev.height - dy;
                    }
                }

                cropRef.current = { x: nx, y: ny, width: nw, height: nh };
                animX.setValue(nx); animY.setValue(ny);
                animW.setValue(nw); animH.setValue(nh);
            },
            onPanResponderRelease: () => {
                activeHandle.current = 'NONE';
                Animated.timing(gridOpacity, { toValue: 0, duration: 400, useNativeDriver: true }).start();
            }
        })
    ).current;

    const performCrop = async (shouldClose: boolean = false) => {
        if (!currentImageUri || !ready) return;

        try {
            // Use ImageManipulator for dimensions to ensure EXIF consistency
            const info = await ImageManipulator.manipulateAsync(currentImageUri!, [], {});
            const { width: w, height: h } = info;

            const scaleX = w / displayImageSize.width;
            const scaleY = h / displayImageSize.height;
            const { x, y, width, height } = cropRef.current;

            const finalX = Math.round(x * scaleX);
            const finalY = Math.round(y * scaleY);
            const finalW = Math.max(1, Math.min(w - finalX, Math.round(width * scaleX)));
            const finalH = Math.max(1, Math.min(h - finalY, Math.round(height * scaleY)));

            // FLICKER FIX: If the crop is actually the full image (or very close), skip the operation
            const isFullImage = finalX <= 2 && finalY <= 2 && Math.abs(finalW - w) <= 2 && Math.abs(finalH - h) <= 2;

            if (isFullImage) {
                console.log(`[CROP] Skipping redundant full-image crop to avoid flicker.`);
                if (shouldClose) {
                    onCrop(currentImageUri, selectedPreset, effectMode, effectColor);
                }
                return;
            }

            console.log(`[CROP] ${w}x${h} @ ${scaleX.toFixed(2)}x -> ${finalX},${finalY} ${finalW}x${finalH}`);

            const result = await ImageManipulator.manipulateAsync(
                currentImageUri!,
                [{ crop: { originX: finalX, originY: finalY, width: finalW, height: finalH } }],
                { compress: 1, format: ImageManipulator.SaveFormat.PNG }
            );

            if (shouldClose) {
                onCrop(result.uri, selectedPreset, effectMode, effectColor);
            } else {
                setCurrentImageUri(result.uri);
            }
        } catch (error) {
            console.error('Crop error:', error);
        }
    };

    const handleCut = () => performCrop(false);
    const handleConfirm = async () => { if (currentImageUri) await performCrop(true); };

    const handleRemoveBg = async () => {
        if (!currentImageUri) return;

        if (Platform.OS === 'web') {
            try {
                const result = await removeBackground(currentImageUri);
                setCurrentImageUri(result);
            } catch (error) { console.log('Bg removal error:', error); }
        } else {
            // NATIVE STRATEGY: Upload -> WebView
            const url = await uploadToTempServer(currentImageUri);
            if (url) {
                setTempImageUrl(url);
                setShowWebRemover(true);
            } else {
                alert("Error subiendo imagen temporal");
            }
        }
    };

    if (!visible) return null;

    return (
        <Modal visible={visible} transparent animationType="fade" onRequestClose={onClose}>
            <View style={styles.container}>
                <View style={[styles.contentWrapper, Platform.OS === 'web' && { width: Math.min(WINDOW_WIDTH, 500), height: Math.min(WINDOW_HEIGHT, 850), borderRadius: 20, borderWidth: 1 }]}>
                    <View style={[styles.header, { paddingTop: insets.top }]}>
                        <Text style={styles.headerTitle}>EDITAR IMAGEN</Text>
                        <Pressable onPress={onClose} style={[styles.closeBtn, { top: insets.top }]}>
                            <Ionicons name="close" size={24} color="#FFF" />
                        </Pressable>
                    </View>

                    <View style={{ width: STAGE_WIDTH, height: STAGE_HEIGHT, position: 'relative', justifyContent: 'center', alignItems: 'center', backgroundColor: '#000' }}>
                        {ready ? (
                            <View
                                {...mainResponder.panHandlers}
                                style={{ width: displayImageSize.width, height: displayImageSize.height, position: 'relative', overflow: 'hidden' }}
                            >
                                {/* THE IMAGE (Wrapped with Color Matrices for real-time filters - Guarded against crashes) */}
                                {(selectedPreset !== 'original' && HAS_NATIVE_FILTERS) ? (
                                    <ColorMatrix matrix={getFilterMatrix(selectedPreset)}>
                                        <Image source={{ uri: currentImageUri! }} style={{ width: displayImageSize.width, height: displayImageSize.height }} resizeMode="contain" />
                                    </ColorMatrix>
                                ) : (
                                    <Image source={{ uri: currentImageUri! }} style={{ width: displayImageSize.width, height: displayImageSize.height }} resizeMode="contain" />
                                )}

                                {/* SHADOW OVERLAYS (Professional Dimming - Fixed with 1px overlap to prevent pixel bleeding) */}
                                <Animated.View style={[styles.overlay, { top: 0, left: 0, right: 0, height: Animated.add(animY, 1) }]} pointerEvents="none" />
                                <Animated.View style={[styles.overlay, { top: Animated.add(Animated.add(animY, animH), -1), left: 0, right: 0, bottom: 0 }]} pointerEvents="none" />
                                <Animated.View style={[styles.overlay, { top: animY, left: 0, width: Animated.add(animX, 1), height: animH }]} pointerEvents="none" />
                                <Animated.View style={[styles.overlay, { top: animY, left: Animated.add(Animated.add(animX, animW), -1), right: 0, height: animH }]} pointerEvents="none" />

                                {/* THE CROP BOX CONTAINER (PointerEvents none so it doesn't block background mainResponder) */}
                                <Animated.View
                                    style={{
                                        position: 'absolute',
                                        left: animX,
                                        top: animY,
                                        width: animW,
                                        height: animH,
                                        borderColor: 'rgba(255,255,255,0.6)',
                                        borderWidth: 1,
                                        zIndex: 10,
                                        overflow: 'visible'
                                    }}
                                    pointerEvents="none"
                                >
                                    {/* GRID LINES (3x3 Guide - Animated Opacity) */}
                                    <Animated.View style={[styles.gridLineV, { opacity: gridOpacity, left: '33.3%' }]} pointerEvents="none" />
                                    <Animated.View style={[styles.gridLineV, { left: '66.6%', opacity: gridOpacity }]} pointerEvents="none" />
                                    <Animated.View style={[styles.gridLineH, { opacity: gridOpacity, top: '33.3%' }]} pointerEvents="none" />
                                    <Animated.View style={[styles.gridLineH, { top: '66.6%', opacity: gridOpacity }]} pointerEvents="none" />

                                    {/* VISUAL CORNERS (L-Shaped - WhatsApp Style) */}
                                    <View style={[styles.cornerL, { top: -2, left: -2, borderTopWidth: 4, borderLeftWidth: 4 }]} pointerEvents="none" />
                                    <View style={[styles.cornerL, { top: -2, right: -2, borderTopWidth: 4, borderRightWidth: 4 }]} pointerEvents="none" />
                                    <View style={[styles.cornerL, { bottom: -2, left: -2, borderBottomWidth: 4, borderLeftWidth: 4 }]} pointerEvents="none" />
                                    <View style={[styles.cornerL, { bottom: -2, right: -2, borderBottomWidth: 4, borderRightWidth: 4 }]} pointerEvents="none" />

                                    {/* TOUCH DOTS (Visual guides for grabbing) */}
                                    <View style={[styles.touchDot, { top: -6, left: -6 }]} pointerEvents="none" />
                                    <View style={[styles.touchDot, { top: -6, right: -6 }]} pointerEvents="none" />
                                    <View style={[styles.touchDot, { bottom: -6, left: -6 }]} pointerEvents="none" />
                                    <View style={[styles.touchDot, { bottom: -6, right: -6 }]} pointerEvents="none" />
                                </Animated.View>
                            </View>
                        ) : (
                            <ActivityIndicator size="large" color={COLORS.primary} />
                        )}

                        {/* CHANGE PHOTO BUTTON */}
                        <Pressable
                            style={({ pressed }) => [styles.floatingChangeBtn, pressed && { opacity: 0.7, scale: 0.95 }]}
                            onPress={onChangePhoto}
                        >
                            <Ionicons name="camera-reverse" size={20} color="#FFF" />
                            <Text style={styles.floatingBtnText}>CAMBIAR</Text>
                        </Pressable>
                    </View>

                    {/* PREMIUM SLIM FOOTER */}
                    <View style={[styles.footerContainer, { paddingBottom: Math.max(insets.bottom, 15) }]}>
                        {/* SELECTABLE PRESETS (Slim Pill Style) */}
                        <ScrollView
                            horizontal
                            showsHorizontalScrollIndicator={false}
                            style={styles.filterScroll}
                            contentContainerStyle={styles.filterScrollContent}
                        >
                            {([
                                { id: 'grit', label: 'GRIT' },
                                { id: 'vibrant', label: 'VIVO' },
                                { id: 'bw', label: 'B&N' },
                                { id: 'cinematic', label: 'CINE' }
                            ] as const).map(p => (
                                <Pressable
                                    key={p.id}
                                    style={[styles.presetChip, selectedPreset === p.id && styles.presetChipActive]}
                                    onPress={() => setSelectedPreset(selectedPreset === p.id ? 'original' : p.id)}
                                >
                                    <Text style={[styles.presetLabel, selectedPreset === p.id && { color: COLORS.primary }]}>{p.label}</Text>
                                </Pressable>
                            ))}
                        </ScrollView>

                        {/* SECONDARY CONTROLS (Aura, Neon, Ghost, Glitch) */}
                        <ScrollView
                            horizontal
                            showsHorizontalScrollIndicator={false}
                            style={styles.effectsScroll}
                            contentContainerStyle={styles.effectsScrollContent}
                        >
                            <Pressable
                                style={[styles.effectChip, effectMode === 'aura' && { borderColor: effectColor, backgroundColor: 'rgba(0,0,0,0.4)' }]}
                                onPress={() => setEffectMode(effectMode === 'aura' ? 'none' : 'aura')}
                            >
                                <Ionicons name="sparkles" size={14} color={effectMode === 'aura' ? effectColor : "#555"} />
                                <Text style={[styles.effectLabel, effectMode === 'aura' && { color: '#FFF' }]}>AURA</Text>
                            </Pressable>

                            <Pressable
                                style={[styles.effectChip, effectMode === 'neon' && { borderColor: effectColor, backgroundColor: 'rgba(0,0,0,0.4)' }]}
                                onPress={() => setEffectMode(effectMode === 'neon' ? 'none' : 'neon')}
                            >
                                <Ionicons name="flash-outline" size={14} color={effectMode === 'neon' ? effectColor : "#555"} />
                                <Text style={[styles.effectLabel, effectMode === 'neon' && { color: '#FFF' }]}>NEÓN</Text>
                            </Pressable>

                            <Pressable
                                style={[styles.effectChip, effectMode === 'ghost' && { borderColor: effectColor, backgroundColor: 'rgba(0,0,0,0.4)' }]}
                                onPress={() => setEffectMode(effectMode === 'ghost' ? 'none' : 'ghost')}
                            >
                                <Ionicons name="copy-outline" size={14} color={effectMode === 'ghost' ? effectColor : "#555"} />
                                <Text style={[styles.effectLabel, effectMode === 'ghost' && { color: '#FFF' }]}>GHOST</Text>
                            </Pressable>

                            <Pressable
                                style={[styles.effectChip, effectMode === 'glitch' && { borderColor: effectColor, backgroundColor: 'rgba(0,0,0,0.4)' }]}
                                onPress={() => setEffectMode(effectMode === 'glitch' ? 'none' : 'glitch')}
                            >
                                <Ionicons name="barcode-outline" size={14} color={effectMode === 'glitch' ? effectColor : "#555"} />
                                <Text style={[styles.effectLabel, effectMode === 'glitch' && { color: '#FFF' }]}>GLITCH</Text>
                            </Pressable>

                            <Pressable
                                onPress={() => setShowColorPicker(!showColorPicker)}
                                style={[styles.colorTrigger, { borderColor: showColorPicker ? '#FFF' : '#333' }]}
                            >
                                <View style={[styles.colorPreview, { backgroundColor: effectColor }]} />
                                <Ionicons name="chevron-up" size={14} color="#FFF" />
                            </Pressable>
                        </ScrollView>

                        {/* COLOR PALETTE (Floating) */}
                        {showColorPicker && (
                            <View style={styles.floatingPalette}>
                                <View style={styles.paletteHeader}>
                                    <Text style={styles.paletteTitle}>COLOR DEL EFECTO</Text>
                                    <Pressable onPress={() => setShowColorPicker(false)}>
                                        <Ionicons name="close-circle" size={20} color="#666" />
                                    </Pressable>
                                </View>
                                <View style={styles.colorGrid}>
                                    {['#00FFFF', '#FF0000', '#FFD700', '#FFFFFF', '#FF69B4', '#00FF00', '#A020F0', '#FF8C00'].map(c => (
                                        <Pressable key={c} onPress={() => setEffectColor(c)} style={[styles.colorCircle, { backgroundColor: c }, effectColor === c && styles.colorCircleActive]} />
                                    ))}
                                </View>
                            </View>
                        )}

                        {/* ACTION BAR */}
                        <View style={styles.actionBar}>
                            <Pressable style={styles.secondaryAction} onPress={handleCut}>
                                <Ionicons name="cut-outline" size={20} color="#FFF" />
                                <Text style={styles.actionText}>CORTAR</Text>
                            </Pressable>

                            <Pressable
                                style={[styles.secondaryAction, isRemovingBg && { opacity: 0.5 }]}
                                onPress={handleRemoveBg}
                                disabled={isRemovingBg}
                            >
                                {isRemovingBg ? <ActivityIndicator size="small" color="#FFF" /> : <Ionicons name="sparkles" size={20} color="#FFF" />}
                                <Text style={styles.actionText}>{isRemovingBg ? "IA..." : "FONDO IA"}</Text>
                            </Pressable>

                            <Pressable style={styles.primaryAction} onPress={handleConfirm}>
                                <Ionicons name="checkmark-circle" size={22} color="#000" />
                                <Text style={styles.primaryActionText}>LISTO</Text>
                            </Pressable>
                        </View>
                    </View>
                </View>
            </View>

            {/* WEBVIEW PROCESSOR */}
            <BackgroundRemoverWebView
                visible={showWebRemover}
                imageUrl={tempImageUrl}
                onClose={() => setShowWebRemover(false)}
                onImageProcessed={(newUrl) => {
                    setCurrentImageUri(newUrl);
                    setShowWebRemover(false);
                }}
            />
        </Modal >
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: 'rgba(0,0,0,0.95)', alignItems: 'center', justifyContent: 'center' },
    contentWrapper: { width: '100%', height: '100%', backgroundColor: '#000', borderRadius: 0, overflow: 'hidden', position: 'relative' },
    header: { height: 100, flexDirection: 'row', justifyContent: 'center', alignItems: 'center', paddingHorizontal: 20, borderBottomWidth: 1, borderBottomColor: '#222' },
    headerTitle: { color: 'rgba(255,255,255,0.9)', fontSize: 14, fontWeight: '900', letterSpacing: 2 },
    closeBtn: { position: 'absolute', right: 20, top: 0, height: 60, justifyContent: 'center' },

    // Pro Stage Elements
    overlay: { position: 'absolute', backgroundColor: 'rgba(0,0,0,0.7)' },
    gridLineV: { position: 'absolute', left: '33.3%', top: 0, bottom: 0, width: 1, backgroundColor: 'rgba(255,255,255,0.2)' },
    gridLineH: { position: 'absolute', top: '33.3%', left: 0, right: 0, height: 1, backgroundColor: 'rgba(255,255,255,0.2)' },
    cornerL: { position: 'absolute', width: 30, height: 30, borderColor: COLORS.primary, zIndex: 15 },
    touchDot: { position: 'absolute', width: 12, height: 12, borderRadius: 6, backgroundColor: '#FFF', borderWidth: 2, borderColor: COLORS.primary, zIndex: 20 },
    touchHandle: { position: 'absolute', width: 60, height: 60, backgroundColor: 'transparent', zIndex: 30 }, // High Z for priority

    floatingChangeBtn: {
        position: 'absolute', top: 15, left: 15,
        backgroundColor: 'rgba(0,0,0,0.6)', paddingVertical: 6, paddingHorizontal: 12, borderRadius: 20,
        borderWidth: 1, borderColor: 'rgba(255,255,255,0.3)', flexDirection: 'row', alignItems: 'center', gap: 6,
        zIndex: 50
    },
    floatingBtnText: { color: '#FFF', fontSize: 10, fontWeight: 'bold' },

    // Pro Footer Slim
    footerContainer: { position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: 'rgba(10,10,10,0.95)', borderTopWidth: 1, borderTopColor: '#222', padding: 15, paddingBottom: Platform.OS === 'ios' ? 35 : 15, gap: 10 },
    sectionLabel: { color: '#666', fontSize: 10, fontWeight: '900', letterSpacing: 1.5, marginBottom: 5 },

    filterScroll: { marginBottom: 5, maxHeight: 40 },
    filterScrollContent: { gap: 8, paddingRight: 20 },
    presetChip: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingVertical: 8, paddingHorizontal: 12, borderRadius: 20, backgroundColor: '#1A1A1A', borderWidth: 1, borderColor: '#333' },
    presetChipActive: { borderColor: COLORS.primary, backgroundColor: 'rgba(0,0,0,0.3)' },
    presetLabel: { color: '#999', fontSize: 10, fontWeight: 'bold' },

    effectsRow: { flexDirection: 'row', gap: 10, alignItems: 'center', marginTop: 5 },
    effectsScroll: { maxHeight: 40 },
    effectsScrollContent: { gap: 8, paddingRight: 20 },
    effectChip: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingVertical: 8, paddingHorizontal: 12, borderRadius: 20, borderWidth: 1, borderColor: '#333', backgroundColor: '#111' },
    effectLabel: { color: '#666', fontSize: 10, fontWeight: 'bold' },
    colorTrigger: { flexDirection: 'row', alignItems: 'center', gap: 8, padding: 4, paddingRight: 8, borderRadius: 15, borderWidth: 1, borderColor: '#333', backgroundColor: '#111' },
    colorPreview: { width: 22, height: 22, borderRadius: 11 },

    floatingPalette: {
        position: 'absolute', bottom: 100, left: 20, right: 20,
        backgroundColor: '#151515', borderRadius: 20, padding: 15,
        borderWidth: 1, borderColor: '#333', ...SHADOWS.lg, elevation: 10, zIndex: 100
    },
    paletteHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    paletteTitle: { color: '#FFF', fontSize: 10, fontWeight: 'bold', letterSpacing: 1 },
    colorGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12, justifyContent: 'center' },
    colorCircle: { width: 36, height: 36, borderRadius: 18, borderWidth: 2, borderColor: 'transparent' },
    colorCircleActive: { borderColor: '#FFF', transform: [{ scale: 1.1 }] },

    actionBar: { flexDirection: 'row', gap: 10, marginTop: 10 },
    secondaryAction: { flex: 1, height: 48, backgroundColor: '#1A1A1A', borderRadius: 15, flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 8, borderWidth: 1, borderColor: '#333' },
    actionText: { color: '#FFF', fontSize: 12, fontWeight: 'bold' },
    primaryAction: { flex: 1.2, height: 48, backgroundColor: COLORS.primary, borderRadius: 15, flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 8 },
    primaryActionText: { color: '#000', fontSize: 14, fontWeight: '900', letterSpacing: 1 }
});
