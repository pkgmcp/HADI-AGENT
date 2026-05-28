@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-bold">Admin Dashboard</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h4 class="text-sm font-medium text-gray-500">Total API Calls</h4>
            <p class="text-2xl font-bold mt-1" id="api-calls">-</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <h4 class="text-sm font-medium text-gray-500">Total Cost</h4>
            <p class="text-2xl font-bold mt-1" id="total-cost">-</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <h4 class="text-sm font-medium text-gray-500">Active Sessions</h4>
            <p class="text-2xl font-bold mt-1" id="active-sessions">-</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <h4 class="text-sm font-medium text-gray-500">Budget Used</h4>
            <p class="text-2xl font-bold mt-1" id="budget-used">-</p>
        </div>
    </div>
</div>

<script>
async function loadAdminStats() {
    try {
        const response = await fetch('/api/v1/mcp/status');
        const data = await response.json();
        const tokens = data.data?.token_usage ?? {};

        document.getElementById('api-calls').textContent = tokens.daily_tokens ?? 0;
    } catch (e) {
        document.querySelectorAll('#api-calls, #total-cost, #active-sessions, #budget-used')
            .forEach(el => el.textContent = 'Error');
    }
}
loadAdminStats();
</script>
@endsection
