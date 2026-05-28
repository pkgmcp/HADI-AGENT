@extends('layouts.app')

@section('title', 'MCP Server - HADI Agent')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">MCP Server</h2>
        <span id="health-status" class="px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-600">Checking...</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">AI Providers</h3>
            <div id="providers-list" class="space-y-3">
                <p class="text-gray-500">Loading providers...</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Token Usage</h3>
            <div id="token-usage" class="space-y-3">
                <p class="text-gray-500">Loading usage data...</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Send Message</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Message</label>
                <textarea id="message-content" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
            <button onclick="sendMessage()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Send to MCP
            </button>
            <div id="message-response" class="mt-4 p-4 bg-gray-50 rounded-md hidden"></div>
        </div>
    </div>
</div>

<script>
async function loadStatus() {
    try {
        const [status, health] = await Promise.all([
            fetch('/api/v1/mcp/status').then(r => r.json()),
            fetch('/api/v1/mcp/health').then(r => r.json()),
        ]);

        const healthEl = document.getElementById('health-status');
        if (health.data?.status === 'operational') {
            healthEl.className = 'px-3 py-1 rounded-full text-sm bg-green-100 text-green-800';
            healthEl.textContent = 'Operational';
        } else {
            healthEl.className = 'px-3 py-1 rounded-full text-sm bg-red-100 text-red-800';
            healthEl.textContent = 'Error';
        }

        const providersEl = document.getElementById('providers-list');
        const providers = status.data?.providers ?? {};
        if (Object.keys(providers).length === 0) {
            providersEl.innerHTML = '<p class="text-gray-500">No providers configured</p>';
        } else {
            providersEl.innerHTML = Object.entries(providers).map(([name, info]) => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <span class="font-medium">${name}</span>
                    <span class="text-sm ${info.available ? 'text-green-600' : 'text-red-600'}">
                        ${info.available ? 'Available' : 'Unavailable'}
                    </span>
                </div>
            `).join('');
        }

        const usageEl = document.getElementById('token-usage');
        const tokens = status.data?.token_usage ?? {};
        usageEl.innerHTML = `
            <div class="flex justify-between p-3 bg-gray-50 rounded-md">
                <span>Daily Tokens</span>
                <span class="font-bold">${tokens.daily_tokens ?? 0}</span>
            </div>
            <div class="flex justify-between p-3 bg-gray-50 rounded-md">
                <span>Sessions</span>
                <span class="font-bold">${tokens.total_sessions ?? 0}</span>
            </div>
        `;
    } catch (e) {
        document.getElementById('health-status').textContent = 'Unreachable';
    }
}

async function sendMessage() {
    const content = document.getElementById('message-content').value;
    if (!content) return;

    const responseEl = document.getElementById('message-response');
    responseEl.classList.remove('hidden');
    responseEl.innerHTML = '<p class="text-gray-500">Sending...</p>';

    try {
        const response = await fetch('/api/v1/mcp/messages', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                role: 'user',
                content,
                metadata: {
                    provider: 'openai',
                    session_id: 'web_' + Date.now(),
                },
            }),
        });

        const data = await response.json();
        responseEl.innerHTML = `
            <p class="font-medium">Response:</p>
            <p class="mt-2">${data.data?.content ?? 'No response'}</p>
            <p class="text-sm text-gray-500 mt-2">
                Tokens: ${data.data?.total_tokens ?? 0} |
                Cost: $${data.data?.cost ?? 0} |
                Latency: ${data.data?.latency_ms?.toFixed(0) ?? 0}ms
            </p>
        `;
    } catch (e) {
        responseEl.innerHTML = `<p class="text-red-500">Error: ${e.message}</p>`;
    }
}

loadStatus();
</script>
@endsection
