
import mysql from "mysql2/promise";
import axios from "axios";

const config = {
    host: "127.0.0.1",
    user: "server_admin",
    password: "Cocacola123",
    database: "proformamvc",
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

async function main() {
    try {
        console.log("1. Fetching Exchange Rate...");
        const response = await axios.get("https://api.exchangerate-api.com/v4/latest/USD");
        const rate = response.data.rates.PEN;
        console.log(`   Current Rate: 1 USD = ${rate} PEN`);

        console.log("\n2. Finding USD Products in Database...");
        const connection = await mysql.createConnection(config);

        // Query for USD products (assuming 'USD' or '$')
        const [products] = await connection.query("SELECT id, nombre, precio, moneda FROM productos WHERE moneda = 'USD' OR moneda = '$'");

        if (products.length === 0) {
            console.log("   No USD products found.");

            // Fallback: Just show first 2 products even if Soles, and pretend they are USD for the demo
            console.log("   (Simulating with first 2 products for demonstration)");
            const [demoProds] = await connection.query("SELECT id, nombre, precio, moneda FROM productos LIMIT 2");
            for (const p of demoProds) {
                const precioSoles = (p.precio * rate).toFixed(2);
                console.log(`   - ${p.nombre}: ${p.precio} ${p.moneda}  ---->  S/ ${precioSoles} (aprox)`);
            }

        } else {
            console.log(`   Found ${products.length} products:`);
            for (const p of products) {
                const precioSoles = (p.precio * rate).toFixed(2);
                console.log(`   - ${p.nombre}: $${p.precio}  ---->  S/ ${precioSoles}`);
            }
        }

        await connection.end();
    } catch (e) {
        console.error("Error:", e.message);
    }
}

main();
