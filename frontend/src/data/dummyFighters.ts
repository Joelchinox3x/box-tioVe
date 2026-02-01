export const getRandomElement = (arr: any[]) => arr[Math.floor(Math.random() * arr.length)];

export const getRandomNumber = (min: number, max: number) => Math.floor(Math.random() * (max - min + 1)) + min;

export const generateDNI = () => {
    // Generar 8 números exactos (Formato Perú)
    const numbers = Math.floor(Math.random() * 90000000) + 10000000;
    return `${numbers}`;
};

const nombresMasculinos = [
    "Alejandro", "Mateo", "Lucas", "Daniel", "Javier", "Piero", "Carlos", "David", "Sergio", "Jorge",
    "Adrián", "Enrique", "Gabriel", "Samuel", "Álvaro", "Miguel", "Pablo", "Rubén", "Diego", "Mario"
];

const nombresFemeninos = [
    "Sofia", "Camila", "Lucia", "Martina", "Valentina", "Antonella", "Valeria", "Mariana", "Isabella", "Fernanda",
    "Jimena", "Paula", "Elena", "Andrea", "Renata", "Flavia", "Paola", "Ximena", "Lorena", "Claudia"
];

const apellidos = [
    "García", "Rodríguez", "González", "Fernández", "López", "Martínez", "Sánchez", "Pérez", "Gómez", "Martin",
    "Jiménez", "Ruiz", "Hernández", "Díaz", "Moreno", "Muñoz", "Álvarez", "Romero", "Alonso", "Gutiérrez"
];

const apodosMasculinos = ["La Roca", "El Toro", "Dinamita", "El Rayo", "Cobra", "Martillo", "Titán", "Viper", "El Muro", "Furia"];
const apodosFemeninos = ["La Diabla", "La Fiera", "Diamante", "Pantera", "Avispa", "Guerrera", "Amazona", "Estrella", "Tormenta", "Valquiria"];

export const generateDebugFighter = () => {
    const isFemale = Math.random() > 0.5;
    const genero = isFemale ? 'femenino' : 'masculino';

    const nombresPool = isFemale ? nombresFemeninos : nombresMasculinos;
    const apodosPool = isFemale ? apodosFemeninos : apodosMasculinos;

    const primerNombre = getRandomElement(nombresPool);
    const segundoNombre = getRandomElement(nombresPool);
    const apellido = getRandomElement(apellidos);
    const segundoApellido = getRandomElement(apellidos);

    // Generar email basado en nombre
    const email = `${primerNombre.toLowerCase()}.${apellido.toLowerCase()}${getRandomNumber(1, 99)}@example.com`;

    return {
        nombre: `${primerNombre} ${segundoNombre}`, // Dos nombres
        apellidos: `${apellido} ${segundoApellido}`, // Dos apellidos
        apodo: getRandomElement(apodosPool),
        dni: generateDNI(),
        email: email,
        edad: getRandomNumber(18, 45).toString(),
        peso: isFemale ? getRandomNumber(48, 80).toString() : getRandomNumber(55, 110).toString(),
        altura: isFemale ? getRandomNumber(150, 180).toString() : getRandomNumber(160, 200).toString(),
        // Celular Perú: 9 dígitos, empieza con 9
        telefono: `9${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}${getRandomNumber(0, 9)}`,
        genero: genero,
        countryCode: 'PE',
        club_id: ''
    };
};
