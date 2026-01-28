
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
    CallToolRequestSchema,
    ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import mysql from "mysql2/promise";
import axios from "axios";

// Database Configuration
const dbConfig = {
    host: "mi_mysql",
    user: "server_admin",
    password: "Cocacola123",
    database: "eventobox_db",
};

// API Configuration
const API_BASE_URL = "http://localhost:8080/api";

const server = new Server(
    {
        name: "eventobox-mcp-server",
        version: "1.0.0",
    },
    {
        capabilities: {
            tools: {},
        },
    }
);

// Tools Definition
server.setRequestHandler(ListToolsRequestSchema, async () => {
    return {
        tools: [
            {
                name: "db_query",
                description: "Execute a SELECT query on the database",
                inputSchema: {
                    type: "object",
                    properties: {
                        query: { type: "string", description: "The SQL SELECT query" },
                    },
                    required: ["query"],
                },
            },
            {
                name: "db_list_tables",
                description: "List all tables in the database",
                inputSchema: {
                    type: "object",
                    properties: {},
                },
            },
            {
                name: "db_describe_table",
                description: "Describe the schema of a specific table",
                inputSchema: {
                    type: "object",
                    properties: {
                        tableName: { type: "string" },
                    },
                    required: ["tableName"],
                },
            },
            {
                name: "api_request",
                description: "Make an HTTP request to the internal API",
                inputSchema: {
                    type: "object",
                    properties: {
                        method: {
                            type: "string",
                            enum: ["GET", "POST", "PUT", "DELETE"],
                            default: "GET"
                        },
                        endpoint: { type: "string", description: "API endpoint (e.g., /usuarios)" },
                        data: { type: "object", description: "JSON body for POST/PUT" },
                    },
                    required: ["endpoint"],
                },
            },
            {
                name: "search_github_repos",
                description: "Search for GitHub repositories",
                inputSchema: {
                    type: "object",
                    properties: {
                        query: { type: "string", description: "Search keywords" },
                        limit: { type: "number", description: "Max results (default 5)" },
                    },
                    required: ["query"],
                },
            },
        ],
    };
});

// Tools Implementation
server.setRequestHandler(CallToolRequestSchema, async (request) => {
    const { name, arguments: args } = request.params;

    try {
        if (name === "db_query") {
            const conn = await mysql.createConnection(dbConfig);
            try {
                const [rows] = await conn.execute(args.query);
                return { content: [{ type: "text", text: JSON.stringify(rows, null, 2) }] };
            } finally {
                await conn.end();
            }
        }

        if (name === "db_list_tables") {
            const conn = await mysql.createConnection(dbConfig);
            try {
                const [rows] = await conn.execute("SHOW TABLES");
                const tables = rows.map(r => Object.values(r)[0]);
                return { content: [{ type: "text", text: JSON.stringify(tables, null, 2) }] };
            } finally {
                await conn.end();
            }
        }

        if (name === "db_describe_table") {
            const conn = await mysql.createConnection(dbConfig);
            try {
                const [rows] = await conn.execute(`DESCRIBE ${args.tableName}`);
                return { content: [{ type: "text", text: JSON.stringify(rows, null, 2) }] };
            } finally {
                await conn.end();
            }
        }

        if (name === "api_request") {
            const method = args.method || "GET";
            const url = `${API_BASE_URL}${args.endpoint.startsWith('/') ? '' : '/'}${args.endpoint}`;

            const response = await axios({
                method,
                url,
                data: args.data,
                validateStatus: () => true // Don't throw on error status
            });

            return {
                content: [{
                    type: "text",
                    text: JSON.stringify({
                        status: response.status,
                        data: response.data
                    }, null, 2)
                }]
            };
        }

        if (name === "search_github_repos") {
            const query = args.query;
            const limit = args.limit || 5;

            try {
                const response = await axios.get("https://api.github.com/search/repositories", {
                    params: { q: query, per_page: limit },
                    headers: { "User-Agent": "McpServer" },
                });

                const repos = response.data.items.map((r) => ({
                    name: r.full_name,
                    description: r.description,
                    stars: r.stargazers_count,
                    url: r.html_url,
                }));

                return { content: [{ type: "text", text: JSON.stringify(repos, null, 2) }] };
            } catch (error) {
                return {
                    content: [{ type: "text", text: `GitHub API Error: ${error.message}` }],
                    isError: true,
                };
            }
        }

        throw new Error(`Unknown tool: ${name}`);
    } catch (error) {
        return {
            content: [{ type: "text", text: `Error: ${error.message}` }],
            isError: true,
        };
    }
});

const transport = new StdioServerTransport();
await server.connect(transport);
