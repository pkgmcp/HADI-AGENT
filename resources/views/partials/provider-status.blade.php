@props(['name' => '', 'available' => false, 'models' => []])

<div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
    <div>
        <span class="font-medium">{{ $name }}</span>
        <span class="text-xs text-gray-500 ml-2">{{ implode(', ', $models) }}</span>
    </div>
    <span class="px-2 py-1 rounded-full text-xs {{ $available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
        {{ $available ? 'Connected' : 'Disconnected' }}
    </span>
</div>
