@props(['name' => '', 'type' => '', 'provider' => '', 'model' => ''])

<div class="bg-white shadow rounded-lg p-6">
    <h3 class="text-lg font-semibold">{{ $name }}</h3>
    <div class="mt-2 space-y-1 text-sm text-gray-600">
        <p>Type: <span class="font-medium">{{ $type }}</span></p>
        <p>Provider: <span class="font-medium">{{ $provider }}</span></p>
        <p>Model: <span class="font-medium">{{ $model }}</span></p>
    </div>
    <div class="mt-4">
        {{ $slot ?? '' }}
    </div>
</div>
