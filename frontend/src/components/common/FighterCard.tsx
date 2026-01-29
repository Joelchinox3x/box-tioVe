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
    backgroundUri?: string | null;
    backgroundOffsetY?: number;
    backgroundOffsetX?: number;
    backgroundScale?: number;
    backgroundFlipX?: boolean;
    backgroundRotation?: number;
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
    stickerTransforms = {}
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
        const numPeso = parseFloat(p);
        if (isNaN(numPeso)) return '---';
        if (numPeso < 60) return 'PESO PLUMA';
        if (numPeso < 70) return 'PESO LIGERO';
        if (numPeso < 80) return 'PESO WÉLTER';
        if (numPeso < 90) return 'PESO MEDIO';
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

                {/* LAYER 2: FIGHTER PHOTO */}
                {photoUri && (
                    <Image
                        source={{ uri: photoUri }}
                        style={[
                            StyleSheet.absoluteFill,
                            styles.cardImage,
                            {
                                transform: [
                                    { translateY: backgroundOffsetY },
                                    { translateX: backgroundOffsetX },
                                    { scale: backgroundScale },
                                    { scaleX: backgroundFlipX ? -1 : 1 },
                                    { rotate: `${backgroundRotation}deg` }
                                ]
                            }
                        ]}
                        resizeMode={backgroundResizeMode}
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
                                    zIndex: 11 + index, // Above Border (zIndex 10)
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
                        style={[StyleSheet.absoluteFill, { zIndex: 10, backgroundColor: 'transparent' }]}
                        resizeMode="stretch"
                    />
                )}

                {/* LAYER 4: CONTENT */}
                <LinearGradient
                    colors={['rgba(0,0,0,0)', 'rgba(0,0,0,0.3)', 'rgba(0,0,0,0.9)']}
                    style={[styles.overlay, { zIndex: 20 }]}
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
                                    {displayNombre} {apodo ? <Text style={{ color: '#FFD700', fontStyle: 'italic' }}>"{apodo}"</Text> : ''} {displayApellido}
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
            {variant === 'preview' && (
                <Text style={styles.hint}>VISTA PREVIA DE TU FICHA</Text>
            )}

            {/* Upload Button Overlay */}
            {onUploadBackground && (
                <TouchableOpacity
                    style={styles.uploadButton}
                    onPress={onUploadBackground}
                    activeOpacity={0.8}
                >
                    <Ionicons name="camera" size={20} color="#000" />
                    <Text style={styles.uploadButtonText}>TU FOTO</Text>
                </TouchableOpacity>
            )}

            {/* Share Button Overlay */}
            {onShare && (
                <TouchableOpacity
                    style={styles.shareButton}
                    onPress={onShare}
                    activeOpacity={0.8}
                >
                    <Ionicons name="logo-whatsapp" size={20} color="#fff" />
                    <Text style={styles.shareButtonText}>COMPARTIR</Text>
                </TouchableOpacity>
            )}
        </ContainerComponent>
    );
};
