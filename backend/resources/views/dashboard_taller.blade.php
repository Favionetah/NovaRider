<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novarider - Panel del Taller</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <div class="w-64 bg-gray-950 border-r border-gray-800 flex flex-col justify-between">
            <div class="p-5">
                <div class="flex items-center space-x-3 text-emerald-500 font-bold text-xl tracking-wider mb-8">
                    <i class="fa-solid fa-motorcycle text-2xl"></i>
                    <span>NOVARIDER</span>
                </div>
                
                <nav class="space-y-2">
                    <a href="#" class="flex items-center space-x-3 px-4 py-2.5 bg-emerald-600 text-white rounded-lg font-medium transition">
                        <i class="fa-solid fa-chart-pie w-5"></i>
                        <span>Resumen General</span>
                    </a>
                    <a href="{{ route('caja.index') }}" class="flex items-center space-x-3 px-4 py-2.5 text-gray-400 hover:bg-gray-800 hover:text-white rounded-lg transition">
                        <i class="fa-solid fa-cash-register w-5"></i>
                        <span>Módulo Caja</span>
                    </a>
                    <a href="{{ route('equipamiento.index') }}" class="flex items-center space-x-3 px-4 py-2.5 text-gray-400 hover:bg-gray-800 hover:text-white rounded-lg transition">
                        <i class="fa-solid fa-screwdriver-wrench w-5"></i>
                        <span>Equipamiento</span>
                    </a>
                </nav>
            </div>
            
            <div class="p-5 border-t border-gray-800">
                <div class="flex items-center space-x-3 text-sm">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center font-bold text-gray-950">
                        M
                    </div>
                    <div>
                        <p class="font-medium text-white">Marcelo</p>
                        <p class="text-xs text-gray-500 font-mono">Rol: Líder</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col overflow-y-auto">
            <header class="bg-gray-950 h-16 border-b border-gray-800 flex items-center justify-between px-8">
                <h1 class="text-lg font-semibold text-white">Panel de Control de Operaciones</h1>
                <span class="bg-emerald-500/10 text-emerald-400 text-xs px-2.5 py-1 rounded-full font-mono border border-emerald-500/20">
                    Servidor Activo
                </span>
            </header>

            <main class="p-8 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-950 p-6 rounded-xl border border-gray-800">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-400 font-medium">Estado de Caja</p>
                                <h3 class="text-2xl font-bold text-emerald-400 mt-1">Abierta</h3>
                            </div>
                            <span class="p-3 bg-emerald-500/10 text-emerald-400 rounded-lg"><i class="fa-solid fa-door-open"></i></span>
                        </div>
                    </div>

                    <div class="bg-gray-950 p-6 rounded-xl border border-gray-800">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-400 font-medium">Efectivo Estimado</p>
                                <h3 class="text-2xl font-bold text-white mt-1">1,500.00 Bs.</h3>
                            </div>
                            <span class="p-3 bg-blue-500/10 text-blue-400 rounded-lg"><i class="fa-solid fa-money-bill-wave"></i></span>
                        </div>
                    </div>

                    <div class="bg-gray-950 p-6 rounded-xl border border-gray-800">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-400 font-medium">Alertas de Equipos</p>
                                <h3 class="text-2xl font-bold text-amber-400 mt-1">1 Reporte</h3>
                            </div>
                            <span class="p-3 bg-amber-500/10 text-amber-400 rounded-lg"><i class="fa-solid fa-triangle-exclamation"></i></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-gray-950 p-6 rounded-xl border border-gray-800 space-y-4">
                        <h2 class="text-md font-bold text-white tracking-wide border-b border-gray-800 pb-3 flex items-center gap-2">
                            <i class="fa-solid fa-cash-register text-emerald-500"></i> Acciones de Caja y Recibos
                        </h2>
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <button class="bg-gray-900 hover:bg-gray-850 p-4 rounded-lg border border-gray-800 text-left transition group">
                                <i class="fa-solid fa-unlock text-emerald-400 mb-2 block text-lg"></i>
                                <span class="font-medium text-sm block text-white group-hover:text-emerald-400">Apertura Turno</span>
                            </button>
                            <button class="bg-gray-900 hover:bg-gray-850 p-4 rounded-lg border border-gray-800 text-left transition group">
                                <i class="fa-solid fa-file-invoice-dollar text-blue-400 mb-2 block text-lg"></i>
                                <span class="font-medium text-sm block text-white group-hover:text-blue-400">Emitir Recibo</span>
                            </button>
                        </div>
                    </div>

                    <div class="bg-gray-950 p-6 rounded-xl border border-gray-800 space-y-4">
                        <h2 class="text-md font-bold text-white tracking-wide border-b border-gray-800 pb-3 flex items-center gap-2">
                            <i class="fa-solid fa-screwdriver-wrench text-amber-500"></i> Control de Infraestructura
                        </h2>
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <button class="bg-gray-900 hover:bg-gray-850 p-4 rounded-lg border border-gray-800 text-left transition group">
                                <i class="fa-solid fa-ban text-rose-400 mb-2 block text-lg"></i>
                                <span class="font-medium text-sm block text-white group-hover:text-rose-400">Reportar Falla</span>
                            </button>
                            <button class="bg-gray-900 hover:bg-gray-850 p-4 rounded-lg border border-gray-800 text-left transition group">
                                <i class="fa-solid fa-calendar-check text-purple-400 mb-2 block text-lg"></i>
                                <span class="font-medium text-sm block text-white group-hover:text-purple-400">Mantenimientos</span>
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>

    </div>

</body>
</html>