// Tipos principales de la aplicación

export interface Fighter {
  id: number;
  nombre: string;
  apellido: string;
  apodo?: string;
  peso: number;
  altura: number;
  edad: number;
  email: string;
  telefono: string;
  dni: string;
  categoria?: string;
  foto_perfil?: string;
  victorias?: number;
  derrotas?: number;
  empates?: number;
  created_at?: string;
}

export interface Event {
  id: number;
  nombre: string;
  descripcion?: string;
  fecha: string;
  hora: string;
  direccion: string;
  ciudad?: string;
  estado: 'proximamente' | 'en_curso' | 'finalizado' | 'cancelado';
  imagen_banner?: string;
}

export interface FightMatch {
  id: number;
  evento_id: number;
  peleador1_id: number;
  peleador2_id: number;
  peleador1?: Fighter;
  peleador2?: Fighter;
  categoria: string;
  rondas: number;
  estado: 'programada' | 'en_curso' | 'finalizada' | 'cancelada';
  ganador_id?: number;
  fecha_pelea?: string;
}

export interface User {
  id: number;
  nombre: string;
  email: string;
  telefono?: string;
  tipo: 'admin' | 'usuario' | 'peleador';
  foto?: string;
}

export interface Ticket {
  id: number;
  evento_id: number;
  usuario_id: number;
  tipo: 'general' | 'vip' | 'premium';
  precio: number;
  codigo_qr?: string;
  estado: 'activo' | 'usado' | 'cancelado';
}

// Tipos de respuesta de la API
export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  error?: string;
}

export interface EventData {
  evento: Event | null;
  peleadores_destacados: Fighter[];
  peleas_pactadas: FightMatch[];
}

// Tipos de Boletos
export interface TipoBoleto {
  id: number;
  evento_id: number;
  nombre: string;
  precio: number;
  cantidad_total: number;
  cantidad_vendida: number;
  cantidad_disponible: number;
  color_hex: string;
  descripcion?: string;
  orden: number;
  activo: boolean;
}

export interface BoletoVendido {
  id: number;
  evento_id: number;
  tipo_boleto_id: number;
  vendedor_id?: number;
  comprador_nombres_apellidos: string;
  comprador_telefono: string;
  comprador_dni: string;
  cantidad: number;
  precio_total: number;
  codigo_qr: string;
  metodo_pago: 'yape' | 'transferencia' | 'efectivo';
  comprobante_pago?: string;
  estado_pago: 'pendiente' | 'verificado' | 'rechazado';
  estado_boleto: 'activo' | 'usado' | 'cancelado';
  fecha_compra: string;
  fecha_validacion?: string;
  fecha_uso?: string;
  observaciones?: string;
  // Datos adicionales del JOIN
  tipo_boleto_nombre?: string;
  color_hex?: string;
  evento_nombre?: string;
  evento_fecha?: string;
  evento_hora?: string;
  evento_direccion?: string;
}

export interface ComprarBoletoRequest {
  tipo_boleto_id: number;
  comprador_nombres_apellidos: string;
  comprador_telefono: string;
  comprador_dni: string;
  cantidad: number;
  metodo_pago: 'yape' | 'transferencia' | 'efectivo';
  comprobante_pago?: string;
  vendedor_id?: number;
}

export interface ReporteBoletos {
  resumen_por_tipo: ResumenPorTipo[];
  totales: TotalesReporte;
}

export interface ResumenPorTipo {
  tipo_boleto: string;
  precio: number;
  total_vendidos: number;
  cantidad_boletos: number;
  ingresos_total: number;
  pendientes: number;
  verificados: number;
  rechazados: number;
}

export interface TotalesReporte {
  total_ventas: number;
  total_boletos: number;
  ingresos_totales: number;
}
