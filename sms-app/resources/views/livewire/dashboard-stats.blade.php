<div class="space-y-6">
    <!-- Period Selector -->
    <div class="flex justify-end">
        <div class="inline-flex rounded-md shadow-sm" role="group">
            <button 
                type="button" 
                wire:click="$set('period', 'week')" 
                class="px-4 py-2 text-sm font-medium rounded-l-lg border {{ $period === 'week' ? 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900 dark:text-blue-200' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600' }}"
            >
                This Week
            </button>
            <button 
                type="button" 
                wire:click="$set('period', 'month')" 
                class="px-4 py-2 text-sm font-medium border-t border-b {{ $period === 'month' ? 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900 dark:text-blue-200' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600' }}"
            >
                This Month
            </button>
            <button 
                type="button" 
                wire:click="$set('period', 'year')" 
                class="px-4 py-2 text-sm font-medium rounded-r-lg border {{ $period === 'year' ? 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900 dark:text-blue-200' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600' }}"
            >
                This Year
            </button>
        </div>
    </div>

    <!-- Loading Indicator -->
    @if($loading)
        <div class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>
    @else
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Messages Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Messages</h3>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_messages'] ?? 0) }}</p>
                    </div>
                </div>
                @if(isset($stats['trend']) && $stats['trend'] != 0)
                    <div class="mt-4 text-sm {{ $stats['trend'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $stats['trend'] > 0 ? '↑' : '↓' }} {{ abs($stats['trend']) }}% from last {{ $stats['period'] }}
                    </div>
                @endif
            </div>

            <!-- Delivered Messages Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Delivered</h3>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['delivered'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="mt-2">
                    @if(isset($stats['total_messages']) && $stats['total_messages'] > 0)
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($stats['delivered'] / $stats['total_messages']) * 100 }}%"></div>
                        </div>
                        <div class="mt-1 text-xs text-right text-gray-500 dark:text-gray-400">
                            {{ round(($stats['delivered'] / $stats['total_messages']) * 100, 1) }}% success rate
                        </div>
                    @endif
                </div>
            </div>

            <!-- Failed Messages Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Failed</h3>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['failed'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="mt-2">
                    @if(isset($stats['total_messages']) && $stats['total_messages'] > 0)
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-red-600 h-2 rounded-full" style="width: {{ ($stats['failed'] / $stats['total_messages']) * 100 }}%"></div>
                        </div>
                        <div class="mt-1 text-xs text-right text-gray-500 dark:text-gray-400">
                            {{ round(($stats['failed'] / $stats['total_messages']) * 100, 1) }}% failure rate
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Messages Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</h3>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['pending'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-xs text-yellow-600 dark:text-yellow-400">
                        Messages in queue or scheduled
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Messages -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Messages</h3>
                        <a href="{{ route('messages.history') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                            View all
                        </a>
                    </div>
                    <div class="flow-root">
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($recentMessages as $message)
                                <li class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full {{ $message['status_class'] }}">
                                                {{ strtoupper(substr($message['status'], 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                                {{ $message['subject'] }}
                                            </p>
                                            <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                                {{ $message['recipients_count'] }} recipients • {{ $message['created_at'] }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $message['service'] }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="py-4 text-center text-gray-500 dark:text-gray-400">
                                    No recent messages found.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-6">
                <!-- Account Balance -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Account Balance</h3>
                        @if(isset($serviceBalances['error']))
                            <div class="text-red-600 dark:text-red-400 text-sm">
                                {{ $serviceBalances['error'] }}
                            </div>
                        @else
                            <div class="text-center">
                                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format($serviceBalances['balance'] ?? 0, 2) }}
                                    <span class="text-lg text-gray-500 dark:text-gray-400">{{ $serviceBalances['currency'] ?? 'KES' }}</span>
                                </p>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Current service: {{ $serviceBalances['current_service'] ?? 'N/A' }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Contacts Summary -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Contacts</h3>
                            <a href="{{ route('contacts.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                                Manage
                            </a>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($contactStats['total'] ?? 0) }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total contacts</p>
                            </div>
                            @if(($contactStats['recent'] ?? 0) > 0)
                                <div>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">+{{ number_format($contactStats['recent']) }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">New this week</p>
                                </div>
                            @endif
                            @if(!empty($contactStats['groups']))
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Top Groups</h4>
                                    <div class="space-y-2">
                                        @foreach($contactStats['groups'] as $group => $count)
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-900 dark:text-white">{{ $group }}</span>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $count }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
