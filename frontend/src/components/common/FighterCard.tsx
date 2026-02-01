import React from 'react';
import { View, Text, StyleSheet, Image, ImageBackground, TouchableOpacity, ViewStyle, Platform, Dimensions } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { styles, cardWidth } from './FighterCard.styles';

interface FighterCardProps {
    fighter: {
        nombre: string;
        apellidos?: string;
        apodo?: string;
        peso?: string;
        genero?: string;
        photoUri?: string | null;
        clubName?: string;
        record?: string;
        edad?: string;
        altura?: string;
    };
    variant?: 'preview' | 'small' | 'large';
    onPress?: () => void;
    showBadge?: boolean;
    style?: ViewStyle;
    onUploadBackground?: () => void;
    onShare?: () => void;
    // Multi-Layer Support
    fighterLayers?: Array<{
        id: string;
        uri: string;
        x: number;
        y: number;
        scale: number;
        rotation: number;
        flipX: boolean;
        preset?: string;
        effect?: string;
        effectColor?: string;
    }>;
    // Legacy single props maintained for backward compat if needed, 
    // but we will prioritize fighterLayers
    backgroundUri?: string | null;
    backgroundOffsetY?: number; // Legacy
    backgroundOffsetX?: number; // Legacy
    backgroundScale?: number;   // Legacy
    backgroundFlipX?: boolean;  // Legacy
    backgroundRotation?: number;// Legacy
    borderUri?: string | null;
    backgroundResizeMode?: 'cover' | 'contain' | 'stretch' | 'center';
    companyLogoUri?: string | null;
    selectedStickers?: string[];
    stickerTransforms?: Record<string, { x: number, y: number, scale: number, rotation: number, flipX: boolean }>;
}

export const FighterCard: React.FC<FighterCardProps> = ({
    fighter,
    variant = 'large',
    onPress,
    showBadge = true,
    style,
    onUploadBackground,
    onShare,
    backgroundUri = null,
    backgroundOffsetY = 0,
    backgroundOffsetX = 0,
    backgroundScale = 1,
    backgroundFlipX = false,
    backgroundRotation = 0,
    borderUri = null,
    backgroundResizeMode = 'contain',
    companyLogoUri = null,
    selectedStickers = [],
    stickerTransforms = {},
    fighterLayers = [] // Default to empty array
}) => {
    const { nombre, apellidos, apodo, peso, genero, photoUri, clubName, record, edad, altura } = fighter;

    // Gender Color Logic
    const isFemale = genero?.toLowerCase() === 'femenino' || genero?.toLowerCase() === 'mujer';
    // Gold for Male, HotPink for Female
    const themeColor = isFemale ? '#FF69B4' : '#FFD700';
    const borderColor = isFemale ? 'rgba(255, 105, 180, 0.5)' : 'rgba(255, 215, 0, 0.25)';

    // Determinar categoría por peso
    const getCategoria = (p: string | undefined) => {
        if (!p) return '---';
        const kg = parseFloat(p);
        if (isNaN(kg)) return '---';

        if (kg <= 47.6) return 'PESO PAJA';
        if (kg <= 49.0) return 'MINIMOSCA';
        if (kg <= 50.8) return 'PESO MOSCA';
        if (kg <= 52.2) return 'SUPERMOSCA';
        if (kg <= 53.5) return 'PESO GALLO';
        if (kg <= 55.3) return 'SUPERGALLO';
        if (kg <= 57.2) return 'PESO PLUMA';
        if (kg <= 59.0) return 'SUPERPLUMA';
        if (kg <= 61.2) return 'PESO LIGERO';
        if (kg <= 63.5) return 'SUPERLIGERO';
        if (kg <= 66.7) return 'PESO WELTER';
        if (kg <= 69.9) return 'SUPERWELTER';
        if (kg <= 72.6) return 'PESO MEDIANO';
        if (kg <= 76.2) return 'SUPERMEDIANO';
        if (kg <= 79.4) return 'SEMIPESADO';
        if (kg <= 90.7) return 'PESO CRUCERO';
        return 'PESO PESADO';
    };

    const ContainerComponent = onPress ? TouchableOpacity : View;

    // Name Logic: If empty, show placeholder "NOMBRE + APODO"
    // User wants it to clear as soon as a letter is typed.
    const hasName = nombre && nombre.length > 0;
    const fullName = hasName
        ? (apodo ? `${nombre} "${apodo}" ${apellidos || ''}` : `${nombre} ${apellidos || ''}`.trim())
        : "NOMBRE + APODO";

    // Dimension Logic using imported platform-specific cardWidth
    const isLarge = variant === 'large' || variant === 'preview';
    // WEB FIX: Enforce max width of 360 on web directly to avoid style resolution issues
    const webWidth = Platform.OS === 'web' ? Math.min(360, Dimensions.get('window').width - 32) : cardWidth;
    const finalWidth = isLarge ? (Platform.OS === 'web' ? webWidth : cardWidth) : '100%';
    const largeCardHeight = (Platform.OS === 'web' ? webWidth : cardWidth) / 2.2;
    const finalHeight = isLarge ? largeCardHeight : (variant === 'small' ? 120 : 180);
    const alignSelf = isLarge ? 'center' : 'auto';

    // Font Sizing
    const nameSize = variant === 'small' ? 14 : 22; // Increased size (was 18)

    // Realism Rule: Only show first name and first last name
    const displayNombre = nombre ? nombre.trim().split(/\s+/)[0] : '';
    const displayApellido = apellidos ? apellidos.trim().split(/\s+/)[0] : '';

    return (
        <ContainerComponent
            style={[
                styles.container,
                { width: finalWidth as any, alignSelf },
                style
            ]}
            onPress={onPress}
            activeOpacity={0.9}
        >
            <View style={[styles.card, { height: finalHeight, borderColor: borderColor }]}>
                {/* LAYER 1: SCENE BACKGROUND */}
                {backgroundUri ? (
                    <Image
                        source={{ uri: backgroundUri }}
                        style={[StyleSheet.absoluteFill, { width: '100%', height: '100%' }]}
                        resizeMode="cover"
                    />
                ) : (
                    <View style={[StyleSheet.absoluteFill, { backgroundColor: '#111' }]} />
                )}

                {/* LAYER 2: FIGHTER LAYERS (Multi-Layer) */}
                {fighterLayers.map((layer, index) => {
                    // Match Presets from Cropper
                    const PRESETS: any = {
                        original: { contrast: 1, saturate: 1, brightness: 1 },
                        grit: { contrast: 1.4, saturate: 0.6, brightness: 0.9 },
                        vibrant: { contrast: 1.1, saturate: 1.5, brightness: 1.1 },
                        bw: { contrast: 1.2, saturate: 0, brightness: 1 },
                        cinematic: { contrast: 1.5, saturate: 0.8, brightness: 0.85 },
                    };
                    const p = PRESETS[layer.preset || 'original'] || PRESETS.original;
                    const color = layer.effectColor || '#00FFFF';
                    const cssFilter = Platform.OS === 'web' ? `
                        contrast(${p.contrast}) 
                        saturate(${p.saturate}) 
                        brightness(${p.brightness})
                        ${layer.effect === 'aura' ? `drop-shadow(0 0 12px ${color})` : ''}
                        ${layer.effect === 'neon' ? `drop-shadow(0 0 4px #FFF) drop-shadow(0 0 12px ${color})` : ''}
                        ${layer.effect === 'ghost' ? `drop-shadow(15px 15px 0px ${color}88)` : ''}
                        ${layer.effect === 'glitch' ? `drop-shadow(-3px 0 0 #F00) drop-shadow(3px 0 0 ${color})` : ''}
                    ` : undefined;

                    return (
                        <Image
                            key={layer.id}
                            source={{ uri: layer.uri }}
                            style={[
                                StyleSheet.absoluteFill,
                                styles.cardImage,
                                {
                                    zIndex: 5 + index, // Base Z for Fighter Layers
                                    filter: cssFilter as any,
                                    transform: [
                                        { translateY: layer.y },
                                        { translateX: layer.x },
                                        { scale: layer.scale },
                                        { scaleX: layer.flipX ? -1 : 1 },
                                        { rotate: `${layer.rotation}deg` }
                                    ]
                                }
                            ]}
                            resizeMode="contain"
                        />
                    );
                })}

                {/* Fallback for Legacy Single Photo if no layers provided but photoUri/fighter.photoUri exists */}
                {fighterLayers.length === 0 && (photoUri || fighter.photoUri) && (
                    <Image
                        source={{ uri: (photoUri || fighter.photoUri) as string }}
                        style={[StyleSheet.absoluteFill, styles.cardImage, { zIndex: 5 }]}
                        resizeMode="contain"
                    />
                )}

                {/* LAYER 3: COMPANY LOGO */}
                {companyLogoUri && (
                    <Image
                        source={{ uri: companyLogoUri }}
                        style={styles.companyLogo}
                        resizeMode="contain"
                    />
                )}

                {/* LAYER 4: STICKERS */}
                {selectedStickers.map((uri, index) => {
                    const transform = stickerTransforms[uri] || { x: 0, y: 0, scale: 1, rotation: 0, flipX: false };
                    return (
                        <Image
                            key={`${uri}-${index}`}
                            source={{ uri }}
                            style={[
                                StyleSheet.absoluteFill,
                                styles.cardImage,
                                {
                                    zIndex: 11 + index, // Between Photo (0) and Border (40)
                                    transform: [
                                        { translateY: transform.y },
                                        { translateX: transform.x },
                                        { scale: transform.scale },
                                        { scaleX: transform.flipX ? -1 : 1 },
                                        { rotate: `${transform.rotation}deg` }
                                    ]
                                }
                            ]}
                            resizeMode="contain"
                        />
                    );
                })}

                {/* LAYER 5: BORDER */}
                {borderUri && (
                    <Image
                        source={{ uri: borderUri }}
                        style={[StyleSheet.absoluteFill, { zIndex: 40, backgroundColor: 'transparent' }]}
                        resizeMode="stretch"
                    />
                )}

                {/* LAYER 6: CONTENT OVERLAY */}
                <LinearGradient
                    colors={['rgba(0,0,0,0)', 'rgba(0,0,0,0.3)', 'rgba(0,0,0,0.9)']}
                    style={[styles.overlay, { zIndex: 100 }]}
                >
                    <View style={styles.topInfo}>
                        {showBadge && (
                            <View style={[styles.badge, { backgroundColor: themeColor }]}>
                                <Text style={styles.badgeText}>{getCategoria(peso)}</Text>
                            </View>
                        )}

                        <View style={{ flex: 1 }} />

                        {/* CLUB NAME moved to Top Right */}
                        {clubName && variant !== 'small' && (
                            <View style={[styles.clubRow, { marginBottom: 0 }]}>
                                <Ionicons name="business" size={14} color={themeColor} />
                                <Text style={[styles.clubText, { color: 'white', fontWeight: 'bold' }]}>{clubName}</Text>
                            </View>
                        )}

                        {record && variant !== 'small' && (
                            <View style={[styles.recordBadge, { borderColor: themeColor, marginLeft: 8 }]}>
                                <Text style={[styles.recordLabel, { color: themeColor }]}>RÉCORD</Text>
                                <Text style={styles.recordValue}>{record}</Text>
                            </View>
                        )}
                    </View>

                    <View style={styles.bottomInfo}>
                        <Text
                            style={[
                                styles.nickname,
                                {
                                    fontSize: nameSize,
                                    marginBottom: 4,
                                    alignSelf: 'flex-start',
                                    opacity: hasName ? 1 : 0.5,
                                    color: '#FFFFFF',
                                    maxWidth: '100%',
                                }
                            ]}
                            numberOfLines={2}
                            adjustsFontSizeToFit
                            minimumFontScale={0.6}
                        >
                            {hasName ? (
                                <>
                                    {displayNombre} {apodo ? <Text style={{ color: themeColor, fontStyle: 'italic' }}>"{apodo}"</Text> : ''} {displayApellido}
                                </>
                            ) : "NOMBRE + APODO"}
                        </Text>

                        {/* Club Removed from here */}

                        {variant !== 'small' && (
                            <View style={styles.statsRow}>
                                <View style={[styles.stat, styles.statBorder]}>
                                    <Text style={[styles.statLabel, { color: themeColor }]}>EDAD</Text>
                                    <Text style={styles.statValue}>{edad ? `${edad} AÑOS` : '--'}</Text>
                                </View>
                                <View style={styles.stat}>
                                    <Text style={[styles.statLabel, { color: themeColor }]}>ALTURA</Text>
                                    <Text style={styles.statValue}>{altura ? `${altura} CM` : '--'}</Text>
                                </View>
                            </View>
                        )}
                    </View>
                </LinearGradient>
            </View>
        </ContainerComponent>
    );
};
