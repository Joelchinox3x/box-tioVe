import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import mysql from "mysql2/promise";
import axios from "axios";
import { z } from "zod";

// Create server instance
const server = new McpServer({
    name: "proforma-mysql",
    version: "1.0.0",
});

// Database Connection
const pool = mysql.createPool({
    host: "mi_mysql",
    user: "server_admin",
    password: "Cocacola123",
    database: "proformamvc",
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Tool: List Tables
server.tool(
    "list_tables",
    "List all tables in the database",
    {},
    async () => {
        try {
            const [rows] = await pool.query("SHOW TABLES");
            const tables = rows.map(r => Object.values(r)[0]);
            return {
                content: [{ type: "text", text: "Tables:\n" + tables.join("\n") }]
            };
        } catch (error) {
            return { content: [{ type: "text", text: "Error listing tables: " + error.message }], isError: true };
        }
    }
);

// Tool: Describe Table
server.tool(
    "describe_table",
    "Show schema of a specific table",
    { table: z.string() },
    async ({ table }) => {
        try {
            // Simple sanitization by escaping
            const [rows] = await pool.query(`DESCRIBE ${mysql.escapeId(table)}`);
            return {
                content: [{ type: "text", text: JSON.stringify(rows, null, 2) }]
            };
        } catch (e) {
            return { content: [{ type: "text", text: "Error describing table: " + e.message }], isError: true };
        }
    }
);

// Tool: Run SQL Query (Read-Only)
server.tool(
    "query_sql",
    "Execute a read-only SQL query (SELECT, SHOW, DESCRIBE)",
    { sql: z.string().describe("The SQL query to execute") },
    async ({ sql }) => {
        const cleanSql = sql.trim().toUpperCase();
        if (!cleanSql.startsWith("SELECT") && !cleanSql.startsWith("SHOW") && !cleanSql.startsWith("DESCRIBE")) {
            return {
                content: [{ type: "text", text: "Safety Error: Only READ-ONLY queries (SELECT, SHOW, DESCRIBE) are allowed." }],
                isError: true
            };
        }

        try {
            const [rows] = await pool.query(sql);
            return {
                content: [{ type: "text", text: JSON.stringify(rows, null, 2) }]
            };
        } catch (e) {
            return { content: [{ type: "text", text: "SQL Execution Error: " + e.message }], isError: true };
        }
    }
);

// --- NEW TOOLS ---

// Tool: Tailwind Search
server.tool(
    "search_tailwind",
    "Search for Tailwind CSS classes by description (e.g., 'red text', 'flex center')",
    { query: z.string().describe("Keywords to search for") },
    async ({ query }) => {
        // Simple mock data for demonstration if file doesn't exist yet, 
        // or we can implement the full dictionary later. 
        // For this step, I will include a basic dictionary directly here for reliability.
        const tailwindData = [
            { class: 'text-red-500', desc: 'Text color red 500' },
            { class: 'bg-blue-500', desc: 'Background color blue 500' },
            { class: 'flex', desc: 'Display flex' },
            { class: 'items-center', desc: 'Align items center' },
            { class: 'justify-center', desc: 'Justify content center' },
            { class: 'p-4', desc: 'Padding 4 (1rem)' },
            { class: 'm-4', desc: 'Margin 4 (1rem)' },
            { class: 'text-lg', desc: 'Font size large' },
            { class: 'font-bold', desc: 'Font weight bold' }
        ];

        const q = query.toLowerCase();
        const results = tailwindData.filter(item =>
            item.class.includes(q) || item.desc.toLowerCase().includes(q)
        );

        return {
            content: [{ type: "text", text: results.length > 0 ? JSON.stringify(results, null, 2) : "No matches found." }]
        };
    }
);

// Tool: Get Exchange Rate
server.tool(
    "get_exchange_rate",
    "Get live exchange rate for a currency (default base USD)",
    { base: z.string().optional().describe("Base currency (default USD)"), target: z.string().optional().describe("Target currency (e.g. PEN)") },
    async ({ base = "USD", target }) => {
        try {
            const response = await axios.get(`https://api.exchangerate-api.com/v4/latest/${base}`);
            const rates = response.data.rates;

            if (target) {
                const rate = rates[target.toUpperCase()];
                if (!rate) return { content: [{ type: "text", text: `Currency ${target} not found.` }], isError: true };
                return { content: [{ type: "text", text: `1 ${base} = ${rate} ${target.toUpperCase()}` }] };
            }

            return { content: [{ type: "text", text: JSON.stringify(rates, null, 2) }] };
        } catch (e) {
            return { content: [{ type: "text", text: "API Error: " + e.message }], isError: true };
        }
    }
);

// Tool: Search GitHub Repos
server.tool(
    "search_github_repos",
    "Search for GitHub repositories",
    { query: z.string().describe("Search keywords"), limit: z.number().optional().describe("Max results (default 5)") },
    async ({ query, limit = 5 }) => {
        try {
            const response = await axios.get(`https://api.github.com/search/repositories`, {
                params: { q: query, per_page: limit },
                headers: { 'User-Agent': 'McpServer' }
            });

            const repos = response.data.items.map(r => ({
                name: r.full_name,
                description: r.description,
                stars: r.stargazers_count,
                url: r.html_url
            }));

            return { content: [{ type: "text", text: JSON.stringify(repos, null, 2) }] };
        } catch (e) {
            return { content: [{ type: "text", text: "GitHub API Error: " + e.message }], isError: true };
        }
    }
);

// Start Server
async function main() {
    const transport = new StdioServerTransport();
    await server.connect(transport);
    console.error("Proforma MySQL MCP Server running on stdio");
}

main().catch((error) => {
    console.error("Fatal Server Error:", error);
    process.exit(1);
});
