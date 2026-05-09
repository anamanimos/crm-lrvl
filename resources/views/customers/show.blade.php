<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                <a href="{{ route('admin.customers.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                {{ $customer->name }}
            </h2>
            <div class="flex space-x-2">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-all flex items-center shadow-sm shadow-indigo-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-12 gap-6 h-[calc(100vh-200px)]">
                
                <!-- Left: Profile Sidebar -->
                <div class="col-span-12 lg:col-span-3 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="h-24 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                        <div class="px-6 pb-6">
                            <div class="relative -mt-12 mb-4">
                                <div class="w-24 h-24 rounded-2xl bg-white p-1 shadow-lg mx-auto">
                                    <div class="w-full h-full rounded-xl bg-gray-100 flex items-center justify-center text-indigo-600 font-bold text-3xl">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mb-6">
                                <h3 class="text-xl font-bold text-gray-900">{{ $customer->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $customer->wa_number }}</p>

                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Email</label>
                                    <p class="text-sm text-gray-700 break-words">{{ $customer->email ?: '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Source</label>
                                    <p class="text-sm text-gray-700">{{ $customer->source ?: 'Unknown' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Labels</label>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @forelse($customer->labels as $label)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold" style="background-color: {{ $label->color }}20; color: {{ $label->color }}">
                                                {{ $label->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">No labels</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deals Summary -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Active Deals
                        </h4>
                        <div class="space-y-3">
                            @forelse($customer->deals as $deal)
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="text-xs font-semibold text-gray-900">{{ $deal->name }}</span>
                                        <span class="text-[10px] text-gray-500">Rp {{ number_format($deal->value, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-[10px] text-indigo-600 font-medium">{{ $deal->stage->name }}</div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 italic">No active deals</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Center: Chat Interface -->
                <div class="col-span-12 lg:col-span-9 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">WhatsApp History</h4>
                                <p class="text-[10px] text-gray-500">Recent messages from legacy system</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-[#f0f2f5] custom-scrollbar">
                        @forelse($customer->messages as $message)
                            <div class="flex {{ $message->direction == 'out' ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[70%] {{ $message->direction == 'out' ? 'bg-[#dcf8c6]' : 'bg-white' }} rounded-2xl p-3 shadow-sm relative group">
                                    @if($message->reply_content)
                                        <div class="mb-2 p-2 bg-black/5 rounded-lg border-l-4 border-indigo-400 text-[10px] italic">
                                            <div class="font-bold text-indigo-600 mb-1">{{ $message->reply_sender_name }}</div>
                                            {{ Str::limit($message->reply_content, 100) }}
                                        </div>
                                    @endif
                                    
                                    @if($message->type == 'image')
                                        <div class="mb-2">
                                            <img src="{{ $message->media_url }}" class="rounded-lg max-w-full cursor-pointer hover:opacity-90 transition-opacity" alt="Media content">
                                        </div>
                                    @endif

                                    <div class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $message->content }}</div>
                                    
                                    <div class="flex items-center justify-end mt-1 space-x-1">
                                        <span class="text-[9px] text-gray-400">{{ \Carbon\Carbon::parse($message->created_at)->format('H:i') }}</span>
                                        @if($message->direction == 'out')
                                            <svg class="w-3 h-3 {{ $message->status == 'read' ? 'text-blue-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M22.35 6.43l-1.41-1.41-10.94 10.94-4.94-4.94-1.41 1.41 6.35 6.35 12.35-12.35zM15.94 6.43l-1.41-1.41-6.35 6.35 1.41 1.41 6.35-6.35z"></path></svg>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center h-full opacity-50">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                <p class="text-gray-500 font-medium">No messages yet</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="p-4 bg-white border-t border-gray-100">
                        <div class="flex space-x-3">
                            <input type="text" placeholder="Type a message (read-only for now)..." class="flex-1 bg-gray-50 border-gray-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" disabled>
                            <button class="bg-gray-100 text-gray-400 p-2 rounded-xl cursor-not-allowed">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
    </style>
</x-app-layout>
