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

const SHOW_DEBUG_GENERATOR = true;

import { generateDebugFighter } from '../../data/dummyFighters';

const FighterFormScreen = () => {
    // UI State for Template Lists
    const [activeTemplateTab, setActiveTemplateTab] = React.useState<'none' | 'backgrounds' | 'borders'>('none');

    const {
        navigation, scrollViewRef, handleFieldLayout, handleSectionLayout,
        formData, updateField, focusedField, setFocusedField, errors, isSubmitting, clubs, loadingClubs,
        isFieldValid, getFieldError, getFieldSuccess,
        currentStep, totalSteps, handleNext, handleBack,
        photo, cardPhoto, showImageOptions, setShowImageOptions, imageUploadMode,
        bgOffsetY, setBgOffsetY, bgOffsetX, setBgOffsetX, bgScale, setBgScale, bgFlipX, setBgFlipX, isRemovingBg, isLibReady,
        backgroundTemplates, borderTemplates, selectedBorder, setSelectedBorder, selectedBackground, setSelectedBackground,
        handleRemoveBackground, launchCamera, launchGallery, pickProfilePhoto, pickCardBackground, setCardBackgroundUrl, clearProfilePhoto,
        banners, currentBannerIndex,
        handleSubmit, showSuccessModal, successData, handleCloseSuccessModal,
        checkingAuth, existingFighter, showIdentityModal, setShowIdentityModal, isAutoLoggedIn, validateField, handleBlurField,
        fillDebugData, randomizeDesign
    } = useFighterForm();

    return (
        <SafeAreaView style={styles.container} edges={['top']}>
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
                        <View style={{ width: '100%', alignItems: 'center', overflow: 'visible', zIndex: 1, marginBottom: 20 }}>
                            <FighterCard
                                fighter={{
                                    nombre: formData.nombre,
                                    apellidos: formData.apellidos,
                                    apodo: formData.apodo,
                                    peso: formData.peso,
                                    genero: formData.genero,
                                    photoUri: cardPhoto?.uri || (banners.length > 0 ? banners[currentBannerIndex]?.url : undefined),
                                    clubName: clubs?.find(c => c.id === formData.club_id)?.nombre,
                                    edad: formData.edad,
                                    altura: formData.altura
                                }}
                                variant="preview"
                                onUploadBackground={currentStep === 3 || SHOW_DEBUG_GENERATOR ? pickCardBackground : undefined}
                                backgroundUri={selectedBackground}
                                backgroundOffsetY={bgOffsetY}
                                backgroundOffsetX={bgOffsetX}
                                backgroundScale={bgScale}
                                backgroundFlipX={bgFlipX}
                                borderUri={selectedBorder}
                            />
                        </View>

                        {/* Background Adjustment Controls (Step 3, Web & Mobile) */}
                        {(currentStep === 3 || SHOW_DEBUG_GENERATOR) && (
                            <ImageAdjustmentControls
                                isWeb={Platform.OS === 'web'}
                                showDebug={SHOW_DEBUG_GENERATOR}
                                hasPhoto={!!cardPhoto}
                                setBgOffsetX={setBgOffsetX}
                                setBgOffsetY={setBgOffsetY}
                                bgScale={bgScale}
                                setBgScale={setBgScale}
                                bgFlipX={bgFlipX}
                                setBgFlipX={setBgFlipX}
                                onRemoveBackground={handleRemoveBackground}
                                onOpenBackgrounds={() => setActiveTemplateTab(prev => prev === 'backgrounds' ? 'none' : 'backgrounds')}
                                onOpenBorders={() => setActiveTemplateTab(prev => prev === 'borders' ? 'none' : 'borders')}
                                onRandomize={randomizeDesign}
                                isRemovingBg={isRemovingBg}
                                isLibReady={isLibReady}
                            />
                        )}

                        {/* Template Galleries */}
                        {activeTemplateTab !== 'none' && (
                            <View style={{ marginVertical: 10 }}>
                                <Text style={{ color: '#FFD700', marginBottom: 8, fontWeight: 'bold', fontSize: 12 }}>
                                    {activeTemplateTab === 'backgrounds' ? 'SELECCIONA UN FONDO' : 'SELECCIONA UN MARCO'}
                                </Text>
                                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 10, paddingHorizontal: 5 }}>
                                    {activeTemplateTab === 'backgrounds' ? (
                                        backgroundTemplates.length > 0 ? backgroundTemplates.map((img, i) => (
                                            <TouchableOpacity key={i} onPress={() => setCardBackgroundUrl(img.url)}>
                                                <Image source={{ uri: img.url }} style={{ width: 80, height: 80, borderRadius: 8, borderWidth: 1, borderColor: '#555', backgroundColor: '#000' }} />
                                            </TouchableOpacity>
                                        )) : <Text style={{ color: 'gray', fontSize: 12 }}>No hay fondos disponibles</Text>
                                    ) : (
                                        borderTemplates.length > 0 ? borderTemplates.map((img, i) => (
                                            <TouchableOpacity key={i} onPress={() => setSelectedBorder(img.url)}>
                                                <Image source={{ uri: img.url }} style={{ width: 80, height: 80, borderRadius: 8, borderWidth: 1, borderColor: selectedBorder === img.url ? '#FFD700' : '#555', backgroundColor: 'transparent' }} resizeMode="contain" />
                                            </TouchableOpacity>
                                        )) : <Text style={{ color: 'gray', fontSize: 12 }}>No hay marcos disponibles</Text>
                                    )}
                                </ScrollView>
                            </View>
                        )}

                        {/* Step 1: Personal Data */}
                        {currentStep === 1 && (
                            <>
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
                            </>
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
                                        <TouchableOpacity onPress={pickProfilePhoto} style={[styles.photoWrapper, photo && styles.photoWrapperActive]}>
                                            {photo ? <Image source={{ uri: photo.uri }} style={styles.photoPreview} /> : <View style={styles.photoPlaceholder}><Ionicons name="person" size={40} color={COLORS.text.tertiary} /></View>}
                                            <View style={styles.cameraOverlay}><Ionicons name="camera" size={20} color="#FFF" /></View>
                                        </TouchableOpacity>
                                        {photo ? (
                                            <TouchableOpacity onPress={clearProfilePhoto} style={{ marginTop: 10 }}>
                                                <Text style={{ color: COLORS.error, fontSize: 14 }}>Eliminar foto</Text>
                                            </TouchableOpacity>
                                        ) : (
                                            <Text style={styles.photoHintText}>Toca el círculo para subir tu mejor foto de combate</Text>
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
                        {Platform.OS === 'web' && currentStep === 3 && cardPhoto && (
                            <View style={{ alignItems: 'center', marginBottom: 20 }}>
                                {/* ... web specific card bg logic ... */}
                            </View>
                        )}



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
            )}

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
                backgroundOffsetY={bgOffsetY}
                backgroundOffsetX={bgOffsetX}
                backgroundScale={bgScale}
            />

            <FighterImageUploadModal
                visible={showImageOptions}
                onClose={() => setShowImageOptions(false)}
                onCamera={launchCamera}
                onGallery={launchGallery}
                mode={imageUploadMode}
            />
        </SafeAreaView>
    );
};

export default FighterFormScreen;
