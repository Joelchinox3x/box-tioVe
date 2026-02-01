import React, { useState, useEffect, useRef } from 'react';
import { View, Text, Modal, Image, StyleSheet, Dimensions, PanResponder, ActivityIndicator, Platform, Pressable, useWindowDimensions } from 'react-native';
import * as ImageManipulator from 'expo-image-manipulator';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, SHADOWS } from '../../../constants/theme';
import { useBackgroundRemoval } from '../../../hooks/useBackgroundRemoval';

const { width: SCREEN_WIDTH, height: SCREEN_HEIGHT } = Dimensions.get('window');

interface ImageCropperProps {
    visible: boolean;
    imageUri: string | null;
    onClose: () => void;
    onCrop: (uri: string, preset: string, effect: string, effectColor: string) => void;
    onChangePhoto?: () => void;
}

import { HexColorPicker } from "react-colorful";

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
    const { removeBackground, isProcessing: isRemovingBg, isLibReady } = useBackgroundRemoval();

    // Dimensions - STATE
    const [imageSize, setImageSize] = useState<Size>({ width: 0, height: 0 });
    const [stageSize, setStageSize] = useState<Size>({ width: 0, height: 0 });
    const [displayImageSize, setDisplayImageSize] = useState<Size>({ width: 0, height: 0 });

    // REFS for PanResponder (Stale Closure Fix)
    const displayImageSizeRef = useRef<Size>({ width: 0, height: 0 });

    // Crop State (in Display Coordinates)
    const [crop, setCrop] = useState<Rect>({ x: 0, y: 0, width: 100, height: 100 });
    // Ref for crop to avoid state lag in PanResponder
    const cropRef = useRef<Rect>({ x: 0, y: 0, width: 100, height: 100 });

    // Filter & Effects State
    const [selectedPreset, setSelectedPreset] = useState<'original' | 'grit' | 'vibrant' | 'bw' | 'cinematic'>('original');
    const [effectMode, setEffectMode] = useState<'none' | 'aura' | 'neon' | 'ghost' | 'glitch'>('none');
    const [effectColor, setEffectColor] = useState<string>('#00FFFF');
    const [showColorPicker, setShowColorPicker] = useState(false);

    const PRESETS = {
        original: { contrast: 1, saturate: 1, brightness: 1 },
        grit: { contrast: 1.4, saturate: 0.6, brightness: 0.9 },
        vibrant: { contrast: 1.1, saturate: 1.5, brightness: 1.1 },
        bw: { contrast: 1.2, saturate: 0, brightness: 1 },
        cinematic: { contrast: 1.5, saturate: 0.8, brightness: 0.85 },
    };

    const STAGE_WIDTH = Platform.OS === 'web' && WINDOW_WIDTH > 600 ? 500 : Math.min(WINDOW_WIDTH, 600);
    const STAGE_HEIGHT = Math.max(300, WINDOW_HEIGHT - 320);

    // Sync Refs
    useEffect(() => { displayImageSizeRef.current = displayImageSize; }, [displayImageSize]);
    useEffect(() => { cropRef.current = crop; }, [crop]);

    useEffect(() => {
        if (Platform.OS === 'web' && typeof document !== 'undefined') {
            const style = document.createElement('style');
            style.innerHTML = `
                .react-colorful {
                    width: 100% !important;
                    height: 160px !important;
                    border-radius: 12px !important;
                    border: none !important;
                }
                .react-colorful__saturation {
                    border-bottom: 8px solid #121212 !important;
                    border-radius: 12px 12px 0 0 !important;
                }
                .react-colorful__hue {
                    height: 14px !important;
                    border-radius: 0 0 12px 12px !important;
                }
                .react-colorful__pointer {
                    width: 20px !important;
                    height: 20px !important;
                    border: 3px solid #fff !important;
                }
            `;
            document.head.appendChild(style);
            return () => { document.head.removeChild(style); };
        }
    }, []);

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
            Image.getSize(uriToLoad, (w, h) => {
                setImageSize({ width: w, height: h });

                // Calculate how the image fits into the stage
                const stageRatio = STAGE_WIDTH / STAGE_HEIGHT;
                const imgRatio = w / h;

                let dw, dh;
                if (imgRatio > stageRatio) {
                    dw = STAGE_WIDTH; dh = STAGE_WIDTH / imgRatio;
                } else {
                    dh = STAGE_HEIGHT; dw = STAGE_HEIGHT * imgRatio;
                }

                setDisplayImageSize({ width: dw, height: dh });
                setStageSize({ width: STAGE_WIDTH, height: STAGE_HEIGHT });

                // Initial Crop Box (Centered, Max Fit)
                initCropBox(dw, dh);

                setReady(true);
            }, () => onClose());
        }
    }, [visible, currentImageUri, imageUri]);

    const initCropBox = (dw: number, dh: number) => {
        const newCrop = { x: 0, y: 0, width: dw, height: dh };
        setCrop(newCrop);
        cropRef.current = newCrop;
    };


    // -------------------------------------------------------------
    // PAN RESPONDERS
    // -------------------------------------------------------------

    // Helper to get touch style for web
    const getWebTouchStyle = () => Platform.OS === 'web' ? { touchAction: 'none', userSelect: 'none' } as any : {};

    // 1. MOVE Responder
    const lastCropStart = useRef<Rect>({ x: 0, y: 0, width: 0, height: 0 });

    const moveResponder = useRef(
        PanResponder.create({
            onStartShouldSetPanResponder: () => true,
            onMoveShouldSetPanResponder: () => true,
            onStartShouldSetPanResponderCapture: () => false, // Allow children (handles) to claim touch
            onMoveShouldSetPanResponderCapture: () => false,

            onPanResponderGrant: () => {
                lastCropStart.current = { ...cropRef.current };
            },
            onPanResponderMove: (e, gesture) => {
                const { width: dw, height: dh } = displayImageSizeRef.current;
                const start = lastCropStart.current;

                let newX = start.x + gesture.dx;
                let newY = start.y + gesture.dy;

                // Constraint
                newX = Math.max(0, Math.min(dw - start.width, newX));
                newY = Math.max(0, Math.min(dh - start.height, newY));

                setCrop(prev => ({ ...prev, x: newX, y: newY }));
            },
            onPanResponderTerminationRequest: () => false,
            onShouldBlockNativeResponder: () => true,
        })
    ).current;

    // 2. RESIZE Responder Generator
    const createResizeResponder = (corner: 'TL' | 'TR' | 'BL' | 'BR') => PanResponder.create({
        onStartShouldSetPanResponder: () => true,
        onMoveShouldSetPanResponder: () => true,
        onStartShouldSetPanResponderCapture: () => true,
        onMoveShouldSetPanResponderCapture: () => true,

        onPanResponderGrant: () => {
            lastCropStart.current = { ...cropRef.current };
        },
        onPanResponderMove: (e, gesture) => {
            const prev = lastCropStart.current;
            const { width: dw, height: dh } = displayImageSizeRef.current;

            let dx = gesture.dx;
            let dy = gesture.dy;

            let nx = prev.x;
            let ny = prev.y;
            let nw = prev.width;
            let nh = prev.height;
            const minDim = 50;

            // CALCULATE (Freeform)
            if (corner === 'BR') {
                nw = prev.width + dx;
                nh = prev.height + dy;
            } else if (corner === 'BL') {
                nw = prev.width - dx;
                nx = prev.x + dx;
                nh = prev.height + dy;
            } else if (corner === 'TR') {
                nw = prev.width + dx;
                nh = prev.height - dy;
                ny = prev.y + dy;
            } else if (corner === 'TL') {
                nw = prev.width - dx;
                nx = prev.x + dx;
                nh = prev.height - dy;
                ny = prev.y + dy;
            }

            // SIMPLIFIED CLAMPING
            if (nw < minDim) nw = minDim;
            if (nh < minDim) nh = minDim;
            if (nx < 0) nx = 0;
            if (ny < 0) ny = 0;
            if (nx + nw > dw) nw = dw - nx;
            if (ny + nh > dh) nh = dh - ny;

            setCrop({ x: nx, y: ny, width: nw, height: nh });
        },
        onPanResponderTerminationRequest: () => false,
        onShouldBlockNativeResponder: () => true,
    });

    const resizeTL = useRef(createResizeResponder('TL')).current;
    const resizeTR = useRef(createResizeResponder('TR')).current;
    const resizeBL = useRef(createResizeResponder('BL')).current;
    const resizeBR = useRef(createResizeResponder('BR')).current;

    const performCrop = async (shouldClose: boolean = false) => {
        if (!currentImageUri || !ready) return;
        const scaleX = imageSize.width / displayImageSize.width;
        const scaleY = imageSize.height / displayImageSize.height;

        const originX = crop.x * scaleX;
        const originY = crop.y * scaleY;
        const targetWidth = crop.width * scaleX;
        const targetHeight = crop.height * scaleY;

        try {
            const result = await ImageManipulator.manipulateAsync(
                currentImageUri,
                [{
                    crop: {
                        originX,
                        originY,
                        width: targetWidth,
                        height: targetHeight
                    }
                }],
                { compress: 1, format: ImageManipulator.SaveFormat.PNG }
            );

            if (shouldClose) {
                onCrop(result.uri, selectedPreset, effectMode, effectColor);
            } else {
                setCurrentImageUri(result.uri);
            }
        } catch (error) {
            console.log('Crop error:', error);
        }
    };

    const handleCut = () => performCrop(false);
    const handleConfirm = async () => {
        if (!currentImageUri) return;
        await performCrop(true);
    };

    const handleRemoveBg = async () => {
        if (!currentImageUri) return;
        try {
            const result = await removeBackground(currentImageUri);
            setCurrentImageUri(result);
        } catch (error) {
            console.log('Background removal error:', error);
        }
    };

    if (!visible) return null;

    return (
        <Modal visible={visible} transparent animationType="fade" onRequestClose={onClose}>
            <View style={styles.container}>
                <View style={[
                    styles.contentWrapper,
                    Platform.OS === 'web' && {
                        width: Math.min(WINDOW_WIDTH, 500),
                        height: Math.min(WINDOW_HEIGHT, 850),
                        borderRadius: 20,
                        borderWidth: 1,
                    }
                ]}>
                    <View style={styles.header}>
                        <Text style={styles.headerTitle}>EDITAR IMAGEN</Text>
                        <Pressable onPress={onClose} style={styles.closeBtn}>
                            <Ionicons name="close" size={24} color="#FFF" />
                        </Pressable>
                    </View>

                    <View style={[styles.stage, { width: STAGE_WIDTH, height: STAGE_HEIGHT }]}>
                        {ready ? (
                            <View style={{ width: displayImageSize.width, height: displayImageSize.height, position: 'relative' }}>
                                <Image
                                    source={{ uri: currentImageUri! }}
                                    style={{
                                        width: displayImageSize.width,
                                        height: displayImageSize.height,
                                        filter: `
                                            contrast(${PRESETS[selectedPreset].contrast}) 
                                            saturate(${PRESETS[selectedPreset].saturate}) 
                                            brightness(${PRESETS[selectedPreset].brightness})
                                            ${effectMode === 'aura' ? `drop-shadow(0 0 12px ${effectColor})` : ''}
                                            ${effectMode === 'neon' ? `drop-shadow(0 0 4px #FFF) drop-shadow(0 0 12px ${effectColor})` : ''}
                                            ${effectMode === 'ghost' ? `drop-shadow(15px 15px 0px ${effectColor}88)` : ''}
                                            ${effectMode === 'glitch' ? `drop-shadow(-3px 0 0 #F00) drop-shadow(3px 0 0 ${effectColor})` : ''}
                                        `
                                    } as any}
                                    resizeMode="contain"
                                />
                                <View style={{ position: 'absolute', top: 0, left: 0, right: 0, height: crop.y, backgroundColor: 'rgba(0,0,0,0.6)' }} />
                                <View style={{ position: 'absolute', top: crop.y + crop.height, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0,0,0,0.6)' }} />
                                <View style={{ position: 'absolute', top: crop.y, left: 0, width: crop.x, height: crop.height, backgroundColor: 'rgba(0,0,0,0.6)' }} />
                                <View style={{ position: 'absolute', top: crop.y, left: crop.x + crop.width, right: 0, height: crop.height, backgroundColor: 'rgba(0,0,0,0.6)' }} />

                                <View
                                    style={{
                                        position: 'absolute',
                                        left: crop.x,
                                        top: crop.y,
                                        width: crop.width,
                                        height: crop.height,
                                        borderColor: '#FFF',
                                        borderWidth: 2,
                                        borderStyle: 'solid',
                                        zIndex: 10,
                                        ...getWebTouchStyle()
                                    }}
                                    {...moveResponder.panHandlers}
                                >
                                    <View style={{ position: 'absolute', left: '33%', top: 0, bottom: 0, width: 1, backgroundColor: 'rgba(255,255,255,0.3)' }} />
                                    <View style={{ position: 'absolute', left: '66%', top: 0, bottom: 0, width: 1, backgroundColor: 'rgba(255,255,255,0.3)' }} />
                                    <View style={{ position: 'absolute', top: '33%', left: 0, right: 0, height: 1, backgroundColor: 'rgba(255,255,255,0.3)' }} />
                                    <View style={{ position: 'absolute', top: '66%', left: 0, right: 0, height: 1, backgroundColor: 'rgba(255,255,255,0.3)' }} />

                                    <View {...resizeTL.panHandlers} style={[styles.cornerHandle, { top: -10, left: -10 }, getWebTouchStyle()]} />
                                    <View {...resizeTR.panHandlers} style={[styles.cornerHandle, { top: -10, right: -10 }, getWebTouchStyle()]} />
                                    <View {...resizeBL.panHandlers} style={[styles.cornerHandle, { bottom: -10, left: -10 }, getWebTouchStyle()]} />
                                    <View {...resizeBR.panHandlers} style={[styles.cornerHandle, { bottom: -10, right: -10 }, getWebTouchStyle()]} />

                                    <View style={styles.dragLabel} pointerEvents="none">
                                        <Ionicons name="move" size={12} color="#000" />
                                        <Text style={{ fontSize: 10, fontWeight: 'bold' }}>MOVER</Text>
                                    </View>
                                </View>
                            </View>
                        ) : (
                            <ActivityIndicator size="large" color={COLORS.primary} />
                        )}

                        <Pressable
                            style={({ pressed }) => [styles.floatingChangeBtn, pressed && { opacity: 0.7 }]}
                            onPress={onChangePhoto}
                        >
                            <Ionicons name="camera-reverse" size={22} color="#FFF" />
                        </Pressable>
                    </View>

                    <View style={styles.footer}>
                        <View style={styles.filterRow}>
                            {([
                                { id: 'grit', label: 'GRIT' },
                                { id: 'vibrant', label: 'VIVO' },
                                { id: 'bw', label: 'B&N' },
                                { id: 'cinematic', label: 'CINE' }
                            ] as const).map(p => (
                                <Pressable
                                    key={p.id}
                                    style={[styles.filterBtn, selectedPreset === p.id && styles.filterBtnActive]}
                                    onPress={() => setSelectedPreset(selectedPreset === p.id ? 'original' : p.id)}
                                >
                                    <Text style={[styles.filterText, selectedPreset === p.id && styles.filterTextActive]}>{p.label}</Text>
                                </Pressable>
                            ))}
                        </View>

                        <View style={styles.glowRow}>
                            <Pressable
                                style={[
                                    styles.glowBtn,
                                    { borderColor: effectMode === 'aura' ? effectColor : COLORS.primary },
                                    effectMode === 'aura' && { backgroundColor: effectColor }
                                ]}
                                onPress={() => setEffectMode(effectMode === 'aura' ? 'none' : 'aura')}
                            >
                                <Ionicons name="sparkles" size={14} color={effectMode === 'aura' ? "#000" : "#888"} />
                                <Text style={[styles.filterText, effectMode === 'aura' && { color: '#000' }]}>AURA</Text>
                            </Pressable>

                            <Pressable
                                style={[
                                    styles.glowBtn,
                                    { borderColor: effectMode === 'neon' ? effectColor : '#0FF' },
                                    effectMode === 'neon' && { backgroundColor: effectColor }
                                ]}
                                onPress={() => setEffectMode(effectMode === 'neon' ? 'none' : 'neon')}
                            >
                                <Ionicons name="flash-outline" size={14} color={effectMode === 'neon' ? "#000" : "#0FF"} />
                                <Text style={[styles.filterText, effectMode === 'neon' && { color: '#000' }]}>NEÓN</Text>
                            </Pressable>

                            <Pressable
                                style={[
                                    styles.glowBtn,
                                    { borderColor: effectMode === 'ghost' ? effectColor : '#888' },
                                    effectMode === 'ghost' && { backgroundColor: effectColor }
                                ]}
                                onPress={() => setEffectMode(effectMode === 'ghost' ? 'none' : 'ghost')}
                            >
                                <Ionicons name="copy-outline" size={14} color={effectMode === 'ghost' ? "#000" : "#888"} />
                                <Text style={[styles.filterText, effectMode === 'ghost' && { color: '#000' }]}>GHOST</Text>
                            </Pressable>

                            <Pressable
                                style={[
                                    styles.glowBtn,
                                    { borderColor: effectMode === 'glitch' ? effectColor : '#F00' },
                                    effectMode === 'glitch' && { backgroundColor: effectColor }
                                ]}
                                onPress={() => setEffectMode(effectMode === 'glitch' ? 'none' : 'glitch')}
                            >
                                <Ionicons name="barcode-outline" size={14} color={effectMode === 'glitch' ? "#000" : "#F00"} />
                                <Text style={[styles.filterText, effectMode === 'glitch' && { color: '#000' }]}>GLITCH</Text>
                            </Pressable>

                            <View style={{ flex: 1 }} />

                            <Pressable
                                onPress={() => setShowColorPicker(!showColorPicker)}
                                style={[
                                    styles.paletteTrigger,
                                    { backgroundColor: showColorPicker ? '#FFF' : 'rgba(255,255,255,0.1)' }
                                ]}
                            >
                                <Ionicons
                                    name="color-palette-outline"
                                    size={18}
                                    color={showColorPicker ? "#000" : effectColor}
                                />
                            </Pressable>
                        </View>

                        {showColorPicker && (
                            <>
                                <Pressable
                                    style={styles.clickOutside}
                                    onPress={() => setShowColorPicker(false)}
                                />
                                <View style={styles.floatingPicker}>
                                    <View style={styles.pickerHeader}>
                                        <Text style={styles.pickerTitle}>PALETA PREMIUM</Text>
                                        <Pressable onPress={() => setShowColorPicker(false)}>
                                            <Ionicons name="close-circle" size={20} color="#444" />
                                        </Pressable>
                                    </View>
                                    <View style={styles.colorPalette}>
                                        {['#00FFFF', '#FF0000', '#FFD700', '#FFFFFF', '#FF69B4', '#00FF00', '#A020F0', '#FF8C00'].map(c => (
                                            <Pressable
                                                key={c}
                                                onPress={() => setEffectColor(c)}
                                                style={[
                                                    styles.colorCircle,
                                                    { backgroundColor: c },
                                                    effectColor === c && styles.colorCircleActive
                                                ]}
                                            />
                                        ))}
                                    </View>
                                    <View style={styles.proPickerContainer}>
                                        <HexColorPicker color={effectColor} onChange={setEffectColor} />
                                    </View>
                                </View>
                            </>
                        )}

                        <View style={{ flexDirection: 'row', gap: 6, width: '100%', marginTop: 5 }}>
                            <Pressable style={[styles.actionBtn, { flex: 1.2 }]} onPress={handleCut}>
                                <Ionicons name="cut-outline" size={18} color="#FFF" />
                                <Text style={styles.actionBtnText}>CORTAR</Text>
                            </Pressable>

                            <Pressable
                                style={({ pressed }) => [
                                    styles.actionBtn,
                                    { flex: 1.8 },
                                    (isRemovingBg || !isLibReady) && { opacity: 0.5 },
                                    pressed && { opacity: 0.8 }
                                ]}
                                onPress={handleRemoveBg}
                                disabled={isRemovingBg || !isLibReady}
                            >
                                {isRemovingBg ? <ActivityIndicator size="small" color="#FFF" /> : <Ionicons name="layers-outline" size={18} color="#FFF" />}
                                <Text style={styles.actionBtnText}>
                                    {isRemovingBg ? "PROCESANDO..." : isLibReady ? "QUITAR FONDO" : "FONDO IA"}
                                </Text>
                            </Pressable>

                            <Pressable style={[styles.confirmBtn, { flex: 1.2, height: 44, borderRadius: 15 }]} onPress={handleConfirm}>
                                <Ionicons name="checkmark-circle" size={20} color="#000" />
                                <Text style={[styles.confirmBtnText, { fontSize: 14 }]}>LISTO</Text>
                            </Pressable>
                        </View>
                    </View>
                </View>
            </View>
        </Modal>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: 'rgba(0,0,0,0.85)', alignItems: 'center', justifyContent: 'center' },
    contentWrapper: {
        width: '100%',
        height: '100%',
        backgroundColor: '#000',
        borderRadius: 0,
        overflow: 'hidden',
        position: 'relative',
        borderWidth: 0,
        borderColor: '#333',
        ...SHADOWS.lg,
    },
    header: { height: 50, flexDirection: 'row', justifyContent: 'center', alignItems: 'center', paddingHorizontal: 20, zIndex: 50, position: 'relative' },
    headerTitle: { color: '#FFF', fontSize: 16, fontWeight: 'bold', textAlign: 'center' },
    closeBtn: { position: 'absolute', right: 20, top: 0, height: 50, justifyContent: 'center', padding: 5 },
    stage: { backgroundColor: '#111', justifyContent: 'center', alignItems: 'center', overflow: 'hidden' },

    cornerHandle: {
        position: 'absolute',
        width: 30, height: 30,
        borderRadius: 15,
        backgroundColor: COLORS.primary,
        borderWidth: 2,
        borderColor: '#FFF',
        zIndex: 20
    },
    dragLabel: {
        position: 'absolute', alignSelf: 'center', top: '45%',
        backgroundColor: 'rgba(255,255,255,0.7)', padding: 4, borderRadius: 4, flexDirection: 'row', gap: 2, alignItems: 'center',
    },
    floatingChangeBtn: {
        position: 'absolute', top: 10, right: 10,
        backgroundColor: 'rgba(0,0,0,0.6)', padding: 8, borderRadius: 20, borderWidth: 1, borderColor: '#FFF'
    },
    footer: {
        position: 'absolute', bottom: 20, left: 20, right: 20, alignItems: 'stretch', gap: 12
    },
    confirmBtn: {
        width: '100%',
        backgroundColor: COLORS.primary,
        paddingVertical: 14,
        borderRadius: 25,
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        gap: 8,
        elevation: 5
    },
    confirmBtnText: { color: '#000', fontSize: 18, fontWeight: '900', letterSpacing: 1 },
    actionBtn: {
        flex: 1,
        backgroundColor: '#222',
        paddingVertical: 12,
        borderRadius: 15,
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        gap: 8,
        borderWidth: 1,
        borderColor: '#444',
        zIndex: 10
    },
    actionBtnText: { color: '#FFF', fontSize: 12, fontWeight: 'bold' },

    paletteTrigger: { paddingVertical: 5, paddingHorizontal: 10, borderRadius: 10, borderWidth: 1, borderColor: '#333', justifyContent: 'center', alignItems: 'center' },

    floatingPicker: {
        position: 'absolute', bottom: 70,
        backgroundColor: '#121212', borderRadius: 20, padding: 12,
        width: '100%', borderColor: '#333',
        ...SHADOWS.lg, elevation: 20, alignSelf: 'center',
        borderWidth: 1.5,
        shadowOpacity: 0.5,
        shadowRadius: 15,
        zIndex: 110,
    },
    colorPalette: { flexDirection: 'row', flexWrap: 'nowrap', gap: 8, justifyContent: 'center', marginBottom: 15 },
    colorCircle: { width: 34, height: 34, borderRadius: 17, borderWidth: 2, borderColor: 'rgba(255,255,255,0.1)' },
    colorCircleActive: { borderColor: '#FFF', transform: [{ scale: 1.15 }] },

    proPickerContainer: {
        width: '100%',
        alignItems: 'center',
        paddingTop: 10,
        paddingBottom: 5,
    },
    clickOutside: {
        position: 'absolute',
        top: -SCREEN_HEIGHT,
        left: -SCREEN_WIDTH,
        width: SCREEN_WIDTH * 2,
        height: SCREEN_HEIGHT * 2,
        zIndex: 100,
    },
    pickerHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12, paddingHorizontal: 4 },
    pickerTitle: { color: '#FFF', fontSize: 10, fontWeight: 'bold', letterSpacing: 1 },
    filterRow: {
        flexDirection: 'row',
        gap: 5,
        flexWrap: 'wrap',
        justifyContent: 'flex-start',
        marginBottom: 8,
        paddingHorizontal: 5,
        width: '100%'
    },
    glowRow: {
        flexDirection: 'row',
        gap: 2,
        flexWrap: 'wrap',
        justifyContent: 'flex-start',
        alignItems: 'center',
        marginBottom: 12,
        paddingHorizontal: 5,
        width: '100%',
        zIndex: 5
    },

    filterBtn: {
        paddingVertical: 10,
        paddingHorizontal: 15,
        borderRadius: 12,
        backgroundColor: '#1A1A1A',
        borderWidth: 1,
        borderColor: '#333',
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        minWidth: 60,
        justifyContent: 'center'
    },
    filterBtnActive: {
        backgroundColor: '#333',
        borderColor: '#555'
    },
    filterText: {
        color: '#888',
        fontSize: 10,
        fontWeight: '700'
    },
    filterTextActive: {
        color: '#FFF'
    },

    glowBtn: {
        paddingVertical: 5,
        paddingHorizontal: 8,
        borderRadius: 10,
        backgroundColor: '#111',
        borderWidth: 1,
        borderColor: COLORS.primary,
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4
    },
    glowBtnActive: {
        backgroundColor: COLORS.primary,
        borderColor: COLORS.primary
    },
});
