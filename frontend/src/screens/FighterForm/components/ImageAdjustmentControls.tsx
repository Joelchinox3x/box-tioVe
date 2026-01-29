import React from 'react';
import { View, Text, TouchableOpacity, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface ImageAdjustmentControlsProps {
    // Config
    isWeb: boolean;
    showDebug: boolean;
    hasPhoto: boolean;

    // Focus
    adjustmentFocus: 'photo' | string;
    setAdjustmentFocus: (focus: 'photo' | string) => void;
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
    onOpenBackgrounds: () => void;
    onOpenBorders: () => void;
    onOpenStickers: () => void;
    onRandomize: () => void;
}

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
    onOpenBackgrounds,
    onOpenBorders,
    onOpenStickers,
    onRandomize
}) => {
    // Render conditions
    if (!showDebug && !hasPhoto) {
        return null;
    }

    return (
        <View style={{ gap: 10, marginTop: 10, marginBottom: 10 }}>
            {/* Focal Selection (Photo vs Stickers) */}
            {(selectedStickers.length > 0) && (
                <View style={{ marginBottom: 5 }}>
                    <Text style={{ color: '#FFF', fontSize: 10, fontWeight: 'bold', textAlign: 'center', marginBottom: 8 }}>¿QUÉ QUIERES AJUSTAR?</Text>
                    <View style={{ flexDirection: 'row', justifyContent: 'center', flexWrap: 'wrap', gap: 8 }}>
                        <TouchableOpacity
                            onPress={() => setAdjustmentFocus('photo')}
                            style={{
                                paddingVertical: 6,
                                paddingHorizontal: 12,
                                borderRadius: 15,
                                backgroundColor: adjustmentFocus === 'photo' ? '#FFD700' : 'rgba(255,255,255,0.1)',
                                borderWidth: 1,
                                borderColor: adjustmentFocus === 'photo' ? '#FFD700' : 'rgba(255,255,255,0.2)'
                            }}
                        >
                            <Text style={{ color: adjustmentFocus === 'photo' ? '#000' : '#FFF', fontSize: 11, fontWeight: 'bold' }}>TU FOTO</Text>
                        </TouchableOpacity>

                        {selectedStickers.map((url, idx) => (
                            <TouchableOpacity
                                key={url}
                                onPress={() => setAdjustmentFocus(url)}
                                style={{
                                    paddingVertical: 4,
                                    paddingHorizontal: 8,
                                    borderRadius: 15,
                                    backgroundColor: adjustmentFocus === url ? '#FFD700' : 'rgba(255,255,255,0.1)',
                                    borderWidth: 1,
                                    borderColor: adjustmentFocus === url ? '#FFD700' : 'rgba(255,255,255,0.2)',
                                    flexDirection: 'row',
                                    alignItems: 'center',
                                    gap: 4
                                }}
                            >
                                <Ionicons name="sparkles" size={12} color={adjustmentFocus === url ? '#000' : '#FFD700'} />
                                <Text style={{ color: adjustmentFocus === url ? '#000' : '#FFF', fontSize: 10, fontWeight: 'bold' }}>S{idx + 1}</Text>
                            </TouchableOpacity>
                        ))}
                    </View>
                </View>
            )}

            {/* Layers Selection */}
            <View style={{ flexDirection: 'row', justifyContent: 'center', gap: 15, marginBottom: 5 }}>
                <TouchableOpacity onPress={onOpenBackgrounds} style={{ backgroundColor: 'rgba(255, 215, 0, 0.15)', paddingVertical: 8, paddingHorizontal: 12, borderRadius: 8, flexDirection: 'row', alignItems: 'center', gap: 6, borderWidth: 1, borderColor: 'rgba(255, 215, 0, 0.3)' }}>
                    <Ionicons name="image" size={16} color="#FFD700" />
                    <Text style={{ color: '#FFD700', fontSize: 12, fontWeight: 'bold' }}>FONDOS</Text>
                </TouchableOpacity>

                <TouchableOpacity onPress={onRandomize} style={{ backgroundColor: 'rgba(255, 215, 0, 0.15)', padding: 8, borderRadius: 8, borderWidth: 1, borderColor: 'rgba(255, 215, 0, 0.3)', alignItems: 'center', justifyContent: 'center' }}>
                    <Ionicons name="dice-outline" size={18} color="#FFD700" />
                </TouchableOpacity>

                <TouchableOpacity onPress={onOpenStickers} style={{ backgroundColor: 'rgba(255, 215, 0, 0.15)', paddingVertical: 8, paddingHorizontal: 12, borderRadius: 8, flexDirection: 'row', alignItems: 'center', gap: 6, borderWidth: 1, borderColor: 'rgba(255, 215, 0, 0.3)' }}>
                    <Ionicons name="sparkles-outline" size={16} color="#FFD700" />
                    <Text style={{ color: '#FFD700', fontSize: 12, fontWeight: 'bold' }}>STICKERS</Text>
                </TouchableOpacity>

                <TouchableOpacity onPress={onOpenBorders} style={{ backgroundColor: 'rgba(255, 215, 0, 0.15)', paddingVertical: 8, paddingHorizontal: 12, borderRadius: 8, flexDirection: 'row', alignItems: 'center', gap: 6, borderWidth: 1, borderColor: 'rgba(255, 215, 0, 0.3)' }}>
                    <Ionicons name="scan-outline" size={16} color="#FFD700" />
                    <Text style={{ color: '#FFD700', fontSize: 12, fontWeight: 'bold' }}>MARCOS</Text>
                </TouchableOpacity>
            </View>

            {/* Posición (X/Y) */}
            <View style={{ alignItems: 'center' }}>
                <Text style={{ color: '#FFF', fontSize: 10, fontWeight: 'bold', marginBottom: 5 }}>POSICIÓN</Text>
                <View style={{ flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 15 }}>
                    {setFlipX && (
                        <TouchableOpacity onPress={() => setFlipX(prev => !prev)} style={{ padding: 8, backgroundColor: flipX ? 'rgba(255,215,0,0.3)' : 'rgba(255,255,255,0.1)', borderRadius: 20, borderWidth: flipX ? 1 : 0, borderColor: '#FFD700' }}>
                            <Ionicons name="swap-horizontal" size={24} color="#FFF" />
                        </TouchableOpacity>
                    )}
                    <TouchableOpacity onPress={() => setOffsetX(prev => prev - 3)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                        <Ionicons name="arrow-back" size={24} color="#FFF" />
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => setOffsetY(prev => prev - 3)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                        <Ionicons name="arrow-up" size={24} color="#FFF" />
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => setOffsetY(prev => prev + 3)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                        <Ionicons name="arrow-down" size={24} color="#FFF" />
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => setOffsetX(prev => prev + 3)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                        <Ionicons name="arrow-forward" size={24} color="#FFF" />
                    </TouchableOpacity>
                </View>
            </View>

            {/* Zoom & Rotation */}
            <View style={{ flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 20 }}>
                <View style={{ alignItems: 'center' }}>
                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                        <TouchableOpacity onPress={() => setScale(prev => Math.max(0.1, prev - 0.1))} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                            <Ionicons name="remove" size={24} color="#FFF" />
                        </TouchableOpacity>
                        <Text style={{ color: '#FFF', fontSize: 12, fontWeight: 'bold', width: 80, textAlign: 'center' }}>ZOOM: {scale.toFixed(1)}x</Text>
                        <TouchableOpacity onPress={() => setScale(prev => Math.min(5, prev + 0.1))} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                            <Ionicons name="add" size={24} color="#FFF" />
                        </TouchableOpacity>
                    </View>
                </View>

                {setRotation && (
                    <View style={{ alignItems: 'center' }}>
                        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                            <TouchableOpacity onPress={() => setRotation(prev => prev - 3)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                                <Ionicons name="refresh" size={20} color="#FFF" />
                            </TouchableOpacity>
                            <TouchableOpacity onPress={() => setRotation(prev => prev + 3)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                                <Ionicons name="refresh-outline" size={20} color="#FFF" />
                            </TouchableOpacity>
                        </View>
                    </View>
                )}
            </View>

            {/* Remove Background Button (Web Only) */}
            {isWeb && adjustmentFocus === 'photo' && (
                <TouchableOpacity
                    onPress={onRemoveBackground}
                    disabled={isRemovingBg || !isLibReady}
                    style={{
                        backgroundColor: (isRemovingBg || !isLibReady) ? '#666' : 'rgba(255,0,0,0.3)',
                        paddingVertical: 8,
                        paddingHorizontal: 16,
                        borderRadius: 20,
                        alignSelf: 'center',
                        marginTop: 10,
                        borderWidth: 1,
                        borderColor: (isRemovingBg || !isLibReady) ? '#888' : 'rgba(255,0,0,0.5)',
                        flexDirection: 'row',
                        gap: 8,
                        alignItems: 'center'
                    }}
                >
                    {isRemovingBg ? <ActivityIndicator color="#FFF" size="small" /> : <Ionicons name="cut" size={18} color="#FFF" />}
                    <Text style={{ color: '#FFF', fontSize: 12, fontWeight: 'bold' }}>
                        {isRemovingBg ? "QUITANDO FONDO..." : (isLibReady ? "QUITAR FONDO (IA)" : "CARGANDO LIBRERÍA...")}
                    </Text>
                </TouchableOpacity>
            )}
        </View>
    );
};
