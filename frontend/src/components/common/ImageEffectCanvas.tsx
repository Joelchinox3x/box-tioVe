import React, { useMemo, useEffect } from "react";
import { View, StyleSheet, ActivityIndicator, Image as RNImage } from "react-native";
import {
    Canvas,
    Image,
    useImage,
    Group,
    Blur,
    ColorMatrix,
    Paint,
    rect,
    rrect
} from "@shopify/react-native-skia";

// Helper types for Matrix
type Matrix = number[];

// Helper functions to generate matrices (Standard W3C/SVG implementation)
const concat = (m1: Matrix, m2: Matrix): Matrix => {
    const result = new Array(20).fill(0);
    // Simplification: We usually just chain <ColorMatrix> components in Skia for ease
    // But for performance, multiplying is better.
    // For now, let's use the visual nesting approach or simple pre-sets.
    return []; // Placeholder if we need manual mult
};

// 1. Brightness
const brightness = (b: number): Matrix => [
    1, 0, 0, 0, b,
    0, 1, 0, 0, b,
    0, 0, 1, 0, b,
    0, 0, 0, 1, 0,
];

// 2. Contrast
const contrast = (c: number): Matrix => {
    const t = 0.5 * (1 - c);
    return [
        c, 0, 0, 0, t,
        0, c, 0, 0, t,
        0, 0, c, 0, t,
        0, 0, 0, 1, 0,
    ];
};

// 3. Saturation
const saturate = (s: number): Matrix => {
    const lumR = 0.3086;
    const lumG = 0.6094;
    const lumB = 0.0820;

    const sr = (1 - s) * lumR;
    const sg = (1 - s) * lumG;
    const sb = (1 - s) * lumB;

    return [
        sr + s, sg, sb, 0, 0,
        sr, sg + s, sb, 0, 0,
        sr, sg, sb + s, 0, 0,
        0, 0, 0, 1, 0,
    ];
};

interface ImageEffectCanvasProps {
    imageUri: string | null;
    width: number;
    height: number;
    effect: 'none' | 'shadow' | 'aura' | 'neon' | 'ghost' | 'glitch';
    preset?: 'original' | 'grit' | 'vibrant' | 'bw' | 'cinematic';
    primaryColor?: string;
}

export const ImageEffectCanvas: React.FC<ImageEffectCanvasProps> = ({
    imageUri,
    width,
    height,
    effect,
    preset = 'original',
    primaryColor = "#00FFFF"
}) => {
    const skiaImage = useImage(imageUri);

    // Calculate Filter Matrix for Preset
    const presetMatrix = useMemo(() => {
        if (preset === 'grit') return [contrast(1.4), saturate(0.6), brightness(0.1)];
        if (preset === 'vibrant') return [contrast(1.1), saturate(1.5)];
        if (preset === 'bw') return [saturate(0)];
        if (preset === 'cinematic') return [contrast(1.5), saturate(0.8)];
        return null;
    }, [preset]);

    // Helper: Hex to RGB 0-1
    const hexToRgb = (hex: string) => {
        const clean = hex.replace('#', '');
        const r = parseInt(clean.substring(0, 2), 16) / 255;
        const g = parseInt(clean.substring(2, 4), 16) / 255;
        const b = parseInt(clean.substring(4, 6), 16) / 255;
        return { r, g, b };
    };

    const colorFilter = useMemo(() => {
        const { r, g, b } = hexToRgb(primaryColor);
        return [
            0, 0, 0, 0, r,
            0, 0, 0, 0, g,
            0, 0, 0, 0, b,
            0, 0, 0, 1, 0
        ];
    }, [primaryColor]);

    // FALLBACK logic for stuck loading state
    if (!imageUri || !skiaImage) {
        if (imageUri) {
            return (
                <RNImage
                    source={{ uri: imageUri }}
                    style={{ width, height }}
                    resizeMode="contain"
                />
            );
        }
        return <View style={{ width, height }} />;
    }

    // Helper to render filters
    const renderFilters = () => {
        if (!presetMatrix || presetMatrix.length === 0) return null;
        return (
            <Paint>
                {presetMatrix.map((m, i) => (
                    <ColorMatrix key={i} matrix={m} />
                ))}
            </Paint>
        );
    };

    return (
        <Canvas style={{ width, height }}>
            {/* 1. LAYER: BACKGLOW (Aura / Neon) - NOT affected by filters */}
            {(effect === 'aura' || effect === 'neon') && (
                <Group>
                    <Paint>
                        <Blur blur={effect === 'aura' ? 15 : 4} />
                        <ColorMatrix matrix={colorFilter} />
                    </Paint>
                    <Image
                        image={skiaImage}
                        fit="contain"
                        x={0} y={0} width={width} height={height}
                        opacity={effect === 'aura' ? 0.8 : 1}
                    />
                </Group>
            )}

            {/* 2. LAYER: SUBJECT CONTENT (Filters + Subject Effects) */}
            <Group layer={renderFilters()}>
                {/* EFECTO GHOST */}
                {effect === 'ghost' && (
                    <Group>
                        <Paint>
                            <ColorMatrix matrix={colorFilter} />
                        </Paint>
                        <Image
                            image={skiaImage}
                            fit="contain"
                            x={-15} y={0} width={width} height={height}
                            opacity={0.4}
                        />
                        <Image
                            image={skiaImage}
                            fit="contain"
                            x={15} y={0} width={width} height={height}
                            opacity={0.4}
                        />
                    </Group>
                )}

                {/* EFECTO GLITCH */}
                {effect === 'glitch' && (
                    <Group>
                        <Group layer={
                            <Paint>
                                <ColorMatrix matrix={[
                                    1, 0, 0, 0, 0, // Red
                                    0, 0, 0, 0, 0,
                                    0, 0, 0, 0, 0,
                                    0, 0, 0, 1, 0
                                ]} />
                            </Paint>
                        }>
                            <Image
                                image={skiaImage}
                                fit="contain"
                                x={-5} y={0} width={width} height={height}
                                opacity={0.8}
                            />
                        </Group>

                        <Group layer={
                            <Paint>
                                <ColorMatrix matrix={[
                                    0, 0, 0, 0, 0,
                                    0, 1, 0, 0, 0, // Green
                                    0, 0, 1, 0, 0, // Blue
                                    0, 0, 0, 1, 0
                                ]} />
                            </Paint>
                        }>
                            <Image
                                image={skiaImage}
                                fit="contain"
                                x={5} y={0} width={width} height={height}
                                opacity={0.8}
                            />
                        </Group>
                    </Group>
                )}

                {/* IMAGEN ORIGINAL */}
                <Image
                    image={skiaImage}
                    fit="contain"
                    x={0} y={0} width={width} height={height}
                />
            </Group>
        </Canvas>
    );
};
