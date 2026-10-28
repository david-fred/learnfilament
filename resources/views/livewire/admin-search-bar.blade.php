<div class="relative flex items-center">
    <input 
        type="text" 
        wire:model.live.debounce.300ms="search" 
        placeholder="Search orders, products..." 
        class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
    >

    @if(!empty($search) && count($results) > 0)
        <div class="absolute left-0 w-64 mt-2 bg-white rounded-lg shadow-lg top-full dark:bg-gray-900 border border-gray-200 dark:border-gray-700 z-50">
            @foreach($results as $order)
                <a href="{{ route('filament.admin.resources.orders.edit', $order) }}" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                    Order #{{ $order->id }} - ₦{{ number_format($order->price, 2) }}
                </a>
            @endforeach
        </div>
    @endif
</div>