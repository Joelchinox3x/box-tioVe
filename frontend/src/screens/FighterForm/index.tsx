import React from 'react';
import { View, Text, TouchableOpacity, ActivityIndicator, Image, Modal, Platform, ScrollView } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { StatusBar } from 'expo-status-bar';
import { KeyboardAwareScrollView } from 'react-native-keyboard-aware-scroll-view';
import { Ionicons } from '@expo/vector-icons';

import { useFighterForm } from './useFighterForm';
import { styles } from './styles';
import { COLORS } from '../../constants/theme';
import { ScreenHeader } from '../../components/common/ScreenHeader';
import { FormSection } from '../../components/form/FormSection';
import { FormInput } from '../../components/form/FormInput';
import { FormSelect } from '../../components/form/FormSelect';
import { PhoneInput } from '../../components/form/PhoneInput';
import { ClubSelector } from '../../components/form/ClubSelector';
import { FighterCard } from '../../components/common/FighterCard';
import { ImageAdjustmentControls } from './components/ImageAdjustmentControls';
import { FighterImageUploadModal } from './components/FighterImageUploadModal';
import { EpicFighterSuccessModal } from '../../components/EpicFighterSuccessModal';
import { FighterIdentityModal } from '../../components/FighterIdentityModal';
import { SponsorFooter } from './components/SponsorFooter';
import { ImageCropper } from './components/ImageCropper';

const SHOW_DEBUG_GENERATOR = true;

import { generateDebugFighter } from '../../data/dummyFighters';

const FighterFormScreen = () => {
    // UI State for Template Lists
    const [activeTemplateTab, setActiveTemplateTab] = React.useState<'none' | 'backgrounds' | 'borders' | 'stickers'>('none');
    // Local UI state for expandable photo button
    const [showProfilePhotoOptions, setShowProfilePhotoOptions] = React.useState(false);

    const {
        navigation, scrollViewRef, handleFieldLayout, handleSectionLayout,
        formData, updateField, focusedField, setFocusedField, errors, isSubmitting, clubs, loadingClubs,
        isFieldValid, getFieldError, getFieldSuccess,
        currentStep, totalSteps, handleNext, handleBack,
        photo, showImageOptions, setShowImageOptions, imageUploadMode, setImageUploadMode,
        isRemovingBg, isLibReady,
        adjustmentFocus, setAdjustmentFocus, stickerTransforms, setStickerTransforms,
        backgroundTemplates, borderTemplates, stickerTemplates, selectedBorder, setSelectedBorder, selectedBackground, setSelectedBackground, selectedStickers,
        handleRemoveBackground, launchCamera, launchGallery, pickProfilePhoto, pickCardBackground, setCardBackgroundUrl, clearProfilePhoto,
        toggleSticker,
        banners, currentBannerIndex, isManualSelection,
        isImageCropperVisible, setIsImageCropperVisible, pendingImageUri, handleImageCropConfirm,
        handleSubmit, showSuccessModal, successData, handleCloseSuccessModal,
        checkingAuth, existingFighter, showIdentityModal, setShowIdentityModal, isAutoLoggedIn, validateField, handleBlurField,
        fillDebugData, randomizeDesign, companyLogoUri, toggleBorder, resetDesign,
        // Multi-Layer & Unified Transforms
        fighterLayers, addFighterLayer, removeFighterLayer, lastImageSource,
        currentOffsetX, currentOffsetY, currentScale, currentRotation, currentFlipX,
        updateOffsetX, updateOffsetY, updateScale, updateRotation, updateFlipX
    } = useFighterForm();

    // Unified helpers are now imported from the hook. 
    // Local wrappers removed.

    // Derived values for UI if needed (legacy code relied on isPhotoFocus locally)
    const isPhotoFocus = adjustmentFocus !== 'photo' && !fighterLayers?.find(l => l.id === adjustmentFocus) ? false : true;
    // Actually, simple check:

    return (
        <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
            <StatusBar style="light" backgroundColor="#000" />
            <ScreenHeader title="EL JAB DORADO" subtitle="INSCRIPCIÓN DE PELEADOR" slogan="Únete a la élite del boxeo profesional" />

            {checkingAuth ? (
                <View style={styles.loadingContainer}>
                    <ActivityIndicator size="large" color={COLORS.primary} />
                    <Text style={styles.loadingText}>Verificando perfil...</Text>
                </View>
            ) : existingFighter ? (
                <View style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
                    <FighterIdentityModal
                        visible={showIdentityModal}
                        onClose={() => { setShowIdentityModal(false); setTimeout(() => navigation.navigate('Home' as never), 100); }}
                        onEdit={() => { setShowIdentityModal(false); setTimeout(() => navigation.navigate('Profile' as never), 100); }}
                        fighter={{ ...existingFighter, photoUri: existingFighter.foto_perfil ? `https://boxtiove.com/storage/${existingFighter.foto_perfil}` : null }}
                    />
                </View>
            ) : (
                <KeyboardAwareScrollView
                    ref={scrollViewRef}
                    style={styles.scrollView}
                    contentContainerStyle={styles.scrollContent}
                    enableOnAndroid
                    extraScrollHeight={Platform.OS === 'ios' ? 0 : 100}
                >
                    {/* Progress Bar */}
                    <View style={styles.progressContainer}>
                        <View style={styles.stepsTextContainer}>
                            <Text style={styles.stepTitle}>PASO {currentStep}: {currentStep === 1 ? 'DATOS PERSONALES' : currentStep === 2 ? 'PERFIL ATLÉTICO' : 'FOTO OFICIAL'}</Text>
                            <Text style={styles.stepText}>{currentStep} / {totalSteps}</Text>
                        </View>
                        <View style={styles.progressBackground}>
                            <View style={[styles.progressBar, { width: `${(currentStep / totalSteps) * 100}%` }]} />
                        </View>
                    </View>

                    <View style={styles.formContainer}>
                        {/* Vista Previa de la Ficha - Siempre Visible */}
                        <View style={{ width: '100%', alignItems: 'center', overflow: 'visible', zIndex: 1, marginBottom: 5 }}>
                            <View style={{ width: '100%', flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10, paddingHorizontal: 5 }}>
                                <Text style={{ color: 'rgba(255,255,255,0.5)', fontSize: 11, fontWeight: '800', letterSpacing: 1 }}>VISTA PREVIA DE TU FICHA</Text>
                                <TouchableOpacity
                                    onPress={resetDesign}
                                    style={{
                                        flexDirection: 'row',
                                        alignItems: 'center',
                                        gap: 6,
                                        backgroundColor: 'rgba(255,100,100,0.1)',
                                        paddingVertical: 5,
                                        paddingHorizontal: 10,
                                        borderRadius: 12,
                                        borderWidth: 1,
                                        borderColor: 'rgba(255,100,100,0.2)'
                                    }}
                                >
                                    <Ionicons name="refresh" size={14} color="#FF6464" />
                                    <Text style={{ color: '#FF6464', fontSize: 10, fontWeight: 'bold' }}>RESET</Text>
                                </TouchableOpacity>
                            </View>
                            <FighterCard
                                fighter={{
                                    nombre: formData.nombre,
                                    apellidos: formData.apellidos,
                                    apodo: formData.apodo,
                                    peso: formData.peso,
                                    genero: formData.genero,
                                    photoUri: undefined,
                                    clubName: clubs?.find(c => c.id == formData.club_id)?.nombre,
                                    edad: formData.edad,
                                    altura: formData.altura
                                }}
                                variant="preview"
                                backgroundUri={selectedBackground}

                                // Multi-Layer Props
                                fighterLayers={fighterLayers}

                                // Legacy Props (Can receive defaults or be ignored)
                                // backgroundOffsetY={0} 
                                // backgroundOffsetX={0} 
                                // backgroundScale={1}

                                borderUri={selectedBorder}
                                selectedStickers={selectedStickers}
                                stickerTransforms={stickerTransforms}
                                companyLogoUri={companyLogoUri}
                            />
                        </View>

                        {/* Background Adjustment Controls (Step 3, Web & Mobile) */}
                        {(currentStep === 3 || SHOW_DEBUG_GENERATOR) && (
                            <ImageAdjustmentControls
                                isWeb={Platform.OS === 'web'}
                                showDebug={SHOW_DEBUG_GENERATOR}
                                hasPhoto={fighterLayers.length > 0} // Show props if layers exist
                                adjustmentFocus={adjustmentFocus}
                                setAdjustmentFocus={setAdjustmentFocus}
                                selectedStickers={selectedStickers}
                                offsetX={currentOffsetX}
                                setOffsetX={updateOffsetX}
                                offsetY={currentOffsetY}
                                setOffsetY={updateOffsetY}
                                scale={currentScale}
                                setScale={updateScale}
                                flipX={currentFlipX}
                                setFlipX={updateFlipX}
                                rotation={currentRotation} // currentRotation handles both layer and sticker
                                setRotation={updateRotation}
                                onRemoveBackground={handleRemoveBackground}
                                activeTab={activeTemplateTab}
                                onPickPhoto={pickCardBackground}
                                onOpenBackgrounds={() => setActiveTemplateTab(prev => prev === 'backgrounds' ? 'none' : 'backgrounds')}
                                onOpenBorders={() => setActiveTemplateTab(prev => prev === 'borders' ? 'none' : 'borders')}
                                onOpenStickers={() => setActiveTemplateTab(prev => prev === 'stickers' ? 'none' : 'stickers')}
                                onRandomize={randomizeDesign}
                                isRemovingBg={isRemovingBg}
                                isLibReady={isLibReady}

                                // Multi-Layer
                                fighterLayers={fighterLayers}
                                onLaunchCamera={() => launchCamera('background')}
                                onLaunchGallery={() => launchGallery('background')}
                                onRemoveLayer={removeFighterLayer}
                            />
                        )}

                        {/* Template Galleries */}
                        {activeTemplateTab !== 'none' && (
                            <View style={{ marginVertical: 10 }}>
                                <ScrollView
                                    horizontal
                                    showsHorizontalScrollIndicator={Platform.OS === 'web'}
                                    contentContainerStyle={{ gap: 10, paddingHorizontal: 5, paddingBottom: 10 }}
                                >
                                    {activeTemplateTab === 'backgrounds' ? (
                                        backgroundTemplates.length > 0 ? backgroundTemplates.map((img, i) => (
                                            <TouchableOpacity key={i} onPress={() => setCardBackgroundUrl(img.url)}>
                                                <Image source={{ uri: img.url }} style={{ width: 80, height: 80, borderRadius: 8, borderWidth: 1, borderColor: selectedBackground === img.url ? '#FFD700' : '#555', backgroundColor: '#000' }} />
                                            </TouchableOpacity>
                                        )) : <Text style={{ color: 'gray', fontSize: 12 }}>No hay fondos disponibles</Text>
                                    ) : activeTemplateTab === 'borders' ? (
                                        borderTemplates.length > 0 ? borderTemplates.map((img, i) => (
                                            <TouchableOpacity key={i} onPress={() => toggleBorder(img.url)}>
                                                <View style={{ width: 80, height: 80, borderRadius: 8, borderWidth: 1, borderColor: selectedBorder === img.url ? '#FFD700' : '#555', backgroundColor: '#333', overflow: 'hidden' }}>
                                                    {/* Checkerboard Pattern */}
                                                    <View style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0, opacity: 0.1, flexDirection: 'row', flexWrap: 'wrap' }}>
                                                        {Array(16).fill(0).map((_, idx) => (
                                                            <View key={idx} style={{ width: 20, height: 20, backgroundColor: idx % 2 === 0 ? '#FFF' : 'transparent' }} />
                                                        ))}
                                                    </View>
                                                    <Image source={{ uri: img.url }} style={{ width: 80, height: 80 }} resizeMode="cover" />
                                                </View>
                                            </TouchableOpacity>
                                        )) : <Text style={{ color: 'gray', fontSize: 12 }}>No hay marcos disponibles</Text>
                                    ) : (
                                        stickerTemplates.length > 0 ? stickerTemplates.map((img, i) => (
                                            <TouchableOpacity key={i} onPress={() => toggleSticker(img.url)}>
                                                <View style={{ width: 80, height: 80, borderRadius: 8, borderWidth: 1, borderColor: selectedStickers.includes(img.url) ? '#FFD700' : '#555', backgroundColor: 'rgba(255,255,255,0.05)', justifyContent: 'center', alignItems: 'center', overflow: 'hidden' }}>
                                                    {/* Checkerboard Pattern */}
                                                    <View style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0, opacity: 0.1, flexDirection: 'row', flexWrap: 'wrap' }}>
                                                        {Array(16).fill(0).map((_, idx) => (
                                                            <View key={idx} style={{ width: 20, height: 20, backgroundColor: idx % 2 === 0 ? '#FFF' : 'transparent' }} />
                                                        ))}
                                                    </View>
                                                    <Image source={{ uri: img.url }} style={{ width: 75, height: 75 }} resizeMode="contain" />
                                                    {selectedStickers.includes(img.url) ? (
                                                        <View style={{ position: 'absolute', top: 5, right: 5, backgroundColor: '#FFD700', borderRadius: 10 }}>
                                                            <Ionicons name="checkmark-circle" size={16} color="#000" />
                                                        </View>
                                                    ) : (selectedStickers.length >= 3) && (
                                                        <View style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(255,0,0,0.15)', justifyContent: 'center', alignItems: 'center' }}>
                                                            <Ionicons name="close-circle" size={24} color="rgba(255,255,255,0.7)" />
                                                        </View>
                                                    )}
                                                </View>
                                            </TouchableOpacity>
                                        )) : <Text style={{ color: 'gray', fontSize: 12 }}>No hay stickers disponibles.</Text>
                                    )}
                                </ScrollView>
                            </View>
                        )}

                        {/* Separador Visual entre Editor y Formulario */}
                        {(currentStep === 3 || SHOW_DEBUG_GENERATOR) && (
                            <View style={{ height: 1.5, backgroundColor: 'rgba(255, 215, 0, 0.15)', marginVertical: 25, width: '90%', alignSelf: 'center' }} />
                        )}

                        {/* Step 1: Personal Data */}
                        {currentStep === 1 && (
                            <View style={{ marginTop: 10 }}>
                                <FormSection title="IDENTIDAD" icon="person" onLayout={handleSectionLayout('datos_personales')}>
                                    {SHOW_DEBUG_GENERATOR && (
                                        <TouchableOpacity
                                            style={{
                                                alignSelf: 'flex-end',
                                                backgroundColor: 'rgba(255, 215, 0, 0.1)',
                                                padding: 8,
                                                borderRadius: 8,
                                                marginBottom: 10,
                                                flexDirection: 'row',
                                                gap: 6,
                                                borderWidth: 1,
                                                borderColor: 'rgba(255, 215, 0, 0.3)'
                                            }}
                                            onPress={fillDebugData}
                                        >
                                            <Ionicons name="flash" size={16} color="#FFD700" />
                                            <Text style={{ color: '#FFD700', fontSize: 12, fontWeight: 'bold' }}>GENERAR DATOS</Text>
                                        </TouchableOpacity>
                                    )}
                                    <FormInput label="Nombres" value={formData.nombre} onChangeText={(v) => updateField('nombre', v)} placeholder="Ej. Juan Carlos" icon="person" error={getFieldError('nombre')} successMessage={getFieldSuccess('nombre')} isValid={!!getFieldSuccess('nombre')} onFocus={() => setFocusedField('nombre')} onBlur={() => handleBlurField('nombre')} focused={focusedField === 'nombre'} onLayout={handleFieldLayout('nombre')} />
                                    <FormInput label="Apellidos" value={formData.apellidos} onChangeText={(v) => updateField('apellidos', v)} placeholder="Ej. Pérez López" icon="person-outline" error={getFieldError('apellidos')} successMessage={getFieldSuccess('apellidos')} isValid={!!getFieldSuccess('apellidos')} onFocus={() => setFocusedField('apellidos')} onBlur={() => handleBlurField('apellidos')} focused={focusedField === 'apellidos'} />
                                    <FormInput label="DNI / Documento" value={formData.dni} onChangeText={(v) => updateField('dni', v)} placeholder="8 dígitos" keyboardType="numeric" icon="card" maxLength={8} error={getFieldError('dni')} successMessage={getFieldSuccess('dni')} isValid={!!getFieldSuccess('dni')} onFocus={() => setFocusedField('dni')} onBlur={() => handleBlurField('dni')} focused={focusedField === 'dni'} />
                                </FormSection>

                                <FormSection title="CONTACTO" icon="call" onLayout={handleSectionLayout('contacto')}>
                                    <FormInput label="Email" value={formData.email} onChangeText={(v) => updateField('email', v)} placeholder="correo@ejemplo.com" keyboardType="email-address" icon="mail" autoCapitalize="none" error={getFieldError('email')} successMessage={getFieldSuccess('email')} isValid={!!getFieldSuccess('email')} onFocus={() => setFocusedField('email')} onBlur={() => handleBlurField('email')} focused={focusedField === 'email'} />
                                    <PhoneInput label="Teléfono" value={formData.telefono} onChangeText={(v) => updateField('telefono', v)} countryCode={formData.countryCode} onCountryChange={(c) => updateField('countryCode', c)} error={getFieldError('telefono')} successMessage={getFieldSuccess('telefono')} isValid={!!getFieldSuccess('telefono')} onFocus={() => setFocusedField('telefono')} onBlur={() => handleBlurField('telefono')} focused={focusedField === 'telefono'} />
                                </FormSection>
                            </View>
                        )}

                        {/* Step 2: Athletic Profile */}
                        {currentStep === 2 && (
                            <>
                                <FormSection title="CARACTERÍSTICAS FÍSICAS" icon="fitness" onLayout={handleSectionLayout('caracteristicas')}>
                                    <View onLayout={handleFieldLayout('apodo')}>
                                        <FormInput label="Apodo (opcional)" value={formData.apodo} onChangeText={(v) => updateField('apodo', v)} placeholder="Ej. 'El Rayo'" icon="flash" error={getFieldError('apodo')} successMessage={getFieldSuccess('apodo')} isValid={!!getFieldSuccess('apodo')} onFocus={() => setFocusedField('apodo')} onBlur={() => handleBlurField('apodo')} focused={focusedField === 'apodo'} />
                                    </View>

                                    <View onLayout={handleFieldLayout('genero')}>
                                        <FormSelect label="Género" value={formData.genero} options={[{ label: 'Masculino', value: 'masculino' }, { label: 'Femenino', value: 'femenino' }]} icon="male-female" onValueChange={(v) => updateField('genero', v)} />
                                    </View>

                                    <View style={styles.row}>
                                        <View style={styles.halfWidth} onLayout={handleFieldLayout('edad')}>
                                            <FormInput label="Edad" value={formData.edad} onChangeText={(v) => updateField('edad', v)} placeholder="Ej. 24" keyboardType="numeric" maxLength={2} icon="calendar" error={getFieldError('edad')} successMessage={getFieldSuccess('edad')} isValid={!!getFieldSuccess('edad')} onFocus={() => setFocusedField('edad')} onBlur={() => handleBlurField('edad')} focused={focusedField === 'edad'} />
                                        </View>
                                        <View style={styles.halfWidth} onLayout={handleFieldLayout('peso')}>
                                            <FormInput label="Peso (kg)" value={formData.peso} onChangeText={(v) => updateField('peso', v)} placeholder="Ej. 75.5" keyboardType="numeric" maxLength={5} icon="fitness" error={getFieldError('peso')} successMessage={getFieldSuccess('peso')} isValid={!!getFieldSuccess('peso')} onFocus={() => setFocusedField('peso')} onBlur={() => handleBlurField('peso')} focused={focusedField === 'peso'} />
                                        </View>
                                    </View>

                                    <View onLayout={handleFieldLayout('altura')}>
                                        <FormInput label="Altura (cm)" value={formData.altura} onChangeText={(v) => updateField('altura', v)} placeholder="Ej. 178" keyboardType="numeric" maxLength={3} icon="resize" error={getFieldError('altura')} successMessage={getFieldSuccess('altura')} isValid={!!getFieldSuccess('altura')} onFocus={() => setFocusedField('altura')} onBlur={() => handleBlurField('altura')} focused={focusedField === 'altura'} />
                                    </View>
                                    {/* Club removed from Step 2, moving to Step 3 */}
                                </FormSection>
                            </>
                        )}

                        {/* Step 3: Club & Photo */}
                        {currentStep === 3 && (
                            <>
                                <FormSection title="FOTO DE PERFIL (OPCIONAL)" icon="camera" onLayout={handleSectionLayout('foto')}>
                                    <View style={{ alignItems: 'center', marginVertical: 10 }}>
                                        <View style={[styles.photoWrapper, photo && styles.photoWrapperActive]}>
                                            {photo ? (
                                                <Image source={{ uri: photo.uri }} style={styles.photoPreview} />
                                            ) : (
                                                <View style={styles.photoPlaceholder}>
                                                    <Ionicons name="person" size={50} color={COLORS.text.tertiary} />
                                                </View>
                                            )}

                                            {/* Expandable Button Logic */}
                                            <View style={{
                                                position: 'absolute',
                                                bottom: -10,
                                                flexDirection: 'row',
                                                gap: 15,
                                                zIndex: 10
                                            }}>
                                                {showProfilePhotoOptions ? (
                                                    <>
                                                        <TouchableOpacity
                                                            onPress={() => { launchCamera('profile'); setShowProfilePhotoOptions(false); }}
                                                            style={styles.actionBtnCircle}
                                                        >
                                                            <Ionicons name="camera" size={20} color="#FFF" />
                                                        </TouchableOpacity>

                                                        <TouchableOpacity
                                                            onPress={() => { launchGallery('profile'); setShowProfilePhotoOptions(false); }}
                                                            style={styles.actionBtnCircle}
                                                        >
                                                            <Ionicons name="images" size={20} color="#FFF" />
                                                        </TouchableOpacity>
                                                    </>
                                                ) : (
                                                    <TouchableOpacity
                                                        onPress={() => setShowProfilePhotoOptions(true)}
                                                        style={[styles.actionBtnCircle, { backgroundColor: COLORS.primary }]}
                                                    >
                                                        <Ionicons name="camera" size={22} color="#000" />
                                                    </TouchableOpacity>
                                                )}
                                            </View>
                                        </View>

                                        {!photo && (
                                            <Text style={[styles.photoHintText, { marginTop: 25 }]}>
                                                Sube tu foto de perfil
                                            </Text>
                                        )}

                                        {photo && (
                                            <TouchableOpacity onPress={clearProfilePhoto} style={{ marginTop: 20 }}>
                                                <Text style={{ color: COLORS.error, fontSize: 13, fontWeight: '600' }}>ELIMINAR FOTO</Text>
                                            </TouchableOpacity>
                                        )}
                                    </View>
                                </FormSection>

                                <FormSection title="TU CLUB / GIMNASIO" icon="business" onLayout={handleSectionLayout('club')}>
                                    {loadingClubs ? (
                                        <ActivityIndicator color={COLORS.primary} size="small" />
                                    ) : (
                                        <ClubSelector label="Club Representante" options={clubs} value={formData.club_id} onValueChange={(id) => updateField('club_id', id)} error={getFieldError('club_id')} />
                                    )}
                                </FormSection>
                            </>
                        )}

                        {/* Web Card Background Logic (Hidden/Optional or integrated?) - Keeping purely as fallback or specialized web feature if needed, but primary Step 3 is now Club+Photo as requested */}
                        {/* Web Card Background Logic - Removed Legacy cardPhoto check */}



                        {/* Navigation Buttons */}
                        <View style={styles.navigationButtons}>
                            {currentStep > 1 && (
                                <TouchableOpacity style={styles.backButton} onPress={handleBack}>
                                    <Text style={styles.backButtonText}>ANTERIOR</Text>
                                </TouchableOpacity>
                            )}
                            <TouchableOpacity
                                style={[styles.nextButton, isSubmitting && { opacity: 0.7 }]}
                                onPress={currentStep === totalSteps ? handleSubmit : handleNext}
                                disabled={isSubmitting}
                            >
                                {isSubmitting ? (
                                    <ActivityIndicator color="#000" />
                                ) : (
                                    <Text style={styles.nextButtonText}>{currentStep === totalSteps ? 'FINALIZAR REGISTRO' : 'SIGUIENTE'}</Text>
                                )}
                            </TouchableOpacity>
                        </View>

                        <SponsorFooter />
                    </View>
                </KeyboardAwareScrollView>
            )
            }

            {/* Modals */}
            <EpicFighterSuccessModal
                visible={showSuccessModal}
                onClose={handleCloseSuccessModal}
                fighterData={successData ? {
                    nombre: successData.nombre, apellidos: successData.apellidos, apodo: successData.apodo,
                    peso: successData.peso, genero: successData.genero, photoUri: successData.photoUri || photo?.uri,
                    edad: successData.edad, altura: successData.altura, clubName: successData.clubName || clubs.find(c => c.id === formData.club_id)?.nombre
                } : {
                    nombre: formData.nombre, apellidos: formData.apellidos, apodo: formData.apodo,
                    peso: formData.peso, genero: formData.genero, photoUri: photo?.uri,
                    edad: formData.edad, altura: formData.altura, clubName: clubs.find(c => c.id === formData.club_id)?.nombre
                }}
                email={successData?.email || formData.email}
                dni={successData?.dni || formData.dni}
                isAutoLoggedIn={isAutoLoggedIn}
                backgroundOffsetY={currentOffsetY}
                backgroundOffsetX={currentOffsetX}
                backgroundScale={currentScale}
                fighterLayers={fighterLayers}
            />

            <FighterIdentityModal
                visible={showIdentityModal}
                onClose={() => setShowIdentityModal(false)}
                fighter={existingFighter}
                onEdit={() => {
                    setShowIdentityModal(false);
                    // If auto-logged in or already auth, go to profile
                    (navigation as any).navigate(isAutoLoggedIn ? 'Profile' : 'Login');
                }}
            />

            {/* Image Editor (Universal) */}
            <ImageCropper
                visible={isImageCropperVisible}
                imageUri={pendingImageUri}
                onClose={() => setIsImageCropperVisible(false)}
                onCrop={handleImageCropConfirm}
                onChangePhoto={() => {
                    if (lastImageSource === 'camera') launchCamera();
                    else if (lastImageSource === 'gallery') launchGallery();
                    else setShowImageOptions(true);
                }}
            />
            <FighterImageUploadModal
                visible={showImageOptions}
                onClose={() => setShowImageOptions(false)}
                onCamera={launchCamera}
                onGallery={launchGallery}
                mode={imageUploadMode}
            />
        </SafeAreaView >
    );
};

export default FighterFormScreen;
