import React, { useState, useEffect, useRef } from 'react';
import { View, Text, Modal, Image, StyleSheet, Dimensions, PanResponder, ActivityIndicator, Platform, Animated, useWindowDimensions, Pressable, TouchableOpacity } from 'react-native';
import * as ImageManipulator from 'expo-image-manipulator';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../../../constants/theme';
import { useBackgroundRemoval } from '../../../hooks/useBackgroundRemoval';

interface WebImageCropperProps {
    visible: boolean;
    imageUri: string | null;
    onClose: () => void;
    onCrop: (croppedUri: string) => void;
    onChangePhoto?: () => void;
    aspectRatio?: number;
}

const { width: WINDOW_WIDTH, height: WINDOW_HEIGHT } = Dimensions.get('window');

const AutoRepeatButton: React.FC<{
    onPress: () => void;
    style?: any;
    children: React.ReactNode;
    disabled?: boolean;
}> = ({ onPress, style, children, disabled }) => {
    const timerRef = useRef<any>(null);
    const delayRef = useRef<any>(null);
    const isPressing = useRef(false);
    const pressHandlerRef = useRef(onPress);

    // Actualizar el ref cada vez que cambie onPress para evitar stale closures
    pressHandlerRef.current = onPress;

    const stopRepeat = () => {
        isPressing.current = false;
        if (delayRef.current) clearTimeout(delayRef.current);
        if (timerRef.current) clearInterval(timerRef.current);
        delayRef.current = null;
        timerRef.current = null;
    };

    const startRepeat = (e?: any) => {
        // En web móvil, preventDefault suele ser necesario para evitar comportamientos del navegador
        if (e && e.cancelable) e.preventDefault();

        stopRepeat();
        isPressing.current = true;
        pressHandlerRef.current();

        delayRef.current = setTimeout(() => {
            if (!isPressing.current) return;
            timerRef.current = setInterval(() => {
                if (isPressing.current) {
                    pressHandlerRef.current();
                } else {
                    stopRepeat();
                }
            }, 80);
        }, 300);
    };

    // Limpieza al desmontar
    React.useEffect(() => {
        return () => stopRepeat();
    }, []);

    const webProps = React.useMemo(() => {
        if (Platform.OS === 'web') {
            return {
                onPointerDown: startRepeat,
                onPointerUp: stopRepeat,
                onPointerLeave: stopRepeat,
                onContextMenu: (e: any) => e.preventDefault(),
                style: {
                    ...style,
                    touchAction: 'none',
                    userSelect: 'none',
                    WebkitUserSelect: 'none',
                    WebkitTouchCallout: 'none'
                }
            };
        }
        return { style };
    }, [style]);

    return (
        <TouchableOpacity
            onPressIn={Platform.OS === 'web' ? undefined : startRepeat}
            onPressOut={Platform.OS === 'web' ? undefined : stopRepeat}
            disabled={disabled}
            activeOpacity={0.6}
            {...(webProps as any)}
        >
            {children}
        </TouchableOpacity>
    );
};

export const WebImageCropper: React.FC<WebImageCropperProps> = ({
    visible,
    imageUri,
    onClose,
    onCrop,
    onChangePhoto,
    aspectRatio: initialAspectRatio = 1
}) => {
    const { width: WINDOW_WIDTH, height: WINDOW_HEIGHT } = useWindowDimensions();
    const [currentImageUri, setCurrentImageUri] = useState<string | null>(imageUri);
    const [loading, setLoading] = useState(false);
    const [ready, setReady] = useState(false);
    const [imageSize, setImageSize] = useState({ width: 0, height: 0 });
    const [displaySize, setDisplaySize] = useState({ width: 0, height: 0 });
    const [currentRatio, setCurrentRatio] = useState(initialAspectRatio);
    const [boxScale, setBoxScale] = useState(0.6);
    const { removeBackground, isProcessing: isRemovingBg, isLibReady } = useBackgroundRemoval();

    const pan = useRef(new Animated.ValueXY({ x: 0, y: 0 })).current;
    const lastOffset = useRef({ x: 0, y: 0 });

    const STAGE_WIDTH = Math.min(WINDOW_WIDTH, 600);
    const STAGE_HEIGHT = Math.max(250, WINDOW_HEIGHT - 320); // Asegura un mínimo de altura

    // Reset internal state when modal opens with a new image
    useEffect(() => {
        if (visible && imageUri) {
            setCurrentImageUri(imageUri);
        }
    }, [visible, imageUri]);

    // React to image changes (initial or intermediate crops)
    useEffect(() => {
        const uriToLoad = currentImageUri || imageUri;
        if (visible && uriToLoad) {
            setReady(false);
            Image.getSize(uriToLoad, (w, h) => {
                setImageSize({ width: w, height: h });
                const stageRatio = STAGE_WIDTH / STAGE_HEIGHT;
                const imgRatio = w / h;
                let dw, dh;
                if (imgRatio > stageRatio) {
                    dw = STAGE_WIDTH; dh = STAGE_WIDTH / imgRatio;
                } else {
                    dh = STAGE_HEIGHT; dw = STAGE_HEIGHT * imgRatio;
                }
                setDisplaySize({ width: dw, height: dh });

                // DEFAULT TO TOTAL: Match image aspect ratio
                setCurrentRatio(imgRatio);
                setBoxScale(1.0);

                pan.setValue({ x: 0, y: 0 });
                pan.setOffset({ x: 0, y: 0 });
                lastOffset.current = { x: 0, y: 0 };
                setReady(true);
            }, () => onClose());
        }
    }, [visible, currentImageUri, imageUri]);

    // Update currentRatio when it changes from props
    useEffect(() => {
        setCurrentRatio(initialAspectRatio);
    }, [initialAspectRatio]);

    const getBoxDims = () => {
        // En lugar de basarnos solo en minDim, calculamos el máximo posible para el ratio dado
        const stageW = displaySize.width;
        const stageH = displaySize.height;

        // Máximo ancho posible si usamos todo el alto
        let w = stageH * currentRatio;
        let h = stageH;

        // Si el ancho se pasa de los límites horizontales, limitamos por ancho
        if (w > stageW) {
            w = stageW;
            h = stageW / currentRatio;
        }

        // Aplicamos la escala del usuario (0.1 a 1.0)
        return {
            w: w * boxScale,
            h: h * boxScale
        };
    };

    const boxDims = getBoxDims();

    const moveBox = (dx: number, dy: number) => {
        const { w, h } = getBoxDims();
        const limitX = (displaySize.width / 2) - (w / 2);
        const limitY = (displaySize.height / 2) - (h / 2);

        const newX = Math.max(-limitX, Math.min(limitX, lastOffset.current.x + dx));
        const newY = Math.max(-limitY, Math.min(limitY, lastOffset.current.y + dy));

        lastOffset.current = { x: newX, y: newY };
        pan.setOffset({ x: 0, y: 0 });
        pan.setValue({ x: newX, y: newY });
    };

    const panResponder = useRef(
        PanResponder.create({
            onStartShouldSetPanResponder: () => true,
            onMoveShouldSetPanResponder: () => true,
            // Quitamos capture para no ser tan agresivos en web móvil
            onStartShouldSetPanResponderCapture: () => false,
            onMoveShouldSetPanResponderCapture: () => false,
            onPanResponderGrant: () => {
                pan.setOffset({ x: lastOffset.current.x, y: lastOffset.current.y });
                pan.setValue({ x: 0, y: 0 });
            },
            onPanResponderMove: (_, gestureState) => {
                const { w, h } = getBoxDims();
                const limitX = (displaySize.width / 2) - (w / 2);
                const limitY = (displaySize.height / 2) - (h / 2);

                const targetX = lastOffset.current.x + gestureState.dx;
                const targetY = lastOffset.current.y + gestureState.dy;

                const clampedX = Math.max(-limitX, Math.min(limitX, targetX));
                const clampedY = Math.max(-limitY, Math.min(limitY, targetY));

                pan.setValue({ x: clampedX - lastOffset.current.x, y: clampedY - lastOffset.current.y });
            },
            onPanResponderRelease: () => {
                pan.flattenOffset();
                lastOffset.current = { x: (pan.x as any)._value, y: (pan.y as any)._value };
            },
            onPanResponderTerminate: () => {
                pan.flattenOffset();
                lastOffset.current = { x: (pan.x as any)._value, y: (pan.y as any)._value };
            }
        })
    ).current;

    const performCrop = async () => {
        if (!currentImageUri || !ready) return null;
        const ratio = imageSize.width / displaySize.width;
        const { w, h } = getBoxDims();
        const offX = (pan.x as any)._value;
        const offY = (pan.y as any)._value;
        const visualLeft = (displaySize.width / 2) + offX - (w / 2);
        const visualTop = (displaySize.height / 2) + offY - (h / 2);

        return await ImageManipulator.manipulateAsync(
            currentImageUri,
            [{
                crop: {
                    originX: Math.max(0, visualLeft * ratio),
                    originY: Math.max(0, visualTop * ratio),
                    width: Math.min(imageSize.width, w * ratio),
                    height: Math.min(imageSize.height, h * ratio)
                }
            }],
            { compress: 0.8, format: ImageManipulator.SaveFormat.JPEG }
        );
    };

    const handleCut = async () => {
        setLoading(true);
        try {
            const result = await performCrop();
            if (result) {
                setCurrentImageUri(result.uri);
            }
        } catch (error) {
            console.error("Cut error:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleRemoveBg = async () => {
        if (!currentImageUri) return;
        try {
            const result = await removeBackground(currentImageUri);
            if (result) {
                setCurrentImageUri(result);
            }
        } catch (error) {
            console.error("BG Removal error:", error);
        }
    };

    const handleConfirm = async () => {
        if (!currentImageUri) return;
        onCrop(currentImageUri);
    };

    if (!visible) return null;

    return (
        <Modal visible={visible} transparent animationType="fade">
            <View style={styles.container}>
                <View style={styles.header}>
                    <Text style={styles.title}>ENCUADRAR IMAGEN</Text>
                    <Pressable
                        onPress={onClose}
                        style={({ pressed }) => [styles.closeBtn, pressed && { opacity: 0.7 }]}
                        hitSlop={15}
                    >
                        <Ionicons name="close" size={24} color="#FFF" />
                    </Pressable>
                </View>

                <View style={[styles.stage, { width: STAGE_WIDTH, height: STAGE_HEIGHT }]}>
                    {ready ? (
                        <View style={{ width: displaySize.width, height: displaySize.height, overflow: 'hidden' }}>
                            <Image source={{ uri: currentImageUri! }} style={StyleSheet.absoluteFill} resizeMode="contain" />

                            <Animated.View style={[StyleSheet.absoluteFill, { zIndex: 5 }]} pointerEvents="none">
                                <Animated.View style={{ height: Animated.add(displaySize.height / 2 - boxDims.h / 2, pan.y), width: '100%', backgroundColor: 'rgba(0,0,0,0.7)' }} />
                                <View style={{ flexDirection: 'row', height: boxDims.h }}>
                                    <Animated.View style={{ width: Animated.add(displaySize.width / 2 - boxDims.w / 2, pan.x), backgroundColor: 'rgba(0,0,0,0.7)' }} />
                                    <View style={{ width: boxDims.w, height: boxDims.h }} />
                                    <Animated.View style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.7)' }} />
                                </View>
                                <Animated.View style={{ flex: 1, width: '100%', backgroundColor: 'rgba(0,0,0,0.7)' }} />
                            </Animated.View>

                            <Animated.View style={[styles.selector, {
                                width: boxDims.w, height: boxDims.h,
                                marginTop: -boxDims.h / 2, marginLeft: -boxDims.w / 2,
                                transform: pan.getTranslateTransform(),
                                zIndex: 10,
                                cursor: 'move' as any // web hint
                            }]} {...panResponder.panHandlers}>
                                <View style={styles.gridLinesH} /><View style={styles.gridLinesV} />
                                <View style={styles.cornerTL} /><View style={styles.cornerTR} />
                                <View style={styles.cornerBL} /><View style={styles.cornerBR} />
                                <View style={styles.dragHint}><Ionicons name="move" size={12} color="#000" /><Text style={styles.dragText}>MOVER</Text></View>
                            </Animated.View>
                        </View>
                    ) : (
                        <ActivityIndicator size="large" color={COLORS.primary} />
                    )}

                    {/* OVERLAY: Change Photo Button (Top Right) */}
                    <Pressable
                        style={({ pressed }) => [styles.floatingChangeBtn, pressed && { opacity: 0.7 }]}
                        onPress={onChangePhoto}
                        hitSlop={10}
                    >
                        <Ionicons name="camera-reverse" size={22} color="#FFF" />
                    </Pressable>
                </View>

                <View style={styles.footer}>
                    {/* Ratio buttons at top of footer */}
                    <View style={styles.ratioRow}>
                        {[
                            { label: 'TOTAL', val: -1 },
                            { label: '1:1', val: 1 },
                            { label: 'FICHA (2.2:1)', val: 2.2 },
                            { label: 'PERFIL', val: 0.82 },
                            { label: 'CUERPO', val: 0.56 }
                        ].map(r => (
                            <Pressable
                                key={r.label}
                                style={({ pressed }) => [
                                    styles.ratioBtn,
                                    (r.val === -1 ? Math.abs(currentRatio - (imageSize.width / imageSize.height)) < 0.01 : currentRatio === r.val) && styles.ratioBtnActive,
                                    pressed && { opacity: 0.8 }
                                ]}
                                onPress={() => {
                                    if (r.val === -1) {
                                        setCurrentRatio(imageSize.width / imageSize.height);
                                        setBoxScale(1);
                                    } else {
                                        setCurrentRatio(r.val);
                                    }
                                    moveBox(0, 0);
                                }}
                                hitSlop={5}
                            >
                                <Text style={[styles.ratioText, (r.val === -1 ? Math.abs(currentRatio - (imageSize.width / imageSize.height)) < 0.01 : currentRatio === r.val) && styles.ratioTextActive]}>{r.label}</Text>
                            </Pressable>
                        ))}
                    </View>

                    {/* Main control row grouping Scale, D-Pad, and Confirm */}
                    <View style={styles.mainControlsRow}>
                        {/* LEFT: Tools (Scale Only) */}
                        <View style={styles.sideColumn}>
                            <AutoRepeatButton
                                style={styles.miniCtrlBtn}
                                onPress={() => setBoxScale(s => Math.min(1.0, s + 0.05))}
                            >
                                <Ionicons name="add" size={24} color="#FFF" />
                            </AutoRepeatButton>
                            <Text style={styles.miniLabel}>ESCALA</Text>
                            <AutoRepeatButton
                                style={styles.miniCtrlBtn}
                                onPress={() => setBoxScale(s => Math.max(0.1, s - 0.05))}
                            >
                                <Ionicons name="remove" size={24} color="#FFF" />
                            </AutoRepeatButton>
                        </View>

                        {/* Directional D-Pad (Center) */}
                        <View style={styles.dpadContainer}>
                            <View style={styles.dpadRow}>
                                <AutoRepeatButton style={styles.dpadBtn} onPress={() => moveBox(0, -10)}>
                                    <Ionicons name="chevron-up" size={24} color="#FFF" />
                                </AutoRepeatButton>
                            </View>
                            <View style={styles.dpadRow}>
                                <AutoRepeatButton style={styles.dpadBtn} onPress={() => moveBox(-10, 0)}>
                                    <Ionicons name="chevron-back" size={24} color="#FFF" />
                                </AutoRepeatButton>

                                {/* CENTER: Cut Button */}
                                <Pressable
                                    style={({ pressed }) => [
                                        styles.dpadCenter,
                                        { backgroundColor: COLORS.primary },
                                        (loading || !ready) && { opacity: 0.5 },
                                        pressed && { opacity: 0.8, scale: 0.95 } as any
                                    ]}
                                    onPress={handleCut}
                                    disabled={loading || !ready}
                                    hitSlop={5}
                                >
                                    {loading ? <ActivityIndicator size="small" color="#000" /> : <Ionicons name="cut" size={20} color="#000" />}
                                </Pressable>

                                <AutoRepeatButton style={styles.dpadBtn} onPress={() => moveBox(10, 0)}>
                                    <Ionicons name="chevron-forward" size={24} color="#FFF" />
                                </AutoRepeatButton>
                            </View>
                            <View style={styles.dpadRow}>
                                <AutoRepeatButton style={styles.dpadBtn} onPress={() => moveBox(0, 10)}>
                                    <Ionicons name="chevron-down" size={24} color="#FFF" />
                                </AutoRepeatButton>
                            </View>
                        </View>

                        {/* Buttons Column */}
                        <View style={[styles.sideColumn, { width: 100, gap: 15 }]}>
                            {/* NEW: Remove Background Button */}
                            <Pressable
                                style={({ pressed }) => [
                                    styles.cutBtn,
                                    { borderColor: isRemovingBg ? COLORS.primary : COLORS.primary },
                                    (loading || !ready || isRemovingBg || !isLibReady) && { opacity: 0.5 },
                                    pressed && { opacity: 0.8 }
                                ]}
                                onPress={handleRemoveBg}
                                disabled={loading || !ready || isRemovingBg || !isLibReady}
                                hitSlop={5}
                            >
                                {isRemovingBg ? (
                                    <ActivityIndicator color={COLORS.primary} size="small" />
                                ) : (
                                    <>
                                        <Ionicons name="layers-outline" size={24} color={(!isLibReady) ? 'rgba(255,255,255,0.3)' : COLORS.primary} />
                                        <Text style={[styles.cutText, !isLibReady && { color: 'rgba(255,255,255,0.3)' }]}>
                                            {isLibReady ? "QUITAR\nFONDO" : "CARGANDO..."}
                                        </Text>
                                    </>
                                )}
                            </Pressable>

                            <Pressable
                                style={({ pressed }) => [
                                    styles.confirmBtnCompact,
                                    (loading || !ready) && { opacity: 0.5 },
                                    pressed && { opacity: 0.8, scale: 1.05 } as any
                                ]}
                                onPress={handleConfirm}
                                disabled={loading || !ready}
                                hitSlop={15}
                            >
                                <Ionicons name="checkmark" size={28} color="#000" />
                                <Text style={styles.confirmTextCompact}>LISTO</Text>
                            </Pressable>
                        </View>
                    </View>
                </View>
            </View >
        </Modal >
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#050505' },
    header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 20, paddingTop: Platform.OS === 'ios' ? 50 : 20, height: 80 },
    title: { color: COLORS.primary, fontWeight: '900', fontSize: 13, letterSpacing: 2 },
    closeBtn: { padding: 8, backgroundColor: 'rgba(255,255,255,0.05)', borderRadius: 20 },
    stage: { alignSelf: 'center', justifyContent: 'center', alignItems: 'center', backgroundColor: '#111', borderRadius: 12, overflow: 'hidden' },
    selector: { position: 'absolute', top: '50%', left: '50%', borderWidth: 2, borderColor: COLORS.primary, backgroundColor: 'rgba(255,215,0,0.1)' },
    gridLinesH: { ...StyleSheet.absoluteFillObject, borderTopWidth: 1, borderBottomWidth: 1, borderColor: 'rgba(255,215,0,0.15)', marginVertical: '33.3%' },
    gridLinesV: { ...StyleSheet.absoluteFillObject, borderLeftWidth: 1, borderRightWidth: 1, borderColor: 'rgba(255,215,0,0.15)', marginHorizontal: '33.3%' },
    cornerTL: { position: 'absolute', top: -3, left: -3, width: 14, height: 14, borderTopWidth: 3, borderLeftWidth: 3, borderColor: COLORS.primary },
    cornerTR: { position: 'absolute', top: -3, right: -3, width: 14, height: 14, borderTopWidth: 3, borderRightWidth: 3, borderColor: COLORS.primary },
    cornerBL: { position: 'absolute', bottom: -3, left: -3, width: 14, height: 14, borderBottomWidth: 3, borderLeftWidth: 3, borderColor: COLORS.primary },
    cornerBR: { position: 'absolute', bottom: -3, right: -3, width: 14, height: 14, borderBottomWidth: 3, borderRightWidth: 3, borderColor: COLORS.primary },
    dragHint: { position: 'absolute', top: '50%', left: '50%', transform: [{ translateX: -35 }, { translateY: -10 }], backgroundColor: COLORS.primary, paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6, flexDirection: 'row', alignItems: 'center', gap: 4, ...SHADOWS.md },
    dragText: { color: '#000', fontSize: 9, fontWeight: '900' },

    footer: { padding: 15, gap: 15, alignItems: 'center', backgroundColor: '#050505', paddingBottom: 35 },
    ratioRow: { flexDirection: 'row', gap: 8, backgroundColor: 'rgba(255,255,255,0.05)', padding: 4, borderRadius: 12 },
    ratioBtn: { paddingVertical: 6, paddingHorizontal: 12, borderRadius: 8 },
    ratioBtnActive: { backgroundColor: COLORS.primary },
    ratioText: { color: '#FFF', fontSize: 10, fontWeight: 'bold' },
    ratioTextActive: { color: '#000' },

    mainControlsRow: { flexDirection: 'row', width: '100%', justifyContent: 'space-around', alignItems: 'center', maxWidth: 600 },
    sideColumn: { alignItems: 'center', gap: 10, width: 80 },
    miniCtrlBtn: { width: 44, height: 44, borderRadius: 12, backgroundColor: 'rgba(255,255,255,0.05)', justifyContent: 'center', alignItems: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)' },
    miniLabel: { color: 'rgba(255,255,255,0.5)', fontSize: 9, fontWeight: '900' },

    dpadContainer: { alignItems: 'center', gap: 2 },
    dpadRow: { flexDirection: 'row', gap: 8, alignItems: 'center' },
    dpadBtn: { width: 42, height: 42, borderRadius: 21, backgroundColor: 'rgba(255,255,255,0.1)', justifyContent: 'center', alignItems: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.05)' },
    dpadCenter: { width: 50, height: 32, borderRadius: 16, justifyContent: 'center', alignItems: 'center' },

    // Updated Compact Buttons
    confirmBtnCompact: { backgroundColor: COLORS.primary, width: 55, height: 55, borderRadius: 28, justifyContent: 'center', alignItems: 'center', ...SHADOWS.lg },
    confirmTextCompact: { color: '#000', fontWeight: '900', fontSize: 8, marginTop: -2 },
    cutBtn: { backgroundColor: 'rgba(255,255,255,0.05)', width: 55, height: 55, borderRadius: 28, justifyContent: 'center', alignItems: 'center', borderWidth: 1, borderColor: COLORS.primary },
    cutText: { color: COLORS.primary, fontWeight: '900', fontSize: 8, marginTop: -2 },

    floatingChangeBtn: {
        position: 'absolute', top: 15, right: 15, width: 44, height: 44, borderRadius: 22,
        backgroundColor: 'rgba(0,0,0,0.6)', borderWidth: 1, borderColor: 'rgba(255,255,255,0.2)',
        justifyContent: 'center', alignItems: 'center', zIndex: 50
    }
});
