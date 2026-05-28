<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HADI Agent')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-indigo-600">HADI</h1>
                    </div>
                    <div class="ml-6 flex space-x-8">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-700 hover:text-indigo-600">
                            Dashboard
                        </a>
                        <a href="{{ route('agents.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-700 hover:text-indigo-600">
                            Agents
                        </a>
                        <a href="{{ route('mcp.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-700 hover:text-indigo-600">
                            MCP
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4">
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 mt-8">
        <div class="max-w-7xl mx-auto py-4 px-4 text-center text-sm text-gray-500">
            HADI Agent System &copy; {{ date('Y') }}
        </div>
    </footer>
</body>
</html>
