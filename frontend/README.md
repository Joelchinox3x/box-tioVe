# Evento Box - Frontend 2.0

Aplicación móvil React Native con Expo para gestión de eventos de boxeo.

## 🚀 Stack Tecnológico

- **React Native** 0.81.x
- **Expo SDK** 54.x
- **TypeScript** 5.x
- **React Navigation** 7.x
- **Axios** para peticiones HTTP

## 📁 Estructura del Proyecto

```
frontend2/
├── src/
│   ├── components/         # Componentes reutilizables
│   ├── screens/            # Pantallas de la aplicación
│   │   ├── HomeScreen.tsx
│   │   ├── FightersScreen.tsx
│   │   ├── RegisterScreen.tsx
│   │   ├── FighterFormScreen.tsx
│   │   └── ProfileScreen.tsx
│   ├── navigation/         # Configuración de navegación
│   │   └── AppNavigator.tsx
│   ├── services/           # Servicios (API, storage, etc)
│   │   └── api.ts
│   ├── types/              # Definiciones TypeScript
│   │   └── index.ts
│   ├── constants/          # Constantes (theme, config)
│   │   └── theme.ts
│   ├── hooks/              # Custom hooks
│   └── utils/              # Utilidades y helpers
├── assets/                 # Imágenes, fuentes, etc
├── App.tsx                 # Componente principal
└── package.json
```

## 🎨 Tema y Diseño

- **Colores principales:**
  - Primary: `#FFD700` (Dorado)
  - Background: `#000000` (Negro)
  - Surface: `#1a1a1a` (Gris oscuro)

- **Tipografía:** Sistema nativo con pesos personalizados
- **Espaciado:** Sistema de spacing consistente (xs, sm, md, lg, xl, xxl)

## 📱 Pantallas Implementadas

1. **HomeScreen** - Pantalla principal con información del evento
2. **FightersScreen** - Lista de peleadores registrados
3. **RegisterScreen** - Compra de entradas (placeholder)
4. **FighterFormScreen** - Formulario de inscripción (placeholder)
5. **ProfileScreen** - Perfil de usuario (placeholder)

## 🔧 Configuración Inicial

### 1. Instalar dependencias

```bash
npm install
```

### 2. Configurar URL de API

Edita el archivo `src/services/api.ts`:

```typescript
const API_BASE_URL = 'http://TU_IP:8080/Box-TioVe/backend/public/index.php';
```

### 3. Ejecutar la aplicación

```bash
# Para desarrollo
npm start

# Para Android
npm run android

# Para iOS
npm run ios

# Para Web
npm run web
```

## 🌐 Endpoints de API

La aplicación consume los siguientes endpoints:

- `GET /eventos` - Obtener información del evento
- `GET /peleadores` - Obtener lista de peleadores
- `POST /peleadores/inscribir` - Inscribir nuevo peleador

## 📦 Dependencias Principales

```json
{
  "@react-navigation/native": "^7.x",
  "@react-navigation/bottom-tabs": "^7.x",
  "@react-navigation/native-stack": "^7.x",
  "axios": "^1.x",
  "expo": "~54.x",
  "expo-linear-gradient": "~15.x",
  "expo-haptics": "~15.x",
  "@expo/vector-icons": "latest"
}
```

## 🎯 Próximos Pasos

- [ ] Implementar formulario completo de inscripción de peleadores
- [ ] Agregar sistema de autenticación
- [ ] Implementar compra de entradas
- [ ] Añadir detalles de peleadores
- [ ] Agregar sistema de notificaciones
- [ ] Implementar modo offline
- [ ] Agregar animaciones y transiciones

## 🔑 Variables de Entorno

Crear archivo `.env` en la raíz:

```env

API_TIMEOUT=10000
```

## 🐛 Troubleshooting

### Error de conexión a API

Asegúrate de que:
1. El backend esté corriendo
2. La IP en `api.ts` sea correcta
3. El puerto 8080 esté abierto

### Error en navegación

```bash
npm install --legacy-peer-deps
```

### Clear cache

```bash
npm start -- --clear
```

## 📝 Notas

- Proyecto creado con `create-expo-app`
- TypeScript habilitado por defecto
- Sistema de tipos estricto configurado
- Estructura modular y escalable

## 👨‍💻 Desarrollo

Para agregar una nueva pantalla:

1. Crear archivo en `src/screens/NombrePantalla.tsx`
2. Agregar ruta en `src/navigation/AppNavigator.tsx`
3. Definir tipos necesarios en `src/types/index.ts`

## 📄 Licencia

Este proyecto es privado y confidencial.
