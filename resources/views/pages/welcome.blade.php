@extends('layouts.app')

@section('title', 'Welcome - HADI Agent')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl font-bold text-indigo-600 mb-4">HADI Agent</h1>
        <p class="text-xl text-gray-600 mb-8">Hyper-Automated Development Intelligence</p>
        <p class="text-gray-500 mb-8">Enterprise Laravel framework for AI orchestration, MCP servers, and multi-agent systems.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
            <a href="{{ route('agents.index') }}" class="bg-white shadow rounded-lg p-6 hover:shadow-md transition">
                <h3 class="text-lg font-semibold text-indigo-600">AI Agents</h3>
                <p class="text-gray-500 text-sm mt-2">8 specialized agents for planning, coding, reviewing, and more</p>
            </a>
            <a href="{{ route('mcp.index') }}" class="bg-white shadow rounded-lg p-6 hover:shadow-md transition">
                <h3 class="text-lg font-semibold text-teal-600">MCP Server</h3>
                <p class="text-gray-500 text-sm mt-2">Multi-provider routing with token tracking and cost analytics</p>
            </a>
            <a href="{{ route('dashboard') }}" class="bg-white shadow rounded-lg p-6 hover:shadow-md transition">
                <h3 class="text-lg font-semibold text-purple-600">Dashboard</h3>
                <p class="text-gray-500 text-sm mt-2">Real-time system monitoring and usage statistics</p>
            </a>
        </div>
    </div>
</div>
@endsection
