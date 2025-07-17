<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Message History</h2>
                <div class="flex space-x-2">
                    <button 
                        wire:click="exportToCsv"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export to CSV
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg border border-blue-100 dark:border-blue-800">
                    <div class="text-blue-600 dark:text-blue-400 text-sm font-medium">Total Messages</div>
                    <div class="text-2xl font-bold text-blue-800 dark:text-blue-200">{{ number_format($stats['total']) }}</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg border border-green-100 dark:border-green-800">
                    <div class="text-green-600 dark:text-green-400 text-sm font-medium">Delivered</div>
                    <div class="text-2xl font-bold text-green-800 dark:text-green-200">{{ number_format($stats['delivered']) }}</div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/30 p-4 rounded-lg border border-yellow-100 dark:border-yellow-800">
                    <div class="text-yellow-600 dark:text-yellow-400 text-sm font-medium">Pending</div>
                    <div class="text-2xl font-bold text-yellow-800 dark:text-yellow-200">{{ number_format($stats['pending']) }}</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-lg border border-red-100 dark:border-red-800">
                    <div class="text-red-600 dark:text-red-400 text-sm font-medium">Failed</div>
                    <div class="text-2xl font-bold text-red-800 dark:text-red-200">{{ number_format($stats['failed']) }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <input
                            type="text"
                            id="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search messages..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-600 dark:text-white"
                        >
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select
                            id="status"
                            wire:model.live="status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-600 dark:text-white"
                        >
                            <option value="">All Statuses</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="service" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service</label>
                        <select
                            id="service"
                            wire:model.live="service"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-600 dark:text-white"
                        >
                            <option value="">All Services</option>
                            @foreach($services as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="dateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                        <input
                            type="date"
                            id="dateFrom"
                            wire:model.live="dateFrom"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-600 dark:text-white"
                        >
                    </div>
                    <div>
                        <label for="dateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                        <div class="flex space-x-2">
                            <input
                                type="date"
                                id="dateTo"
                                wire:model.live="dateTo"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-600 dark:text-white"
                            >
                            <button
                                type="button"
                                wire:click="resetFilters"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-600 dark:text-white dark:hover:bg-gray-500"
                                title="Reset Filters"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('id')">
                                ID
                                @if($sortField === 'id')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Subject / Message
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                                Status
                                @if($sortField === 'status')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Recipients
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                                Date
                                @if($sortField === 'created_at')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($messages as $message)
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    'sent' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                    'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    'failed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    'scheduled' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                ][$message->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                
                                $stats = $message->getStatusCounts();
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                                    {{ $message->id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $message->subject ?: 'No Subject' }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                        {{ $message->message }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors }}">
                                        {{ $statuses[$message->status] ?? ucfirst($message->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-200">Total: {{ $message->recipients_count }}</div>
                                    <div class="flex space-x-1 mt-1">
                                        @if(($stats['delivered'] ?? 0) > 0)
                                            <span class="inline-flex items-center text-xs text-green-700 dark:text-green-400">
                                                {{ $stats['delivered'] }}✓
                                            </span>
                                        @endif
                                        @if(($stats['failed'] ?? 0) > 0)
                                            <span class="inline-flex items-center text-xs text-red-700 dark:text-red-400">
                                                {{ $stats['failed'] }}✗
                                            </span>
                                        @endif
                                        @if(($stats['pending'] ?? 0) > 0)
                                            <span class="inline-flex items-center text-xs text-yellow-700 dark:text-yellow-400">
                                                {{ $stats['pending'] }}…
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $message->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button
                                        type="button"
                                        wire:click="showMessageDetails({{ $message->id }})"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3"
                                        title="View Details"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No messages found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $messages->links() }}
            </div>
        </div>
    </div>

    <!-- Message Details Modal -->
    <x-dialog-modal wire:model.live="showDetailsModal">
        <x-slot name="title">
            Message Details
        </x-slot>
        
        <x-slot name="content">
            @if($selectedMessage)
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Subject</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $selectedMessage->subject ?: 'No Subject' }}</p>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Message</h3>
                        <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                            <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $selectedMessage->message }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Details</h3>
                            <dl class="mt-2 space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ ucfirst($selectedMessage->status) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $services[$selectedMessage->service] ?? $selectedMessage->service }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $selectedMessage->created_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                @if($selectedMessage->scheduled_at)
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Scheduled For:</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $selectedMessage->scheduled_at->format('M j, Y g:i A') }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recipients ({{ $recipients->count() }})</h3>
                            <div class="mt-2 max-h-60 overflow-y-auto border rounded-md">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Phone</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($recipients as $recipient)
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                    'sent' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                    'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                    'failed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                ][$recipient->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                                    {{ $recipient->phone_number }}
                                                    @if($recipient->contact && $recipient->contact->name)
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $recipient->contact->name }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors }}">
                                                        {{ ucfirst($recipient->status) }}
                                                    </span>
                                                    @if($recipient->status === 'failed' && $recipient->error_message)
                                                        <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $recipient->error_message }}</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </x-slot>
        
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDetailsModal', false)" wire:loading.attr="disabled">
                Close
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>
</div>
