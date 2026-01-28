import React from 'react';
import { View, Text, TouchableOpacity, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface ImageAdjustmentControlsProps {
    // Config
    isWeb: boolean;
    showDebug: boolean;
    hasPhoto: boolean;

    // Posición
    setBgOffsetX: React.Dispatch<React.SetStateAction<number>>;
    setBgOffsetY: React.Dispatch<React.SetStateAction<number>>;

    // Zoom
    bgScale: number;
    setBgScale: React.Dispatch<React.SetStateAction<number>>;

    // Flip
    bgFlipX: boolean;
    setBgFlipX: React.Dispatch<React.SetStateAction<boolean>>;

    // Background Removal
    onRemoveBackground: () => void;
    isRemovingBg: boolean;
    isLibReady: boolean;

    // Template Selection
    // Template Selection
    onOpenBackgrounds: () => void;
    onOpenBorders: () => void;
    onRandomize: () => void;
}

export const ImageAdjustmentControls: React.FC<ImageAdjustmentControlsProps> = ({
    isWeb,
    showDebug,
    hasPhoto,
    setBgOffsetX,
    setBgOffsetY,
    bgScale,
    setBgScale,
    bgFlipX,
    setBgFlipX,
    onRemoveBackground,
    isRemovingBg,
    isLibReady,
    onOpenBackgrounds,
    onOpenBorders,
    onRandomize
}) => {
    // Render conditions
    if (!showDebug && !hasPhoto) {
        return null;
    }

    return (
        <View style={{ gap: 10, marginTop: 10, marginBottom: 10 }}>
            {/* Layers Selection */}
            <View style={{ flexDirection: 'row', justifyContent: 'center', gap: 15, marginBottom: 5 }}>
                <TouchableOpacity onPress={onOpenBackgrounds} style={{ backgroundColor: 'rgba(255, 215, 0, 0.15)', paddingVertical: 8, paddingHorizontal: 12, borderRadius: 8, flexDirection: 'row', alignItems: 'center', gap: 6, borderWidth: 1, borderColor: 'rgba(255, 215, 0, 0.3)' }}>
                    <Ionicons name="image" size={16} color="#FFD700" />
                    <Text style={{ color: '#FFD700', fontSize: 12, fontWeight: 'bold' }}>FONDOS</Text>
                </TouchableOpacity>

                <TouchableOpacity onPress={onRandomize} style={{ backgroundColor: 'rgba(255, 215, 0, 0.15)', padding: 8, borderRadius: 8, borderWidth: 1, borderColor: 'rgba(255, 215, 0, 0.3)', alignItems: 'center', justifyContent: 'center' }}>
                    <Ionicons name="dice-outline" size={18} color="#FFD700" />
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
                    <TouchableOpacity onPress={() => setBgFlipX(prev => !prev)} style={{ padding: 8, backgroundColor: bgFlipX ? 'rgba(255,215,0,0.3)' : 'rgba(255,255,255,0.1)', borderRadius: 20, borderWidth: bgFlipX ? 1 : 0, borderColor: '#FFD700' }}>
                        <Ionicons name="swap-horizontal" size={24} color="#FFF" />
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => setBgOffsetX(prev => prev - 20)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                        <Ionicons name="arrow-back" size={24} color="#FFF" />
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => setBgOffsetY(prev => prev - 20)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                        <Ionicons name="arrow-up" size={24} color="#FFF" />
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => setBgOffsetY(prev => prev + 20)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                        <Ionicons name="arrow-down" size={24} color="#FFF" />
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => setBgOffsetX(prev => prev + 20)} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                        <Ionicons name="arrow-forward" size={24} color="#FFF" />
                    </TouchableOpacity>
                </View>
            </View>

            {/* Zoom */}
            <View style={{ flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 20 }}>
                <TouchableOpacity onPress={() => setBgScale(prev => Math.max(0.1, prev - 0.1))} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                    <Ionicons name="remove" size={24} color="#FFF" />
                </TouchableOpacity>
                <Text style={{ color: '#FFF', fontSize: 12, fontWeight: 'bold', width: 80, textAlign: 'center' }}>ZOOM: {bgScale.toFixed(1)}x</Text>
                <TouchableOpacity onPress={() => setBgScale(prev => Math.min(5, prev + 0.1))} style={{ padding: 8, backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 20 }}>
                    <Ionicons name="add" size={24} color="#FFF" />
                </TouchableOpacity>
            </View>

            {/* Remove Background Button (Web Only) */}
            {isWeb && (
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
