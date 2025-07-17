<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    {{-- In work, do what you enjoy. --}}
    
    <!-- Success Message -->
    @if($sendSuccess)
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">SMS sent successfully!</span>
            <button wire:click="$set('sendSuccess', false)" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Error Message -->
    @if($sendError)
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ $sendError }}</span>
            <button wire:click="$set('sendError', null)" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
            </button>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Compose SMS</h2>
            
            <!-- Recipients -->
            <div class="mb-6">
                <label for="recipients" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Recipients
                </label>
                
                <!-- Selected Recipients -->
                <div class="flex flex-wrap gap-2 mb-2">
                    @foreach($recipients as $index => $recipient)
                        <div class="flex items-center bg-blue-100 text-blue-800 text-sm rounded-full px-3 py-1">
                            <span>{{ $recipient['name'] }}</span>
                            <button type="button" wire:click="removeRecipient({{ $index }})" class="ml-2 text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                
                <!-- Add Recipient -->
                <div class="flex space-x-2 mb-2">
                    <div class="flex-1 relative">
                        <input
                            type="text"
                            wire:model.live="search"
                            wire:keydown.escape="$set('showContactList', false)"
                            placeholder="Search contacts or enter a phone number"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                        
                        <!-- Contact Suggestions -->
                        @if($showContactList && $search)
                            <div class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 rounded-md shadow-lg">
                                <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none">
                                    @forelse($contacts as $contact)
                                        <li 
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50 dark:hover:bg-gray-600"
                                            wire:click="addRecipient('{{ $contact['phone'] }}', '{{ $contact['name'] }}')"
                                        >
                                            <div class="flex items-center">
                                                <span class="font-normal block truncate">
                                                    {{ $contact['name'] }} ({{ $contact['phone'] }})
                                                    @if($contact['group'])
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $contact['group'] }}
                                                        </span>
                                                    @endif
                                                </span>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="py-2 pl-3 pr-9 text-gray-700 dark:text-gray-300">
                                            No contacts found
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        @endif
                    </div>
                    
                    <button 
                        type="button" 
                        wire:click="addCustomRecipient"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Add
                    </button>
                </div>
                
                <!-- Import Contacts -->
                <div class="mt-2 flex items-center space-x-4">
                    <div class="flex-1">
                        <input 
                            type="file" 
                            wire:model="importFile" 
                            accept=".csv,.txt"
                            class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100"
                        >
                        @error('importFile')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button 
                        type="button" 
                        wire:click="importContacts"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <span wire:loading.remove>Import</span>
                        <span wire:loading>Importing...</span>
                    </button>
                    
                    @if(count($groups) > 0)
                        <div class="flex-1 flex items-center space-x-2">
                            <select 
                                wire:model="selectedGroup"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                                <option value="">Select a group</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group }}">{{ $group }}</option>
                                @endforeach
                            </select>
                            <button 
                                type="button" 
                                wire:click="addGroupRecipients"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Add Group
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Subject -->
            <div class="mb-6">
                <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Subject (Optional)
                </label>
                <input 
                    type="text" 
                    id="subject" 
                    wire:model.lazy="subject" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Message subject"
                >
                @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Message -->
            <div class="mb-4">
                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Message
                </label>
                <textarea 
                    id="message" 
                    rows="6" 
                    wire:model.live="message"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Type your message here..."
                ></textarea>
                <div class="mt-1 flex justify-between">
                    <div>
                        @error('message')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="{{ $charCount > $charLimit ? 'text-red-600' : '' }}">{{ $charCount }}</span> / {{ $charLimit }} characters
                        @if($charCount > $charLimit)
                            <span class="text-red-600">({{ ceil($charCount / $charLimit) }} SMS)</span>
                        @endif
                    </p>
                </div>
            </div>
            
            <!-- Service Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Send Via
                </label>
                <div class="flex space-x-4">
                    <label class="inline-flex items-center">
                        <input 
                            type="radio" 
                            class="form-radio h-4 w-4 text-blue-600" 
                            wire:model="service" 
                            value="twilio"
                        >
                        <span class="ml-2 text-gray-700 dark:text-gray-300">Twilio</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input 
                            type="radio" 
                            class="form-radio h-4 w-4 text-green-600" 
                            wire:model="service" 
                            value="textsms"
                        >
                        <span class="ml-2 text-gray-700 dark:text-gray-300">TextSMS Kenya</span>
                    </label>
                </div>
            </div>
            
            <!-- Schedule -->
            <div class="mb-6">
                <label for="scheduledAt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Schedule (Optional)
                </label>
                <input 
                    type="datetime-local" 
                    id="scheduledAt" 
                    wire:model="scheduledAt"
                    min="{{ now()->format('Y-m-d\TH:i') }}"
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                @error('scheduledAt')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button 
                    type="button" 
                    wire:click="confirmSend"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <span wire:loading.remove>Send Message</span>
                    <span wire:loading>Sending...</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Send Confirmation Modal -->
    <x-dialog-modal wire:model.live="sendConfirmation">
        <x-slot name="title">
            Confirm Sending SMS
        </x-slot>
        
        <x-slot name="content">
            <p class="text-gray-700 dark:text-gray-300 mb-4">
                You are about to send an SMS to <span class="font-semibold">{{ count($recipients) }} recipient(s)</span>.
                @if($scheduledAt)
                    The message is scheduled for <span class="font-semibold">{{ \Carbon\Carbon::parse($scheduledAt)->format('M j, Y g:i A') }}</span>.
                @else
                    The message will be sent immediately.
                @endif
            </p>
            <p class="text-gray-700 dark:text-gray-300">
                Are you sure you want to proceed?
            </p>
        </x-slot>
        
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('sendConfirmation', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>
            
            <x-button class="ml-3" wire:click="sendSms" wire:loading.attr="disabled">
                <span wire:loading.remove>Send Now</span>
                <span wire:loading>Sending...</span>
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
