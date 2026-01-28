/**
 * ARCHIVO EXITOSO: mcp_programmatic_client.js
 * 
 * DESCRIPCIÓN:
 * Este fue el script que SOLUCIONÓ el problema de comunicación con el servidor MCP.
 * En lugar de escribir comandos JSON manualmente en la terminal (que falla por saltos de línea y buffers),
 * este script usa el SDK oficial de MCP para establecer una conexión "Cliente-Servidor" robusta.
 * 
 * USO:
 * 1. Asegúrate de estar en la carpeta 'mcp'.
 * 2. Ejecutar con: node mcp_programmatic_client.js
 * (Este script asume que 'server.js' está en la carpeta padre o ajusta la ruta en transport)
 * 
 * CÓMO FUNCIONA:
 * 1. Arranca el servidor MCP ('server.js') como un subproceso hijo.
 * 2. Se conecta a él usando 'StdioClientTransport'.
 * 3. Envía comandos 'callTool' de manera estructurada y espera la promesa (await).
 * 4. Imprime resultados limpios.
 */

import { Client } from "@modelcontextprotocol/sdk/client/index.js";
import { StdioClientTransport } from "@modelcontextprotocol/sdk/client/stdio.js";
import path from "path";

async function main() {
    console.log("--- INICIANDO CLIENTE MCP PROGRAMÁTICO ---");

    // Configuración del transporte: Esto arranca el servidor automáticamente
    // Ajusta la ruta a 'server.js' según donde estés ejecutando esto
    const serverPath = path.resolve("../server.js");
    console.log(`Conectando al servidor en: ${serverPath}`);

    const transport = new StdioClientTransport({
        command: "node",
        args: [serverPath]
    });

    // Inicializamos el Cliente MCP
    const client = new Client(
        {
            name: "mcp-cliente-exitoso",
            version: "1.0.0",
        },
        {
            capabilities: {}
        }
    );

    try {
        // Paso 1: Conexión
        await client.connect(transport);
        console.log("¡Conexión establecida con el Servidor MCP!");

        // Paso 2: Verificación (Listar Tablas)
        console.log("\n--- EJECUTANDO PRUEBA: LISTAR TABLAS ---");
        const tablesResult = await client.callTool({
            name: "list_tables",
            arguments: {}
        });
        console.log("Resultado:", tablesResult.content[0].text);

        // Paso 3: Ejemplo de Consulta SQL (Leer Clubes)
        console.log("\n--- EJECUTANDO PRUEBA: CONSULTA SQL ---");
        const clubsResult = await client.callTool({
            name: "query_sql",
            arguments: { sql: "SELECT * FROM clubs LIMIT 1" }
        });
        console.log("Resultado Club:", clubsResult.content[0].text);

        // AQUÍ FUE DONDE HICIMOS LA MIGRACIÓN
        /*
        const insertSql = `INSERT INTO banners ...`;
        const insertResult = await client.callTool({
             name: "query_sql", 
             arguments: { sql: insertSql } 
        });
        */

        console.log("\n--- FIN DE LA EJECUCIÓN EXITOSA ---");
        process.exit(0);

    } catch (error) {
        console.error("!!! ERROR EN CLIENTE MCP !!!", error);
        process.exit(1);
    }
}

main();
