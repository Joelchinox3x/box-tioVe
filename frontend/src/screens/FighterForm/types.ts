export interface FormData {
    nombre: string;
    apellidos: string;
    apodo: string;
    edad: string;
    peso: string;
    altura: string;
    genero: string;
    email: string;
    telefono: string;
    countryCode: string;
    dni: string;
    club_id: string | number;
}

export interface FormErrors {
    [key: string]: string;
}
