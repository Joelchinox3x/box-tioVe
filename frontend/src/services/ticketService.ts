/**
 * Servicio para gestión de boletos
 */

import api from './api';

export interface TipoBoleto {
    id: number;
    evento_id: number;
    evento_nombre: string;
    tipo_nombre: string;
    precio: number;
    cantidad_total: number;
    cantidad_vendida: number;
    cantidad_disponible: number;
    color_hex: string;
    descripcion: string;
    activo: number;
}

export interface SolicitudCompra {
    evento_id: number;
    tipo_boleto_id: number;
    nombres_apellidos: string;
    telefono: string;
    dni: string;
    cantidad: number;
    vendedor_id?: number;
    metodo_pago?: 'yape' | 'transferencia' | 'efectivo';
}

export interface BoletoVendido {
    id: number;
    codigo_qr: string;
    precio_total: number;
    estado_pago: 'pendiente' | 'verificado' | 'rechazado';
    estado_boleto: 'activo' | 'usado' | 'cancelado';
    comprador_nombres_apellidos: string;
    comprador_dni: string;
    fecha_compra: string;
}

export interface PagoPendiente {
    id: number;
    comprador_nombres_apellidos: string;
    comprador_telefono: string;
    comprador_dni: string;
    cantidad: number;
    precio_total: number;
    comprobante_pago: string | null;
    fecha_compra: string;
    tipo_boleto: string;
    evento_nombre: string;
}

export const ticketService = {
    /**
     * Obtener tipos de boleto disponibles para un evento
     */
    getTiposBoleto: async (eventoId: number): Promise<TipoBoleto[]> => {
        const response = await api.get(`/boletos/tipos-boleto/${eventoId}`);
        return response.data.data;
    },

    /**
     * Crear solicitud de compra de boleto
     */
    crearSolicitudCompra: async (data: SolicitudCompra): Promise<any> => {
        const response = await api.post('/boletos/comprar', data);
        return response.data;
    },

    /**
     * Subir comprobante de pago
     */
    subirComprobante: async (boletoId: number, file: File): Promise<any> => {
        const formData = new FormData();
        formData.append('comprobante', file);

        const response = await api.post(`/boletos/${boletoId}/comprobante`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        return response.data;
    },

    /**
     * Obtener pagos pendientes de validación (admin)
     */
    getPagosPendientes: async (): Promise<PagoPendiente[]> => {
        const response = await api.get('/boletos/pendientes');
        return response.data.data;
    },

    /**
     * Validar pago (aprobar o rechazar) - admin
     */
    validarPago: async (boletoId: number, accion: 'aprobar' | 'rechazar', observaciones?: string): Promise<any> => {
        const response = await api.put(`/boletos/${boletoId}/validar`, {
            accion,
            observaciones,
        });
        return response.data;
    },

    /**
     * Validar QR en la entrada del evento (scanner)
     */
    validarQR: async (codigoQR: string): Promise<any> => {
        const response = await api.post('/boletos/validar-qr', {
            codigo_qr: codigoQR,
        });
        return response.data;
    },
};
