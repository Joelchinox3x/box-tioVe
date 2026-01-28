/**
 * ARCHIVO EXITOSO: test_db_connection.js
 * 
 * DESCRIPCIÓN:
 * Este script fue fundamental para aislar el problema de conectividad.
 * En lugar de intentar depurar todo el servidor MCP a la vez, este script prueba
 * ÚNICAMENTE la conexión a la base de datos MySQL usando la librería 'mysql2/promise'.
 * 
 * USO:
 * Ejecutar con: node test_db_connection.js
 * 
 * POR QUÉ FUNCIONÓ:
 * Nos permitió confirmar que:
 * 1. Las credenciales (usuario/pass) eran correctas.
 * 2. El host '127.0.0.1' era accesible.
 * 3. La base de datos 'eventobox_db' existía.
 * 
 * Si este script falla, el problema es de MySQL/Red. Si funciona, el problema está en la capa MCP.
 */

import mysql from "mysql2/promise";

async function test() {
    console.log("--- INICIANDO TEST DE CONEXIÓN A BASE DE DATOS ---");
    console.log("Intentando conectar a 127.0.0.1 como 'server_admin'...");

    try {
        // Creamos el pool de conexiones con la configuración exacta del proyecto
        const pool = mysql.createPool({
            host: "127.0.0.1",
            user: "server_admin",
            password: "Cocacola123",
            database: "eventobox_db", // Nombre verificado de la BD
            waitForConnections: true,
            connectionLimit: 10,
            queueLimit: 0
        });

        console.log("Pool creado correctamente. Intentando ejecutar consulta simple...");

        // Ejecutamos una consulta básica que no modifica datos, solo lee
        const [rows] = await pool.query("SHOW TABLES");

        console.log("¡ÉXITO! Conexión establecida y datos recibidos.");
        console.log("Tablas encontradas en la base de datos:", rows);

        // Salimos con código 0 (éxito)
        process.exit(0);

    } catch (e) {
        console.error("!!! FALLO LA CONEXIÓN !!!");
        console.error("Detalles del error:", e);

        // Salimos con código 1 (error) para que la terminal lo sepa
        process.exit(1);
    }
}

test();
