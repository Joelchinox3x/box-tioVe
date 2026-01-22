# 📦 Documentación de Componentes - Evento Box

## 🏠 Componentes Home

Componentes profesionales creados para el nuevo diseño del HomeScreen, siguiendo el mockup de referencia.

---

### 1. **Header**
`/frontend/src/components/home/Header.tsx`

Header principal de la aplicación con logo, badge de evento en vivo y acciones de usuario.

#### Props:
```typescript
interface HeaderProps {
  eventTitle?: string;           // Título del evento (default: "Noche Corporativa")
  isLive?: boolean;              // Muestra badge "LIVE EVENT" (default: true)
  onNotificationPress?: () => void;  // Callback al presionar notificaciones
  onProfilePress?: () => void;   // Callback al presionar perfil
  userRole?: 'admin' | 'usuario' | 'peleador';  // Rol del usuario
}
```

#### Características:
- Logo con ícono de calendario
- Badge animado "LIVE EVENT" con punto rojo pulsante
- Botón de notificaciones con badge indicator
- Botón de perfil con indicador de rol (Admin)
- Diseño responsive y dark mode

---

### 2. **CategoryTabs**
`/frontend/src/components/home/CategoryTabs.tsx`

Pills horizontales para filtrar contenido por categorías.

#### Props:
```typescript
interface CategoryTabsProps {
  categories: Category[];        // Array de categorías
  selectedCategory: string;      // ID de categoría seleccionada
  onSelectCategory: (categoryId: string) => void;  // Callback al seleccionar
}

interface Category {
  id: string;
  label: string;
}
```

#### Características:
- Scroll horizontal suave
- Pills con bordes redondeados
- Estado seleccionado con fondo dorado
- Feedback visual al tocar
- Ejemplos: "Todos", "Peso Pesado", "Sector Tech"

---

### 3. **FightCard**
`/frontend/src/components/home/FightCard.tsx`

Tarjeta horizontal VS para mostrar peleas destacadas en carrusel.

#### Props:
```typescript
interface FightCardProps {
  fighter1: Fighter;             // Datos del peleador 1
  fighter2: Fighter;             // Datos del peleador 2
  onPress?: () => void;          // Callback al presionar la tarjeta
  featured?: boolean;            // Muestra badge "ESTELAR" (default: false)
}

interface Fighter {
  id?: number;
  nombre?: string;
  apodo?: string;
  empresa?: string;
  club_nombre?: string;
  foto_perfil?: string;
}
```

#### Características:
- Diseño VS con dos peleadores enfrentados
- Fotos circulares con borde dorado
- Badge "VS" central con gradiente
- Badge "⭐ ESTELAR" para peleas destacadas
- Efecto glow en el fondo
- Placeholders para fotos no disponibles
- Tamaño fijo: 320x200px ideal para carrusel

---

### 4. **FighterCarousel**
`/frontend/src/components/home/FighterCarousel.tsx`

Carrusel horizontal de peleadores recién inscritos.

#### Props:
```typescript
interface FighterCarouselProps {
  fighters: Fighter[];           // Array de peleadores
  title?: string;                // Título de la sección
  subtitle?: string;             // Subtítulo descriptivo
  onFighterPress?: (fighter: Fighter) => void;  // Callback al tocar peleador
}
```

#### Características:
- Cards verticales con foto de perfil
- Gradiente inferior para texto
- Muestra apodo/nombre y record (W-L)
- Scroll horizontal
- Placeholders con emoji 🥊
- Tamaño card: 140x180px

---

### 5. **ScheduledFights**
`/frontend/src/components/home/ScheduledFights.tsx`

Lista vertical de peleas confirmadas con diseño tipo card oscuro.

#### Props:
```typescript
interface ScheduledFightsProps {
  fights: Fight[];               // Array de peleas
  title?: string;                // Título de la sección
  subtitle?: string;             // Subtítulo
  onFightPress?: (fight: Fight) => void;  // Callback al presionar
  emptyMessage?: string;         // Mensaje cuando no hay peleas
}

interface Fight {
  id?: number;
  peleador1?: Fighter;
  peleador2?: Fighter;
  categoria?: string;
  rondas?: number;
  fecha_pelea?: string;
}
```

#### Características:
- Cards oscuros con gradiente
- Layout horizontal: Peleador1 - VS - Peleador2
- Muestra apodo, nombre y empresa
- Fotos circulares con borde dorado
- Badge VS central
- Mensaje de estado vacío personalizable

---

### 6. **GeneralTicketBanner**
`/frontend/src/components/home/GeneralTicketBanner.tsx`

Banner promocional para venta de entradas. Preparado para VIP en el futuro.

#### Props:
```typescript
interface GeneralTicketBannerProps {
  onPress?: () => void;          // Callback al presionar CTA
  title?: string;                // Título del banner
  subtitle?: string;             // Descripción
  buttonText?: string;           // Texto del botón
  isVIP?: boolean;               // Activa modo VIP dorado (default: false)
}
```

#### Características:
- Diseño con efectos de luz animados
- Gradiente según tipo (General: oscuro, VIP: dorado)
- Icono grande con gradiente
- Botón CTA con gradiente y flecha
- Efectos decorativos (dots)
- Preparado para futuro modo VIP
- Sombras y elevación profesional

---

## 🧭 Componentes de Navegación

### 7. **BottomNav**
`/frontend/src/components/navigation/BottomNav.tsx`

Navegación inferior personalizada con botón central destacado.

#### Props:
```typescript
interface BottomNavProps {
  items: NavItem[];              // Array de items de navegación
  activeItem: string;            // ID del item activo
  onItemPress: (itemId: string) => void;  // Callback al presionar
}

interface NavItem {
  id: string;
  label: string;
  icon: keyof typeof Ionicons.glyphMap;
  isCenter?: boolean;            // Marca el botón central
}
```

#### Características:
- 5 tabs: Inicio, Peleadores, Entradas (central), Perfil
- Botón central elevado con gradiente dorado
- Iconos outline/filled según estado
- Sombra superior sutil
- Ajuste automático iOS/Android
- Altura: 70px (Android), 85px (iOS)

---

## 📱 Nuevo HomeScreen

### Estructura del HomeScreen Rediseñado

```
HomeScreen
├── Header (logo, live event, notificaciones, perfil)
├── CategoryTabs (Todos, Peso Pesado, Sector Tech)
├── ScrollView
│   ├── Evento Estelar (carrusel de FightCards)
│   ├── Últimos Inscritos (FighterCarousel)
│   ├── Peleas Pactadas (ScheduledFights)
│   ├── GeneralTicketBanner
│   └── Botones de Acción
│       ├── QUIERO PELEAR → FighterForm
│       ├── VER TODOS LOS PELEADORES → Fighters
│       ├── CREAR CUENTA → RegisterUser
│       └── INICIA SESIÓN → Login
```

### Flujos de Navegación Implementados:

1. **Header → Profile**: Al tocar botón de perfil
2. **Header → Notificaciones**: Al tocar campana (TODO)
3. **CategoryTabs**: Filtra contenido por categoría
4. **FightCard**: Navega a detalle de pelea (TODO)
5. **FighterCarousel → Perfil de Peleador**: Al tocar foto (TODO)
6. **ScheduledFights → Detalle de Pelea**: Al tocar card (TODO)
7. **GeneralTicketBanner → RegisterScreen**: Comprar entradas
8. **Botones de acción**: Navegación completa a todas las pantallas

---

## 🎨 Estilos y Constantes Usadas

Todos los componentes usan las constantes de tema:

```typescript
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../constants/theme';
```

### Paleta de Colores Principal:
- **Primary**: `#FFD700` (Dorado)
- **Background**: `#000000` (Negro)
- **Surface**: `#1a1a1a` (Gris oscuro)
- **Border**: `#333333` (Gris medio)

### Gradientes Usados:
- **Dorado**: `[COLORS.primary, '#FFA500']`
- **Oscuro**: `['#1a1a1a', '#0a0a0a']`
- **VIP Futuro**: `['#FFD700', '#FFA500', '#FF8C00']`

---

## 📂 Estructura de Archivos Creada

```
/frontend/src/
├── components/
│   ├── home/
│   │   ├── Header.tsx
│   │   ├── CategoryTabs.tsx
│   │   ├── FightCard.tsx
│   │   ├── FighterCarousel.tsx
│   │   ├── ScheduledFights.tsx
│   │   ├── GeneralTicketBanner.tsx
│   │   └── index.ts (exports)
│   └── navigation/
│       └── BottomNav.tsx
├── screens/
│   └── HomeScreen.tsx (REDISEÑADO)
└── navigation/
    └── AppNavigator.tsx (ACTUALIZADO)
```

---

## ✅ Checklist de Implementación

- [x] Componente Header con live badge
- [x] Componente CategoryTabs con filtros
- [x] Componente FightCard para carrusel VS
- [x] Componente FighterCarousel para últimos inscritos
- [x] Componente ScheduledFights para peleas pactadas
- [x] Componente GeneralTicketBanner (base para VIP)
- [x] Componente BottomNav personalizado
- [x] HomeScreen rediseñado e integrado
- [x] AppNavigator actualizado con gradiente
- [x] Todos los botones existentes preservados
- [x] Navegación funcional a todas las pantallas

---

## 🔮 Mejoras Futuras Sugeridas

1. **Sistema de Imágenes**
   - Implementar upload de fotos de peleadores
   - CDN para servir imágenes
   - Compresión y optimización automática

2. **Filtros por Categoría**
   - Implementar lógica de filtrado real
   - Agregar más categorías (peso, sector, nivel)
   - Búsqueda en tiempo real

3. **Notificaciones**
   - Sistema de notificaciones push
   - Badge con conteo de pendientes
   - Pantalla de historial de notificaciones

4. **Detalle de Peleas**
   - Pantalla dedicada con stats completos
   - Historial de enfrentamientos
   - Predicciones y votaciones

5. **Modo VIP**
   - Activar banner VIP dorado
   - Beneficios exclusivos
   - Acceso a zonas premium

6. **Animaciones**
   - Transiciones suaves entre pantallas
   - Animación del badge LIVE
   - Efectos parallax en carruseles

7. **Skeleton Loaders**
   - Estados de carga más profesionales
   - Placeholders animados
   - Mejora de UX durante fetching

---

## 🚀 Cómo Usar los Componentes

### Ejemplo: Integrar FightCard

```typescript
import { FightCard } from '../components/home';

<FightCard
  fighter1={{
    nombre: "Carlos",
    apodo: "CEO",
    empresa: "TechGlobal",
    foto_perfil: "https://...",
  }}
  fighter2={{
    nombre: "Maria",
    apodo: "Shark",
    empresa: "FinCorp",
    foto_perfil: "https://...",
  }}
  featured={true}
  onPress={() => navigation.navigate('FightDetail', { id: 1 })}
/>
```

### Ejemplo: Usar CategoryTabs

```typescript
import { CategoryTabs } from '../components/home';

const [selectedCategory, setSelectedCategory] = useState('todos');

const categories = [
  { id: 'todos', label: 'Todos' },
  { id: 'peso_pesado', label: 'Peso Pesado' },
  { id: 'sector_tech', label: 'Sector Tech' },
];

<CategoryTabs
  categories={categories}
  selectedCategory={selectedCategory}
  onSelectCategory={setSelectedCategory}
/>
```

---

## 💡 Notas Técnicas

1. **TypeScript**: Todos los componentes están completamente tipados
2. **React Native**: Compatible con iOS y Android
3. **Expo**: Usa LinearGradient de expo-linear-gradient
4. **Rendimiento**: Optimizado con ScrollView horizontal
5. **Responsive**: Ajustes automáticos por plataforma
6. **Dark Mode**: Diseño completamente oscuro por defecto
7. **Accesibilidad**: activeOpacity para feedback táctil

---

Hecho con ❤️ por Claude Code
Proyecto: Evento Box - Plataforma de Eventos de Box Corporativo
