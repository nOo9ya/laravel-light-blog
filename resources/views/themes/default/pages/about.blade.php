@extends('themes.default.layouts.app')

@section('title', '소개')
@section('meta_description', '저희 블로그에 대해 알아보세요. 우리의 이야기, 비전, 그리고 가치관을 소개합니다.')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- 헤더 섹션 -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">안녕하세요!</h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            저희 블로그에 오신 것을 환영합니다. 이곳은 지식을 나누고, 경험을 공유하며, 
            함께 성장하는 공간입니다.
        </p>
    </div>

    <!-- 메인 콘텐츠 -->
    <div class="max-w-4xl mx-auto">
        <!-- 소개 섹션 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">우리의 이야기</h2>
            <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                <p class="mb-6">
                    이 블로그는 <strong>지식 공유</strong>와 <strong>소통</strong>을 목적으로 시작되었습니다. 
                    우리는 다양한 분야의 전문가들과 함께 유익한 정보를 제공하고, 
                    독자들과의 활발한 소통을 통해 더 나은 콘텐츠를 만들어가고 있습니다.
                </p>
                <p class="mb-6">
                    기술, 개발, 일상, 문화 등 다양한 주제를 다루며, 
                    특히 <em>실무에서 바로 활용할 수 있는 실용적인 내용</em>에 중점을 두고 있습니다.
                </p>
                <p>
                    모든 글은 철저한 검증을 거쳐 발행되며, 독자들의 피드백을 적극 반영하여 
                    지속적으로 개선해나가고 있습니다.
                </p>
            </div>
        </div>

        <!-- 비전 & 미션 -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">우리의 비전</h3>
                </div>
                <p class="text-gray-700 leading-relaxed">
                    지식의 경계를 허물고, 누구나 쉽게 접근할 수 있는 
                    양질의 정보를 제공하는 플랫폼이 되는 것입니다.
                </p>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">우리의 미션</h3>
                </div>
                <p class="text-gray-700 leading-relaxed">
                    정확하고 유용한 정보를 통해 독자들의 성장을 돕고, 
                    서로 배우고 가르치는 건강한 커뮤니티를 만들어갑니다.
                </p>
            </div>
        </div>

        <!-- 핵심 가치 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">핵심 가치</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">학습</h3>
                    <p class="text-gray-600 text-sm">
                        끊임없는 학습과 지식 습득을 통해 
                        더 나은 콘텐츠를 제공합니다.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">공유</h3>
                    <p class="text-gray-600 text-sm">
                        지식과 경험을 아낌없이 나누며 
                        함께 성장하는 문화를 만들어갑니다.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">열정</h3>
                    <p class="text-gray-600 text-sm">
                        독자들에게 도움이 되는 콘텐츠를 
                        만들어가는 열정을 잃지 않습니다.
                    </p>
                </div>
            </div>
        </div>

        <!-- 팀 소개 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">우리 팀</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- 팀원 카드 예시 -->
                <div class="text-center">
                    <div class="w-24 h-24 bg-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-600">팀</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">개발팀</h3>
                    <p class="text-gray-600 text-sm mb-2">기술 블로그 운영</p>
                    <p class="text-gray-500 text-xs">
                        최신 기술 트렌드와 개발 노하우를 공유합니다.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-24 h-24 bg-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-600">콘</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">콘텐츠팀</h3>
                    <p class="text-gray-600 text-sm mb-2">콘텐츠 기획 및 제작</p>
                    <p class="text-gray-500 text-xs">
                        독자들이 원하는 양질의 콘텐츠를 기획하고 제작합니다.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-24 h-24 bg-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-600">커</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">커뮤니티팀</h3>
                    <p class="text-gray-600 text-sm mb-2">독자 소통 및 관리</p>
                    <p class="text-gray-500 text-xs">
                        독자들과의 소통을 담당하고 커뮤니티를 운영합니다.
                    </p>
                </div>
            </div>
        </div>

        <!-- 통계 -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-8 text-white mb-8">
            <h2 class="text-2xl font-semibold mb-6 text-center">우리의 성장</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                    <div class="text-3xl font-bold mb-2">{{ $stats['posts'] ?? '100+' }}</div>
                    <div class="text-indigo-100">게시글</div>
                </div>
                <div>
                    <div class="text-3xl font-bold mb-2">{{ $stats['categories'] ?? '20+' }}</div>
                    <div class="text-indigo-100">카테고리</div>
                </div>
                <div>
                    <div class="text-3xl font-bold mb-2">{{ $stats['tags'] ?? '50+' }}</div>
                    <div class="text-indigo-100">태그</div>
                </div>
                <div>
                    <div class="text-3xl font-bold mb-2">{{ $stats['comments'] ?? '200+' }}</div>
                    <div class="text-indigo-100">댓글</div>
                </div>
            </div>
        </div>

        <!-- CTA 섹션 -->
        <div class="bg-gray-50 rounded-lg p-8 text-center">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">함께해요!</h2>
            <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                우리의 여정에 동참하고 싶으시다면 언제든지 연락주세요. 
                여러분의 소중한 의견과 제안을 기다리고 있습니다.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('contact') }}" 
                   class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    문의하기
                </a>
                <a href="{{ route('posts.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    블로그 둘러보기
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// 스크롤 애니메이션 효과
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in');
        }
    });
}, observerOptions);

// 애니메이션 대상 요소들
document.querySelectorAll('.bg-white, .bg-gradient-to-br, .bg-gradient-to-r').forEach(el => {
    observer.observe(el);
});
</script>

<style>
.animate-fade-in {
    animation: fadeInUp 0.6s ease-out forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush
@endsection