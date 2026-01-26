import api from './api';
import type {
  TipoBoleto,
  BoletoVendido,
  ComprarBoletoRequest,
  ReporteBoletos,
} from '../types';

export const boletosService = {
  /**
   * Obtener tipos de boletos disponibles para un evento
   */
  async getTiposDisponibles(eventoId: number): Promise<TipoBoleto[]> {
    const response = await api.get(`/boletos/tipos-boleto/${eventoId}`);
    return response.data.data || [];
  },

  /**
   * Comprar boleto
   */
  async comprarBoleto(data: ComprarBoletoRequest) {
    const response = await api.post('/boletos/comprar', {
      evento_id: data.tipo_boleto_id, // Se obtiene del tipo de boleto
      tipo_boleto_id: data.tipo_boleto_id,
      nombres_apellidos: data.comprador_nombres_apellidos,
      telefono: data.comprador_telefono,
      dni: data.comprador_dni,
      cantidad: data.cantidad,
      metodo_pago: data.metodo_pago,
      comprobante_pago: data.comprobante_pago,
      vendedor_id: data.vendedor_id,
    });
    return response.data;
  },

  /**
   * Obtener mis boletos por DNI
   */
  async getMisBoletos(dni: string): Promise<BoletoVendido[]> {
    const response = await api.get(`/boletos/mis-boletos/${dni}`);
    return response.data.boletos || [];
  },

  /**
   * Verificar boleto por código QR
   */
  async verificarBoleto(codigoQR: string) {
    const response = await api.get(`/boletos/verificar/${codigoQR}`);
    return response.data;
  },

  /**
   * Subir comprobante de pago
   */
  async subirComprobante(boletoId: number, archivo: File) {
    const formData = new FormData();
    formData.append('comprobante', archivo);

    const response = await api.post(`/boletos/${boletoId}/comprobante`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  // === ADMIN ENDPOINTS ===

  /**
   * Obtener boletos pendientes de validación (ADMIN)
   */
  async getBoletosPendientes(): Promise<BoletoVendido[]> {
    const response = await api.get('/boletos/pendientes');
    return response.data.data || response.data.boletos || [];
  },

  /**
   * Validar pago de boleto (ADMIN)
   */
  async validarPago(boletoId: number, accion: 'aprobar' | 'rechazar', observaciones?: string) {
    const response = await api.put(`/boletos/${boletoId}/validar`, {
      accion,
      observaciones,
    });
    return response.data;
  },

  /**
   * Marcar boleto como usado (ADMIN - escaneo en entrada)
   */
  async usarBoleto(codigoQR: string) {
    const response = await api.post('/boletos/validar-qr', {
      codigo_qr: codigoQR,
    });
    return response.data;
  },

  /**
   * Obtener reporte de ventas (ADMIN)
   */
  async getReporte(eventoId: number): Promise<ReporteBoletos> {
    const response = await api.get(`/boletos/reporte/${eventoId}`);
    return response.data.reporte;
  },

  // === TIPOS DE BOLETO (ADMIN) ===

  /**
   * Crear tipo de boleto (ADMIN)
   */
  async crearTipoBoleto(data: {
    evento_id: number;
    nombre: string;
    precio: number;
    cantidad_total: number;
    color_hex?: string;
    descripcion?: string;
    orden?: number;
  }) {
    const response = await api.post('/tipos-boleto/crear', data);
    return response.data;
  },

  /**
   * Editar tipo de boleto (ADMIN)
   */
  async editarTipoBoleto(id: number, data: Partial<TipoBoleto>) {
    const response = await api.put(`/tipos-boleto/editar/${id}`, data);
    return response.data;
  },

  /**
   * Desactivar tipo de boleto (ADMIN)
   */
  async desactivarTipoBoleto(id: number) {
    const response = await api.delete(`/tipos-boleto/${id}`);
    return response.data;
  },

  /**
   * Obtener todos los tipos de boleto de un evento (ADMIN)
   */
  async getTiposPorEvento(eventoId: number): Promise<TipoBoleto[]> {
    const response = await api.get(`/tipos-boleto/evento/${eventoId}`);
    return response.data.tipos_boleto || [];
  },
};

export default boletosService;
