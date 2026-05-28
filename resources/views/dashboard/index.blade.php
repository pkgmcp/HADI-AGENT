@extends('layouts.app')

@section('title', 'Dashboard - HADI Agent')

@section('content')
<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">HADI Agent System</h2>
        <p class="text-gray-600">Enterprise Laravel AI orchestration and MCP server system.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-indigo-600">MCP Server</h3>
            <p class="text-3xl font-bold mt-2" id="provider-count">0</p>
            <p class="text-gray-500 text-sm">Active Providers</p>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-green-600">AI Agents</h3>
            <p class="text-3xl font-bold mt-2" id="agent-count">0</p>
            <p class="text-gray-500 text-sm">Registered Agents</p>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-purple-600">Workflows</h3>
            <p class="text-3xl font-bold mt-2" id="workflow-count">0</p>
            <p class="text-gray-500 text-sm">Active Workflows</p>
        </div>
    </div>
</div>

<script>
async function loadStats() {
    try {
        const [status, agents] = await Promise.all([
            fetch('/api/v1/mcp/status').then(r => r.json()),
            fetch('/api/v1/agents').then(r => r.json()),
        ]);

        document.getElementById('provider-count').textContent =
            status.data?.providers ? Object.keys(status.data.providers).length : 0;
        document.getElementById('agent-count').textContent =
            agents.data?.length ?? 0;
    } catch (e) {
        console.error('Failed to load stats', e);
    }
}
loadStats();
</script>
@endsection
