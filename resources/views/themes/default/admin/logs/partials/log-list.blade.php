<div class="divide-y divide-gray-200">
    @forelse($logs as $log)
        <div class="px-6 py-4">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $log['level_class'] }}">
                            {{ strtoupper($log['level']) }}
                        </span>
                        <span class="text-sm text-gray-500">{{ $log['parsed_date']->format('Y-m-d H:i:s') }}</span>
                    </div>
                    
                    <p class="text-sm text-gray-900 mb-2">
                        {{ Str::limit($log['message'], 200) }}
                    </p>
                    
                    @if(!empty($log['stack_trace']))
                        <details class="mt-2">
                            <summary class="text-sm text-blue-600 cursor-pointer hover:text-blue-800">스택 트레이스 보기</summary>
                            <pre class="mt-2 text-xs text-gray-700 bg-gray-50 p-3 rounded overflow-x-auto">{{ trim($log['stack_trace']) }}</pre>
                        </details>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="px-6 py-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">로그가 없습니다</h3>
            <p class="mt-1 text-sm text-gray-500">아직 에러 로그가 없습니다.</p>
        </div>
    @endforelse
</div>