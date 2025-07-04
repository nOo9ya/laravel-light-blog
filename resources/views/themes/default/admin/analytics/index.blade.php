@extends('themes.default.layouts.app')

@section('title', '통계')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">통계</h1>
        <p class="text-gray-600">사이트 방문 및 사용 통계를 확인하세요</p>
    </div>

    <!-- 기본 통계 카드 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-blue-500 text-white p-6 rounded-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">오늘 방문자</h3>
                    <p class="text-2xl font-bold">5</p>
                </div>
                <div class="text-3xl opacity-80">
                    👥
                </div>
            </div>
        </div>
        <div class="bg-green-500 text-white p-6 rounded-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">오늘 페이지뷰</h3>
                    <p class="text-2xl font-bold">12</p>
                </div>
                <div class="text-3xl opacity-80">
                    👁️
                </div>
            </div>
        </div>
        <div class="bg-yellow-500 text-white p-6 rounded-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">오늘 검색</h3>
                    <p class="text-2xl font-bold">3</p>
                </div>
                <div class="text-3xl opacity-80">
                    🔍
                </div>
            </div>
        </div>
        <div class="bg-purple-500 text-white p-6 rounded-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">인기 포스트</h3>
                    <p class="text-2xl font-bold">8</p>
                </div>
                <div class="text-3xl opacity-80">
                    ⭐
                </div>
            </div>
        </div>
    </div>

    <!-- 상세 통계 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 인기 포스트 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">인기 포스트</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">포스트 제목</th>
                            <th class="px-4 py-2 text-left">조회수</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b">
                            <td class="px-4 py-2">테스트 포스트</td>
                            <td class="px-4 py-2">25</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-2">샘플 포스트</td>
                            <td class="px-4 py-2">18</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-2">예시 포스트</td>
                            <td class="px-4 py-2">12</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 인기 검색어 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">인기 검색어</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">검색어</th>
                            <th class="px-4 py-2 text-left">검색 횟수</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b">
                            <td class="px-4 py-2">Laravel</td>
                            <td class="px-4 py-2">15</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-2">PHP</td>
                            <td class="px-4 py-2">8</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-2">블로그</td>
                            <td class="px-4 py-2">5</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 플랫폼 및 브라우저 통계 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
        <!-- 플랫폼 통계 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">플랫폼 통계</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">Windows</span>
                    <span class="text-sm text-gray-600">60%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: 60%"></div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">macOS</span>
                    <span class="text-sm text-gray-600">25%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: 25%"></div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">Linux</span>
                    <span class="text-sm text-gray-600">15%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 15%"></div>
                </div>
            </div>
        </div>

        <!-- 기기 유형 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">기기 유형</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">Desktop</span>
                    <span class="text-sm text-gray-600">70%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: 70%"></div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">Mobile</span>
                    <span class="text-sm text-gray-600">25%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: 25%"></div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">Tablet</span>
                    <span class="text-sm text-gray-600">5%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 5%"></div>
                </div>
            </div>
        </div>

        <!-- 국가별 통계 -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">국가별 방문자</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">🇰🇷 대한민국</span>
                    <span class="text-sm text-gray-600">85%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">🇺🇸 미국</span>
                    <span class="text-sm text-gray-600">8%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">🇯🇵 일본</span>
                    <span class="text-sm text-gray-600">4%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium">🌍 기타</span>
                    <span class="text-sm text-gray-600">3%</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection