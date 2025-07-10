@extends(themed('layouts.app'))

@section('title', '에러 로그 관리')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- 헤더 -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">에러 로그 관리</h1>
                    <p class="mt-2 text-sm text-gray-600">시스템 에러 로그를 모니터링하고 관리합니다.</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="refreshLogs()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        새로고침
                    </button>
                    <form action="{{ route('admin.logs.clear') }}" method="POST" class="inline" onsubmit="return confirm('정말로 모든 에러 로그를 삭제하시겠습니까?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            로그 삭제
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 통계 카드 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">전체 에러</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['error'] + $stats['critical'] + $stats['emergency'] + $stats['alert'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">경고</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['warning'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">최근 1시간</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['recent_errors'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">로그 파일</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $fileInfo['size_human'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 검색 및 필터 -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <div class="flex-1 max-w-lg">
                        <label for="search" class="sr-only">검색</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="search" name="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="에러 메시지 검색..." onkeyup="searchLogs(this.value)">
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <select id="limit" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md" onchange="changeLimitAndRefresh(this.value)">
                            <option value="50">50개</option>
                            <option value="100" selected>100개</option>
                            <option value="200">200개</option>
                            <option value="500">500개</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- 에러 로그 목록 -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">최근 에러 로그</h3>
                <p class="mt-1 text-sm text-gray-500">시간 순으로 정렬된 시스템 에러 로그입니다.</p>
            </div>
            
            <div id="logs-container">
                @include('themes.default.admin.logs.partials.log-list', ['logs' => $logs])
            </div>
        </div>
    </div>
</div>

<script>
let searchTimeout;

function searchLogs(query) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const limit = document.getElementById('limit').value;
        fetchLogs(query, limit);
    }, 500);
}

function changeLimitAndRefresh(limit) {
    const search = document.getElementById('search').value;
    fetchLogs(search, limit);
}

function refreshLogs() {
    const search = document.getElementById('search').value;
    const limit = document.getElementById('limit').value;
    fetchLogs(search, limit);
}

function fetchLogs(search = '', limit = 100) {
    const url = new URL('{{ route("admin.logs.ajax") }}');
    url.searchParams.append('search', search);
    url.searchParams.append('limit', limit);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 로그 목록 업데이트
                updateLogsList(data.logs);
                // 통계 업데이트는 여기서 필요시 구현
            }
        })
        .catch(error => {
            console.error('Error fetching logs:', error);
        });
}

function updateLogsList(logs) {
    const container = document.getElementById('logs-container');
    let html = '<div class="divide-y divide-gray-200">';
    
    if (logs.length === 0) {
        html += `
            <div class="px-6 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">로그가 없습니다</h3>
                <p class="mt-1 text-sm text-gray-500">검색 조건을 변경해 보세요.</p>
            </div>
        `;
    } else {
        logs.forEach(log => {
            const timestamp = new Date(log.timestamp).toLocaleString('ko-KR');
            const message = log.message.length > 100 ? log.message.substring(0, 100) + '...' : log.message;
            
            html += `
                <div class="px-6 py-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${log.level_class}">
                                    ${log.level.toUpperCase()}
                                </span>
                                <span class="text-sm text-gray-500">${timestamp}</span>
                            </div>
                            <p class="text-sm text-gray-900 mb-2">${message}</p>
                            ${log.stack_trace ? `
                                <details class="mt-2">
                                    <summary class="text-sm text-blue-600 cursor-pointer hover:text-blue-800">스택 트레이스 보기</summary>
                                    <pre class="mt-2 text-xs text-gray-700 bg-gray-50 p-3 rounded overflow-x-auto">${log.stack_trace}</pre>
                                </details>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    html += '</div>';
    container.innerHTML = html;
}

// 30초마다 자동 새로고침
setInterval(refreshLogs, 30000);
</script>
@endsection