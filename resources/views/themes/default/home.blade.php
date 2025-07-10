@extends('themes.default.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Hero Section -->
    <div class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Laravel Light Blog에 오신 것을 환영합니다</h1>
        <p class="text-xl text-gray-600 mb-6">경량화된 고성능 웹진 플랫폼</p>
        <div class="flex flex-wrap gap-4">
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Laravel 11</span>
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">PHP 8.3</span>
            <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">TailwindCSS</span>
            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">MariaDB</span>
        </div>
    </div>
    
    <!-- Recent Posts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">최근 포스트</h2>
            
            <!-- Sample Post -->
            <article class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="h-48 bg-gradient-to-r from-blue-500 to-purple-600"></div>
                <div class="p-6">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <span>2025년 7월 3일</span>
                        <span class="mx-2">•</span>
                        <span>개발</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Laravel Light Blog 시작하기</h3>
                    <p class="text-gray-600 mb-4">경량화된 블로그 시스템의 첫 번째 포스트입니다. 앞으로 다양한 기능들이 추가될 예정입니다.</p>
                    <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">더 읽기 →</a>
                </div>
            </article>
            
            <!-- Another Sample Post -->
            <article class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="h-48 bg-gradient-to-r from-green-500 to-blue-600"></div>
                <div class="p-6">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <span>2025년 7월 2일</span>
                        <span class="mx-2">•</span>
                        <span>기술</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">TailwindCSS로 반응형 디자인 구현</h3>
                    <p class="text-gray-600 mb-4">모바일 퍼스트 접근 방식으로 아름다운 반응형 웹사이트를 만드는 방법을 알아봅시다.</p>
                    <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">더 읽기 →</a>
                </div>
            </article>
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">카테고리</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-600 hover:text-gray-900">개발 (5)</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-gray-900">기술 (3)</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-gray-900">일상 (2)</a></li>
                </ul>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">태그</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">Laravel</span>
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">PHP</span>
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">JavaScript</span>
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">CSS</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection