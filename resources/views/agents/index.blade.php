@extends('layouts.app')

@section('title', 'AI Agents - HADI Agent')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">AI Agents</h2>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Agent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="agents-list">
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading agents...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
async function loadAgents() {
    try {
        const response = await fetch('/api/v1/agents');
        const data = await response.json();
        const tbody = document.getElementById('agents-list');

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No agents registered</td></tr>';
            return;
        }

        tbody.innerHTML = data.data.map(agent => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap font-medium">${agent.name}</td>
                <td class="px-6 py-4 whitespace-nowrap">${agent.type}</td>
                <td class="px-6 py-4 whitespace-nowrap">${agent.config.provider}</td>
                <td class="px-6 py-4 whitespace-nowrap">${agent.config.model}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <button onclick="executeAgent('${agent.name}')" class="text-indigo-600 hover:text-indigo-900">Execute</button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        document.getElementById('agents-list').innerHTML =
            '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Failed to load agents</td></tr>';
    }
}

function executeAgent(name) {
    const task = prompt('Enter task for ' + name + ':');
    if (task) {
        fetch('/api/v1/agents/' + name + '/execute', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({task})
        })
        .then(r => r.json())
        .then(data => alert('Response: ' + data.data.response))
        .catch(e => alert('Error: ' + e.message));
    }
}

loadAgents();
</script>
@endsection
