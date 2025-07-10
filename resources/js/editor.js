import { Editor } from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';

// Toast UI Editor 초기화
function initializeEditor(elementId, options = {}) {
    const container = document.getElementById(elementId);
    if (!container) return null;

    const defaultOptions = {
        height: '400px',
        initialEditType: 'wysiwyg',
        previewStyle: 'vertical',
        placeholder: '내용을 입력하세요...',
        language: 'ko-KR',
        toolbarItems: [
            ['heading', 'bold', 'italic', 'strike'],
            ['hr', 'quote'],
            ['ul', 'ol', 'task', 'indent', 'outdent'],
            ['table', 'image', 'link'],
            ['code', 'codeblock'],
            ['scrollSync']
        ],
        hooks: {
            addImageBlobHook: (blob, callback) => {
                uploadImage(blob, callback);
            }
        },
        ...options
    };

    const editor = new Editor(defaultOptions);
    editor.getHTML();
    
    return editor;
}

// 이미지 업로드 함수
function uploadImage(blob, callback) {
    const formData = new FormData();
    formData.append('image', blob);
    
    // CSRF 토큰 추가
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        formData.append('_token', csrfToken.getAttribute('content'));
    }

    fetch('/admin/posts/upload-image', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : '',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            callback(data.url, data.path);
        } else {
            alert('이미지 업로드 실패: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('이미지 업로드 오류:', error);
        alert('이미지 업로드 중 오류가 발생했습니다.');
    });
}

// 전역으로 함수 노출
window.initializeEditor = initializeEditor;

// DOM 로드 시 자동 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 포스트 생성/수정 폼의 에디터 초기화
    const postContentEditor = document.getElementById('post-content-editor');
    if (postContentEditor) {
        const editor = initializeEditor('post-content-editor');
        
        // 폼 제출 시 에디터 내용을 hidden input에 저장
        const form = postContentEditor.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                const contentInput = document.getElementById('content');
                if (contentInput && editor) {
                    contentInput.value = editor.getHTML();
                }
            });
        }
    }
});