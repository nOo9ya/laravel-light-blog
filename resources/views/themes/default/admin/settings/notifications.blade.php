@extends(themed('layouts.app'))

@section('title', '알림 설정')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- 헤더 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">알림 설정</h1>
            <p class="mt-2 text-sm text-gray-600">에러 발생 시 알림을 받을 서비스를 설정합니다.</p>
        </div>

        <!-- 성공/오류 메시지 -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- 알림 설정 폼 -->
        <form action="{{ route('admin.settings.notifications.update') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- 기본 설정 -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">기본 설정</h3>
                    <p class="mt-1 text-sm text-gray-500">알림 전송과 관련된 기본 설정을 관리합니다.</p>
                </div>
                
                <div class="px-6 py-4 space-y-6">
                    <!-- 알림 레벨 설정 -->
                    <div>
                        <label class="text-base font-medium text-gray-900">알림을 받을 에러 레벨</label>
                        <p class="text-sm leading-5 text-gray-500">선택한 레벨의 에러가 발생했을 때만 알림을 전송합니다.</p>
                        <fieldset class="mt-4">
                            <div class="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
                                @php
                                    $levels = [
                                        'emergency' => '긴급',
                                        'alert' => '경고',
                                        'critical' => '치명적',
                                        'error' => '에러',
                                        'warning' => '경고',
                                        'notice' => '알림',
                                        'info' => '정보',
                                        'debug' => '디버그'
                                    ];
                                    $selectedLevels = $settings->notification_levels ?? ['emergency', 'alert', 'critical', 'error'];
                                @endphp
                                @foreach($levels as $level => $label)
                                    <div class="flex items-center">
                                        <input id="level_{{ $level }}" name="notification_levels[]" type="checkbox" value="{{ $level }}" 
                                               @if(in_array($level, $selectedLevels)) checked @endif
                                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                        <label for="level_{{ $level }}" class="ml-3 block text-sm font-medium text-gray-700">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>

                    <!-- 쓰로틀 설정 -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="throttle_minutes" class="block text-sm font-medium text-gray-700">중복 알림 방지 시간 (분)</label>
                            <input type="number" name="throttle_minutes" id="throttle_minutes" 
                                   value="{{ $settings->throttle_minutes ?? 5 }}" min="1" max="60"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <p class="mt-2 text-sm text-gray-500">같은 에러가 이 시간 내에 다시 발생해도 알림을 보내지 않습니다.</p>
                        </div>

                        <div class="flex items-center">
                            <input id="test_mode" name="test_mode" type="checkbox" value="1"
                                   @if($settings->test_mode ?? false) checked @endif
                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="test_mode" class="ml-3 block text-sm font-medium text-gray-700">
                                테스트 모드
                                <p class="text-sm text-gray-500">개발 환경에서도 알림을 전송합니다.</p>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slack 설정 -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900">Slack 설정</h3>
                        </div>
                        <div class="flex items-center">
                            <input id="slack_enabled" name="slack_enabled" type="checkbox" value="1"
                                   @if($settings->slack_enabled ?? false) checked @endif
                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="slack_enabled" class="ml-3 text-sm font-medium text-gray-700">활성화</label>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="slack_webhook_url" class="block text-sm font-medium text-gray-700">Webhook URL *</label>
                        <input type="url" name="slack_webhook_url" id="slack_webhook_url" 
                               value="{{ $settings->slack_webhook_url ?? '' }}"
                               placeholder="https://hooks.slack.com/services/..."
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="slack_channel" class="block text-sm font-medium text-gray-700">채널 (선택사항)</label>
                            <input type="text" name="slack_channel" id="slack_channel" 
                                   value="{{ $settings->slack_channel ?? '' }}"
                                   placeholder="#general"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="slack_username" class="block text-sm font-medium text-gray-700">사용자명</label>
                            <input type="text" name="slack_username" id="slack_username" 
                                   value="{{ $settings->slack_username ?? 'Laravel Error Bot' }}"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Discord 설정 -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.211.375-.445.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900">Discord 설정</h3>
                        </div>
                        <div class="flex items-center">
                            <input id="discord_enabled" name="discord_enabled" type="checkbox" value="1"
                                   @if($settings->discord_enabled ?? false) checked @endif
                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="discord_enabled" class="ml-3 text-sm font-medium text-gray-700">활성화</label>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="discord_webhook_url" class="block text-sm font-medium text-gray-700">Webhook URL *</label>
                        <input type="url" name="discord_webhook_url" id="discord_webhook_url" 
                               value="{{ $settings->discord_webhook_url ?? '' }}"
                               placeholder="https://discord.com/api/webhooks/..."
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="discord_username" class="block text-sm font-medium text-gray-700">사용자명</label>
                        <input type="text" name="discord_username" id="discord_username" 
                               value="{{ $settings->discord_username ?? 'Laravel Error Bot' }}"
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>
            </div>

            <!-- Telegram 설정 -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900">Telegram 설정</h3>
                        </div>
                        <div class="flex items-center">
                            <input id="telegram_enabled" name="telegram_enabled" type="checkbox" value="1"
                                   @if($settings->telegram_enabled ?? false) checked @endif
                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="telegram_enabled" class="ml-3 text-sm font-medium text-gray-700">활성화</label>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="telegram_bot_token" class="block text-sm font-medium text-gray-700">Bot Token *</label>
                        <input type="text" name="telegram_bot_token" id="telegram_bot_token" 
                               value="{{ $settings->telegram_bot_token ?? '' }}"
                               placeholder="123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZ"
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="telegram_chat_id" class="block text-sm font-medium text-gray-700">Chat ID *</label>
                        <input type="text" name="telegram_chat_id" id="telegram_chat_id" 
                               value="{{ $settings->telegram_chat_id ?? '' }}"
                               placeholder="123456789"
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>
            </div>

            <!-- 저장 버튼 -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">설정 저장</h3>
                            <p class="mt-1 text-sm text-gray-500">변경사항을 저장하고 적용합니다.</p>
                        </div>
                        <div class="flex space-x-3">
                            @if($services['any_enabled'])
                                <button type="button" onclick="sendTestNotification()" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    테스트 알림
                                </button>
                            @endif
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                설정 저장
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- 알림 서비스 상태 -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">현재 알림 서비스 상태</h3>
                <p class="mt-1 text-sm text-gray-500">현재 설정된 알림 서비스의 상태를 확인할 수 있습니다.</p>
            </div>
            
            <div class="px-6 py-4 space-y-4">
                <!-- Slack -->
                <div class="flex items-center justify-between p-4 border rounded-lg {{ $services['slack'] ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 {{ $services['slack'] ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 {{ $services['slack'] ? 'text-green-600' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-base font-medium text-gray-900">Slack</h4>
                            <p class="text-sm text-gray-500">{{ $services['slack'] ? '연결됨' : '설정되지 않음' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        @if($services['slack'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">활성화</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">비활성화</span>
                        @endif
                    </div>
                </div>

                <!-- Discord -->
                <div class="flex items-center justify-between p-4 border rounded-lg {{ $services['discord'] ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 {{ $services['discord'] ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 {{ $services['discord'] ? 'text-green-600' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.211.375-.445.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-base font-medium text-gray-900">Discord</h4>
                            <p class="text-sm text-gray-500">{{ $services['discord'] ? '연결됨' : '설정되지 않음' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        @if($services['discord'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">활성화</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">비활성화</span>
                        @endif
                    </div>
                </div>

                <!-- Telegram -->
                <div class="flex items-center justify-between p-4 border rounded-lg {{ $services['telegram'] ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 {{ $services['telegram'] ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 {{ $services['telegram'] ? 'text-green-600' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-base font-medium text-gray-900">Telegram</h4>
                            <p class="text-sm text-gray-500">{{ $services['telegram'] ? '연결됨' : '설정되지 않음' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        @if($services['telegram'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">활성화</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">비활성화</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 설정 가이드 -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">설정 가이드</h3>
                <p class="mt-1 text-sm text-gray-500">각 서비스의 알림을 설정하는 방법입니다.</p>
            </div>
            
            <div class="px-6 py-4 space-y-6">
                <!-- Slack 설정 -->
                <div class="border-l-4 border-blue-400 pl-4">
                    <h4 class="text-base font-medium text-gray-900 mb-2">Slack 설정</h4>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>1. Slack 워크스페이스에서 Incoming Webhooks 앱을 설치합니다.</p>
                        <p>2. 알림을 받을 채널을 선택하고 Webhook URL을 복사합니다.</p>
                        <p>3. <code class="bg-gray-100 px-1 rounded">.env</code> 파일에 다음을 추가합니다:</p>
                        <pre class="bg-gray-50 p-2 rounded text-xs font-mono">SLACK_WEBHOOK_URL=https://hooks.slack.com/services/...</pre>
                    </div>
                </div>

                <!-- Discord 설정 -->
                <div class="border-l-4 border-purple-400 pl-4">
                    <h4 class="text-base font-medium text-gray-900 mb-2">Discord 설정</h4>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>1. Discord 서버에서 채널 설정 → 연동을 클릭합니다.</p>
                        <p>2. Webhook 생성을 클릭하고 Webhook URL을 복사합니다.</p>
                        <p>3. <code class="bg-gray-100 px-1 rounded">.env</code> 파일에 다음을 추가합니다:</p>
                        <pre class="bg-gray-50 p-2 rounded text-xs font-mono">DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/...</pre>
                    </div>
                </div>

                <!-- Telegram 설정 -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <h4 class="text-base font-medium text-gray-900 mb-2">Telegram 설정</h4>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>1. BotFather(@botfather)에게 /newbot 명령어로 봇을 생성합니다.</p>
                        <p>2. 봇 토큰을 복사하고, 생성된 봇과 채팅을 시작합니다.</p>
                        <p>3. 봇에게 메시지를 보낸 후, Chat ID를 확인합니다.</p>
                        <p>4. <code class="bg-gray-100 px-1 rounded">.env</code> 파일에 다음을 추가합니다:</p>
                        <pre class="bg-gray-50 p-2 rounded text-xs font-mono">TELEGRAM_BOT_TOKEN=123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZ
TELEGRAM_CHAT_ID=123456789</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- 테스트 알림 -->
        @if($services['any_enabled'])
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">테스트 알림</h3>
                <p class="mt-1 text-sm text-gray-500">설정된 알림 서비스로 테스트 메시지를 전송합니다.</p>
            </div>
            
            <div class="px-6 py-4">
                <button onclick="sendTestNotification()" id="test-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    테스트 알림 전송
                </button>
                
                <div id="test-result" class="mt-4 hidden">
                    <!-- 결과가 여기에 표시됩니다 -->
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function sendTestNotification() {
    const btn = document.getElementById('test-btn');
    const result = document.getElementById('test-result');
    
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>전송 중...';
    
    fetch('{{ route("admin.settings.notifications.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let html = '<div class="bg-green-50 border border-green-200 rounded-md p-4"><div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg></div><div class="ml-3"><h3 class="text-sm font-medium text-green-800">테스트 알림 전송 완료</h3><div class="mt-2 text-sm text-green-700"><ul class="list-disc pl-5 space-y-1">';
            
            Object.entries(data.results).forEach(([service, success]) => {
                const status = success ? '성공' : '실패';
                const color = success ? 'text-green-700' : 'text-red-700';
                html += `<li class="${color}">${service}: ${status}</li>`;
            });
            
            html += '</ul></div></div></div></div>';
            result.innerHTML = html;
            result.classList.remove('hidden');
        } else {
            result.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-md p-4"><div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg></div><div class="ml-3"><h3 class="text-sm font-medium text-red-800">테스트 알림 전송 실패</h3></div></div></div>';
            result.classList.remove('hidden');
        }
    })
    .catch(error => {
        result.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-md p-4"><div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg></div><div class="ml-3"><h3 class="text-sm font-medium text-red-800">네트워크 오류</h3></div></div></div>';
        result.classList.remove('hidden');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>테스트 알림 전송';
    });
}
</script>
@endsection