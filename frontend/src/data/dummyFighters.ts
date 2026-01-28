export const getRandomElement = (arr: any[]) => arr[Math.floor(Math.random() * arr.length)];

export const getRandomNumber = (min: number, max: number) => Math.floor(Math.random() * (max - min + 1)) + min;

export const generateDNI = () => {
    // Generar 8 números exactos (Formato Perú)
    const numbers = Math.floor(Math.random() * 90000000) + 10000000;
    return `${numbers}`;
};

const nombres = [
    "Alejandro", "Mateo", "Lucas", "Daniel", "Javier", "Piero", "Carlos", "David", "Sergio", "Jorge",
    "Adrián", "Enrique", "Gabriel", "Samuel", "Álvaro", "Miguel", "Pablo", "Rubén", "Diego", "Mario"
];

const apellidos = [
    "García", "Rodríguez", "González", "Fernández", "López", "Martínez", "Sánchez", "Pérez", "Gómez", "Martin",
    "Jiménez", "Ruiz", "Hernández", "Díaz", "Moreno", "Muñoz", "Álvarez", "Romero", "Alonso", "Gutiérrez"
];

const apodos = ["La Roca", "El Toro", "Dinamita", "El Rayo", "Cobra", "Martillo", "Titán", "Viper", "El Muro", "Furia"];

export const generateDebugFighter = () => {
    const nombre = getRandomElement(nombres);
    const apellido = getRandomElement(apellidos);
    const segundoApellido = getRandomElement(apellidos);

    // Generar email basado en nombre
    const email = `${nombre.toLowerCase()}.${apellido.toLowerCase()}${getRandomNumber(1, 99)}@example.com`;

    return {
        nombre: `${nombre} ${getRandomElement(nombres)}`, // Dos nombres
        apellidos: `${apellido} ${segundoApellido}`, // Dos apellidos
        apodo: getRandomElement(apodos),
        dni: generateDNI(),
        email: email,
        edad: getRandomNumber(18, 45).toString(),
        peso: getRandomNumber(55, 110).toString(),
        altura: getRandomNumber(160, 200).toString(),
        // Celular Perú: 9 dígitos, empieza con 9
        telefono: `9${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}`,
        // Mantener valores por defecto del form para otros campos
        genero: 'masculino',
        countryCode: 'PE',
        club_id: '' // El usuario debe seleccionar el club o se mantiene si ya eligió
    };
};
