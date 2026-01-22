# 🚀 Quick Start - Frontend2

## Inicio Rápido en 3 Pasos

### 1️⃣ Verifica las dependencias (Ya instaladas ✅)

```bash
npm list --depth=0
```

### 2️⃣ Configura la URL de tu API

Edita `src/services/api.ts` línea 4:

```typescript
const API_BASE_URL = 'http://TU_IP_AQUI:8080/Box-TioVe/backend/public/index.php';
```

**Ejemplo:**
```typescript
const API_BASE_URL = 'http://34.44.67.166:8080/Box-TioVe/backend/public/index.php';
```

### 3️⃣ Ejecuta la aplicación

```bash
npm start
```

Luego:
- Presiona `a` para Android
- Presiona `i` para iOS
- Presiona `w` para Web
- Escanea el QR con Expo Go en tu móvil

---

## 📱 Funcionalidades Implementadas

### ✅ Pantalla Principal (HomeScreen)
- Muestra información del evento
- Botón "COMPRAR ENTRADAS"
- Botón "QUIERO PELEAR" → Navega a formulario de inscripción
- Lista de peleadores destacados
- Peleas pactadas

### ✅ Pantalla de Peleadores (FightersScreen)
- Lista completa de peleadores registrados
- Información: nombre, apodo, peso, altura, edad, categoría

### ✅ Navegación Bottom Tab
- Tab bar personalizado con tema dorado/negro
- Botón central destacado para "Entradas"
- 4 secciones principales + 1 pantalla oculta

### 🚧 Pendientes (Placeholders listos)
- Formulario de inscripción completo
- Sistema de compra de entradas
- Perfil de usuario

---

## 🎨 Personalización del Tema

Edita `src/constants/theme.ts`:

```typescript
export const COLORS = {
  primary: '#FFD700',        // Cambia el dorado
  background: '#000000',     // Cambia el fondo
  // ... más colores
};
```

---

## 🔧 Comandos Útiles

```bash
# Ver logs en tiempo real
npm start

# Limpiar caché
npm start -- --clear

# Build para producción
npm run build

# Ejecutar en dispositivo específico
npm run android
npm run ios
npm run web
```

---

## 📁 Estructura Actual

```
src/
├── screens/           ← Pantallas completas
├── components/        ← Componentes reutilizables (vacío, listo para usar)
├── navigation/        ← Configuración de rutas
├── services/          ← API client configurado
├── types/             ← Tipos TypeScript completos
├── constants/         ← Tema y constantes
├── hooks/             ← Custom hooks (vacío, listo para usar)
└── utils/             ← Utilidades (vacío, listo para usar)
```

---

## 🐛 Errores Comunes

### "Network request failed"
- Verifica que la IP en `api.ts` sea correcta
- Asegúrate de que el backend esté corriendo
- Verifica que estés en la misma red

### "Unable to resolve module"
```bash
npm install
npm start -- --clear
```

### "Metro bundler error"
```bash
npm start -- --reset-cache
```

---

## ✨ Próximo Paso Recomendado

**Implementar el formulario completo de inscripción:**

1. Edita `src/screens/FighterFormScreen.tsx`
2. Copia la lógica de `frontend/src/screens/FighterFormScreen.tsx`
3. Adapta al nuevo sistema de tipos

---

## 📞 Soporte

Si encuentras algún problema:
1. Verifica los logs en la consola
2. Revisa la configuración de API
3. Limpia caché y reinstala dependencias

---

**¡Todo listo para empezar a desarrollar! 🎉**
