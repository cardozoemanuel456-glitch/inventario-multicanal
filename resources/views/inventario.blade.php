<!doctype html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario en Vivo - Sistema</title>
    <!-- Vite para cargar Tailwind y tus scripts de Echo -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-100 font-sans antialiased flex flex-col justify-between selection:bg-indigo-500 selection:text-white">

    <!-- Header / Navbar Minimalista -->
    <header class="border-b border-slate-800 bg-slate-900/50 backdrop-blur-md sticky top-0 z-10">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_12px_rgba(16,185,129,0.5)]"></div>
                <h1 class="text-lg font-semibold tracking-wide text-slate-200">Control de Inventario <span class="text-indigo-400 font-mono text-xs px-2 py-0.5 rounded bg-indigo-950/60 border border-indigo-800/50">Reverb en Vivo</span></h1>
            </div>
            <div class="text-xs text-slate-400 font-mono" id="status-conexion">Estado: Conectado</div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="max-w-5xl w-full mx-auto px-6 py-10 flex-grow">
        
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden backdrop-blur-sm">
            <div class="px-6 py-5 border-b border-slate-800 flex justify-between items-center">
                <h2 class="text-sm font-medium uppercase tracking-wider text-slate-400">Productos en Almacén</h2>
                <span class="text-xs text-slate-500 font-mono">Actualización instantánea vía WebSockets</span>
            </div>

            <!-- Tabla Moderna -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs font-semibold uppercase tracking-wider text-slate-400 bg-slate-950/40">
                            <th class="py-4 px-6">ID</th>
                            <th class="py-4 px-6">Nombre del Producto</th>
                            <th class="py-4 px-6">Precio</th>
                            <th class="py-4 px-6">Stock Actual</th>
                            <th class="py-4 px-6 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="productos" class="divide-y divide-slate-800/60 text-sm">
                        <!-- Los productos se inyectarán dinámicamente aquí -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Consola de Eventos en Tiempo Real (Logs minimalistas) -->
        <div class="mt-8">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Actividad de Eventos (Reverb)</h3>
                <button onclick="document.getElementById('logs').textContent = ''" class="text-xs text-slate-400 hover:text-indigo-400 transition-colors">Limpiar logs</button>
            </div>
            <pre id="logs" class="bg-slate-950 border border-slate-900 rounded-xl p-4 text-xs font-mono text-indigo-300 h-32 overflow-y-auto shadow-inner"></pre>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-900 py-4 text-center text-xs text-slate-400">
        Panel desarrollado con Laravel, Tailwind CSS y WebSockets.
    </footer>

    <!-- Lógica de Carga y Eventos -->
    <script>
        async function cargar() {
            try {
                const res = await fetch('/productos'); 
                const productos = await res.json();
                
                document.getElementById('productos').innerHTML = productos.map(p => `
                    <tr class="hover:bg-slate-800/40 transition-colors group">
                        <td class="py-4 px-6 font-mono text-slate-400 text-xs">#${p.id}</td>
                        <td class="py-4 px-6 font-medium text-slate-200">${p.nombre}</td>
                        <td class="py-4 px-6 font-mono text-emerald-400">$${Number(p.precio).toFixed(2)}</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                ${p.stock} unidades
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <button data-id="${p.id}" data-stock="${p.stock}" 
                                class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium px-3 py-1.5 rounded-lg shadow-lg shadow-indigo-600/20 active:scale-95 transition-all duration-150 cursor-pointer">
                                +1 Stock
                            </button>
                        </td>
                    </tr>
                `).join('');
            } catch (error) {
                console.error("Error al cargar productos:", error);
            }
        }

        document.addEventListener('click', async e => {
            if (!e.target.matches('button[data-id]')) return;
            const btn = e.target;
            btn.disabled = true;
            
            try {
                await fetch('/productos/' + btn.dataset.id, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ stock: Number(btn.dataset.stock) + 1 })
                });
            } catch (error) {
                console.error("Error al actualizar stock:", error);
            } finally {
                btn.disabled = false;
            }
        });

        window.addEventListener('load', cargar);

        // Escucha el evento personalizado que dispara Echo en app.js
        window.addEventListener('inventario-cambio', e => {
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = `[${timestamp}] Evento recibido: ${JSON.stringify(e.detail)}`;
            
            const logsEl = document.getElementById('logs');
            logsEl.textContent = logEntry + '\n' + logsEl.textContent;
            
            // Recarga automática de la tabla de forma instantánea
            cargar();
        });
    </script>
</body>
</html>