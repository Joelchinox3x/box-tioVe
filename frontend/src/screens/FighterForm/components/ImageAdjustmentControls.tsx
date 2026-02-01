import React, { useRef } from 'react';
import { View, Text, TouchableOpacity, ActivityIndicator, Platform, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface ImageAdjustmentControlsProps {
    // Config
    isWeb: boolean;
    showDebug: boolean;
    hasPhoto: boolean;

    // Focus
    adjustmentFocus: 'photo' | string;
    setAdjustmentFocus: (focus: 'photo' | string) => void;
    // Multi-Layer Props
    fighterLayers?: Array<{ id: string; uri: string; }>; // Minimal info needed for UI
    onAddLayer?: () => void; // Deprecated
    onLaunchCamera?: () => void;
    onLaunchGallery?: () => void;
    onRemoveLayer?: (id: string) => void;

    selectedStickers: string[];

    // Posición
    offsetX: number;
    setOffsetX: (value: number | ((prev: number) => number)) => void;
    offsetY: number;
    setOffsetY: (value: number | ((prev: number) => number)) => void;

    // Zoom
    scale: number;
    setScale: (value: number | ((prev: number) => number)) => void;

    // Flip (Solo foto)
    flipX?: boolean;
    setFlipX?: (value: boolean | ((prev: boolean) => boolean)) => void;

    // Rotation (Solo stickers)
    rotation?: number;
    setRotation?: (value: number | ((prev: number) => number)) => void;

    // Background Removal
    onRemoveBackground: () => void;
    isRemovingBg: boolean;
    isLibReady: boolean;

    // Template Selection
    activeTab: 'none' | 'backgrounds' | 'borders' | 'stickers';
    onPickPhoto: () => void;
    onOpenBackgrounds: () => void;
    onOpenBorders: () => void;
    onOpenStickers: () => void;
    onRandomize: () => void;
}

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

export const ImageAdjustmentControls: React.FC<ImageAdjustmentControlsProps> = ({
    isWeb,
    showDebug,
    hasPhoto,
    adjustmentFocus,
    setAdjustmentFocus,
    selectedStickers,
    offsetX,
    setOffsetX,
    offsetY,
    setOffsetY,
    scale,
    setScale,
    flipX,
    setFlipX,
    rotation,
    setRotation,
    onRemoveBackground,
    isRemovingBg,
    isLibReady,
    activeTab,
    onPickPhoto,
    onOpenBackgrounds,
    onOpenBorders,
    onOpenStickers,
    onRandomize,
    fighterLayers,
    onLaunchCamera,
    onLaunchGallery,
    onRemoveLayer
}) => {
    // Local state for expandable layer button
    const [showLayerOptions, setShowLayerOptions] = React.useState(false);

    // Render conditions
    if (!showDebug && !hasPhoto) {
        return null;
    }

    return (
        <View style={{ gap: 10, marginTop: 5, marginBottom: 10, zIndex: 999, elevation: 20 }}>
            {/* Toolbar Unificado de Acciones y Capas */}
            <View style={{ marginBottom: 10, width: '100%' }}>
                <View style={{
                    flexDirection: 'row',
                    alignItems: 'center',
                    backgroundColor: 'rgba(255,255,255,0.08)',
                    paddingVertical: 10,
                    paddingLeft: 5,
                    paddingRight: 15,
                    marginHorizontal: -15, // Compensa el paddingHorizontal de 24 (SPACING.lg)
                    borderWidth: 1,
                    borderRadius: 20,
                    borderColor: 'rgba(255,255,255,0.1)'
                }}>
                    {/* ACCIONES (Fijas Izquierda) */}
                    {/* ACCIONES (Fijas Izquierda) REMOVED - NOW INTEGRATED INTO LAYERS OR NOT NEEDED AS '+', 
                        Wait, we still need IA/Magic button?
                        Actually, typical flow is: Add Layer -> Crop -> IA removes background.
                        But if the layer is already added, we select it and verify if we can remove bg.
                        For now, let's KEEP the left actions for general global stuff or remove if redundant.
                        User said: "boton subjr o cambiar se va y en vez de eso un solo boton de +"
                        So we remove the left 'Subir' button. 
                    */}
                    {/* Botón de AGREGAR (Tipo Speed Dial / Popover) */}
                    {/* Botón de AGREGAR (Tipo Speed Dial / Popover) */}
                    {(!fighterLayers || fighterLayers.length < 3) && (
                        <View style={{ flexDirection: 'row', gap: 6, paddingRight: 8, borderRightWidth: 1, borderRightColor: 'rgba(255,255,255,0.1)', marginRight: 6, position: 'relative', zIndex: 100 }}>
                            <View style={{ alignItems: 'center', gap: 6 }}>
                                {/* Options Popover (Emergente Encima) */}
                                {showLayerOptions && (
                                    <View style={{
                                        position: 'absolute',
                                        bottom: 55, // Un poco más arriba (encima de la tarjeta)
                                        left: -12, // Centrado
                                        width: 60,
                                        alignItems: 'center',
                                        gap: 12,
                                        backgroundColor: '#1a1a1a',
                                        paddingVertical: 12,
                                        borderRadius: 30,
                                        borderWidth: 1,
                                        borderColor: 'rgba(255,255,255,0.2)',
                                        zIndex: 9999, // Z-Index interno alto
                                        elevation: 50, // Elevación máxima para Android
                                        shadowColor: '#000',
                                        shadowOffset: { width: 0, height: 4 },
                                        shadowOpacity: 0.5,
                                        shadowRadius: 5,
                                    }}>
                                        <TouchableOpacity
                                            onPress={() => { onLaunchCamera?.(); setShowLayerOptions(false); }}
                                            style={{
                                                width: 36, height: 36, borderRadius: 18,
                                                backgroundColor: '#333', justifyContent: 'center', alignItems: 'center',
                                                borderWidth: 1, borderColor: '#555'
                                            }}
                                        >
                                            <Ionicons name="camera" size={20} color="#FFF" />
                                        </TouchableOpacity>

                                        <TouchableOpacity
                                            onPress={() => { onLaunchGallery?.(); setShowLayerOptions(false); }}
                                            style={{
                                                width: 36, height: 36, borderRadius: 18,
                                                backgroundColor: '#333', justifyContent: 'center', alignItems: 'center',
                                                borderWidth: 1, borderColor: '#555'
                                            }}
                                        >
                                            <Ionicons name="images" size={20} color="#FFF" />
                                        </TouchableOpacity>

                                        {/* Flechita decorativa abajo */}
                                        <View style={{
                                            position: 'absolute', bottom: -6, left: 22,
                                            width: 0, height: 0,
                                            borderLeftWidth: 6, borderRightWidth: 6, borderTopWidth: 6,
                                            borderStyle: 'solid',
                                            backgroundColor: 'transparent',
                                            borderLeftColor: 'transparent', borderRightColor: 'transparent', borderTopColor: 'rgba(0,0,0,0.9)'
                                        }} />
                                    </View>
                                )}

                                <TouchableOpacity
                                    onPress={() => setShowLayerOptions(!showLayerOptions)}
                                    style={{
                                        width: 36, height: 36, borderRadius: 18,
                                        backgroundColor: showLayerOptions ? '#FFF' : '#FFD700',
                                        justifyContent: 'center', alignItems: 'center',
                                        borderWidth: 1, borderColor: '#FFF'
                                    }}
                                >
                                    <Ionicons name={showLayerOptions ? "close" : "add"} size={24} color="#000" />
                                </TouchableOpacity>
                            </View>
                        </View>
                    )}

                    <View style={{ flex: 1 }}>
                        <View style={{ alignItems: 'flex-start' }}>
                            <View style={{ height: 40, justifyContent: 'center' }}>
                                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ flexDirection: 'row', alignItems: 'center', gap: 10, paddingHorizontal: 4, paddingRight: 10 }}>


                                    {/* FOTOS RENDERING */}
                                    {fighterLayers?.map((layer, idx) => (
                                        <TouchableOpacity
                                            key={layer.id}
                                            onPress={() => setAdjustmentFocus(layer.id)}
                                            style={{
                                                paddingVertical: 6,
                                                paddingHorizontal: 10,
                                                borderRadius: 15,
                                                backgroundColor: adjustmentFocus === layer.id ? '#FFD700' : 'rgba(255,255,255,0.08)',
                                                borderWidth: 1,
                                                borderColor: adjustmentFocus === layer.id ? '#FFD700' : 'rgba(255,255,255,0.1)',
                                                flexDirection: 'row',
                                                alignItems: 'center',
                                                gap: 4
                                            }}
                                        >
                                            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 2 }}>
                                                <Ionicons name="image" size={12} color={adjustmentFocus === layer.id ? '#000' : '#DDD'} />
                                                <Text style={{ color: adjustmentFocus === layer.id ? '#000' : '#DDD', fontSize: 10, fontWeight: 'bold' }}>F{idx + 1}</Text>
                                            </View>

                                            {/* Remove Button (Floating Badge) */}
                                            {onRemoveLayer && (
                                                <TouchableOpacity
                                                    onPress={() => onRemoveLayer(layer.id)}
                                                    style={{
                                                        position: 'absolute',
                                                        top: -6,
                                                        right: -6,
                                                        backgroundColor: '#FF4444',
                                                        width: 18,
                                                        height: 18,
                                                        borderRadius: 9,
                                                        justifyContent: 'center',
                                                        alignItems: 'center',
                                                        borderWidth: 1.5,
                                                        borderColor: '#000'
                                                    }}
                                                >
                                                    <Ionicons name="close" size={10} color="#FFF" />
                                                </TouchableOpacity>
                                            )}
                                        </TouchableOpacity>
                                    ))}

                                    {/* STICKERS RENDERING */}
                                    {selectedStickers.map((url, idx) => (
                                        <TouchableOpacity
                                            key={url}
                                            onPress={() => setAdjustmentFocus(url)}
                                            style={{
                                                paddingVertical: 6,
                                                paddingHorizontal: 10,
                                                borderRadius: 15,
                                                backgroundColor: adjustmentFocus === url ? '#FFD700' : 'rgba(255,255,255,0.08)',
                                                borderWidth: 1,
                                                borderColor: adjustmentFocus === url ? '#FFD700' : 'rgba(255,255,255,0.1)',
                                                flexDirection: 'row',
                                                alignItems: 'center',
                                                gap: 4
                                            }}
                                        >
                                            <Ionicons name="sparkles" size={12} color={adjustmentFocus === url ? '#000' : '#FFD700'} />
                                            <Text style={{ color: adjustmentFocus === url ? '#000' : '#FFF', fontSize: 10, fontWeight: 'bold' }}>S{idx + 1}</Text>
                                        </TouchableOpacity>
                                    ))}
                                </ScrollView>
                            </View>
                        </View>
                    </View>
                </View>
            </View>

            {/* Posición, Zoom, Rota & Espejo */}
            {(hasPhoto || selectedStickers.length > 0) && (
                <View style={{ gap: 12 }}>
                    <View style={{ flexDirection: 'row', justifyContent: 'center', alignItems: 'flex-start', gap: 14 }}>
                        {/* Posición Column */}
                        <View style={{ alignItems: 'center' }}>
                            <Text style={{ color: '#FFF', fontSize: 10, fontWeight: 'bold', marginBottom: 8 }}>POSICIÓN</Text>
                            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                                <AutoRepeatButton onPress={() => setOffsetX(prev => prev - 2)} style={{ padding: 7, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                                    <Ionicons name="arrow-back" size={20} color="#FFF" />
                                </AutoRepeatButton>
                                <View style={{ gap: 4 }}>
                                    <AutoRepeatButton onPress={() => setOffsetY(prev => prev - 2)} style={{ padding: 6, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                                        <Ionicons name="arrow-up" size={18} color="#FFF" />
                                    </AutoRepeatButton>
                                    <AutoRepeatButton onPress={() => setOffsetY(prev => prev + 2)} style={{ padding: 6, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                                        <Ionicons name="arrow-down" size={18} color="#FFF" />
                                    </AutoRepeatButton>
                                </View>
                                <AutoRepeatButton onPress={() => setOffsetX(prev => prev + 2)} style={{ padding: 7, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                                    <Ionicons name="arrow-forward" size={20} color="#FFF" />
                                </AutoRepeatButton>
                            </View>
                        </View>

                        {/* Zoom Column */}
                        <View style={{ alignItems: 'center' }}>
                            <Text style={{ color: '#FFF', fontSize: 10, fontWeight: 'bold', marginBottom: 8 }}>ZOOM</Text>
                            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
                                <AutoRepeatButton onPress={() => setScale(prev => Math.max(0.1, prev - 0.03))} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                                    <Ionicons name="remove" size={22} color="#FFF" />
                                </AutoRepeatButton>
                                <AutoRepeatButton onPress={() => setScale(prev => Math.min(5, prev + 0.03))} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                                    <Ionicons name="add" size={22} color="#FFF" />
                                </AutoRepeatButton>
                            </View>
                        </View>

                        {/* Rota Column */}
                        {setRotation && (
                            <View style={{ alignItems: 'center' }}>
                                <Text style={{ color: '#FFF', fontSize: 10, fontWeight: 'bold', marginBottom: 8 }}>ROTA</Text>
                                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
                                    <AutoRepeatButton onPress={() => setRotation(prev => prev - 2)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                                        <Ionicons name="refresh" size={22} color="#FFF" />
                                    </AutoRepeatButton>
                                    <AutoRepeatButton onPress={() => setRotation(prev => prev + 2)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                                        <Ionicons name="refresh-outline" size={22} color="#FFF" />
                                    </AutoRepeatButton>
                                </View>
                            </View>
                        )}

                        {/* Espejo Column */}
                        {setFlipX && (
                            <View style={{ alignItems: 'center' }}>
                                <Text style={{ color: '#FFF', fontSize: 10, fontWeight: 'bold', marginBottom: 8 }}>ESPEJO</Text>
                                <TouchableOpacity onPress={() => setFlipX(prev => !prev)} style={{ padding: 8, backgroundColor: flipX ? 'rgba(255,215,0,0.3)' : 'rgba(255,255,255,0.1)', borderRadius: 20, borderWidth: flipX ? 1 : 0, borderColor: '#FFD700' }}>
                                    <Ionicons name="swap-horizontal" size={22} color="#FFF" />
                                </TouchableOpacity>
                            </View>
                        )}
                    </View>
                </View>
            )}

            {/* Selector de Capas Principales (Movido abajo y Reordenado) */}
            <View style={{ flexDirection: 'row', justifyContent: 'center', gap: 6, marginTop: 5 }}>
                {/* Random Primero con Colorcito */}
                <TouchableOpacity onPress={onRandomize} style={{ backgroundColor: 'rgba(27, 117, 168, 0.15)', padding: 8, borderRadius: 10, borderWidth: 1, borderColor: 'rgba(27, 117, 168, 0.3)', alignItems: 'center', justifyContent: 'center' }}>
                    <Ionicons name="dice-outline" size={18} color="#199fc0ff" />
                </TouchableOpacity>

                <TouchableOpacity onPress={onOpenBackgrounds} style={{ backgroundColor: activeTab === 'backgrounds' ? '#FFD700' : 'rgba(255, 215, 0, 0.12)', paddingVertical: 8, paddingHorizontal: 10, borderRadius: 12, flexDirection: 'row', alignItems: 'center', gap: 4, borderWidth: 1, borderColor: activeTab === 'backgrounds' ? '#FFD700' : 'rgba(255, 215, 0, 0.25)' }}>
                    <Ionicons name="image" size={14} color={activeTab === 'backgrounds' ? '#000' : '#FFD700'} />
                    <Text style={{ color: activeTab === 'backgrounds' ? '#000' : '#FFD700', fontSize: 11, fontWeight: 'bold' }}>FONDOS</Text>
                </TouchableOpacity>

                <TouchableOpacity onPress={onOpenStickers} style={{ backgroundColor: activeTab === 'stickers' ? '#FFD700' : 'rgba(255, 215, 0, 0.12)', paddingVertical: 8, paddingHorizontal: 10, borderRadius: 12, flexDirection: 'row', alignItems: 'center', gap: 4, borderWidth: 1, borderColor: activeTab === 'stickers' ? '#FFD700' : 'rgba(255, 215, 0, 0.25)' }}>
                    <Ionicons name="sparkles" size={14} color={activeTab === 'stickers' ? '#000' : '#FFD700'} />
                    <Text style={{ color: activeTab === 'stickers' ? '#000' : '#FFD700', fontSize: 11, fontWeight: 'bold' }}>STICKERS</Text>
                </TouchableOpacity>

                <TouchableOpacity onPress={onOpenBorders} style={{ backgroundColor: activeTab === 'borders' ? '#FFD700' : 'rgba(255, 215, 0, 0.12)', paddingVertical: 8, paddingHorizontal: 10, borderRadius: 12, flexDirection: 'row', alignItems: 'center', gap: 4, borderWidth: 1, borderColor: activeTab === 'borders' ? '#FFD700' : 'rgba(255, 215, 0, 0.25)' }}>
                    <Ionicons name="scan" size={14} color={activeTab === 'borders' ? '#000' : '#FFD700'} />
                    <Text style={{ color: activeTab === 'borders' ? '#000' : '#FFD700', fontSize: 11, fontWeight: 'bold' }}>MARCOS</Text>
                </TouchableOpacity>
            </View>


        </View>
    );
};
