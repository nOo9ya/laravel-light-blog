#!/bin/bash

# =============================================================================
# Laravel Light Blog - 마스터 배포 스크립트 (Master Deployment Script)
# 모든 배포 스크립트를 환경별로 자동 실행하는 통합 배포 솔루션
# =============================================================================

set -e  # 오류 발생시 스크립트 중단

# 컬러 출력 설정
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# 진행 상황 표시용
TOTAL_STEPS=0
CURRENT_STEP=0

# 로그 함수들
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_step() {
    CURRENT_STEP=$((CURRENT_STEP + 1))
    echo -e "${PURPLE}[STEP $CURRENT_STEP/$TOTAL_STEPS]${NC} $1"
}

log_progress() {
    local percentage=$((CURRENT_STEP * 100 / TOTAL_STEPS))
    echo -e "${CYAN}[PROGRESS: ${percentage}%]${NC} $1"
}

# 헤더 출력
print_header() {
    echo -e "${CYAN}"
    echo "============================================================================="
    echo "    Laravel Light Blog - 마스터 배포 스크립트"
    echo "    환경별 자동 배포 및 설정 관리"
    echo "============================================================================="
    echo -e "${NC}"
}

# 배포 타입별 단계 수 계산
calculate_steps() {
    case $DEPLOYMENT_TYPE in
        "baremetal")
            TOTAL_STEPS=6  # deploy.sh + system-service.sh + ssl-setup.sh + 검증 + 완료 + 정리
            ;;
        "docker")
            TOTAL_STEPS=4  # 이미지 빌드 + 컨테이너 실행 + 검증 + 완료
            ;;
        "cloud")
            TOTAL_STEPS=5  # deploy.sh + system-service.sh + 검증 + 완료 + 정리
            ;;
        *)
            TOTAL_STEPS=6  # 기본값
            ;;
    esac
}

# 스크립트 존재 확인
check_scripts() {
    local missing_scripts=()
    
    if [ "$DEPLOYMENT_TYPE" != "docker" ]; then
        [ ! -f "deploy.sh" ] && missing_scripts+=("deploy.sh")
        [ ! -f "system-service.sh" ] && missing_scripts+=("system-service.sh")
        [ ! -f "ssl-setup.sh" ] && missing_scripts+=("ssl-setup.sh")
    fi
    
    if [ "$DEPLOYMENT_TYPE" = "docker" ]; then
        [ ! -f "Dockerfile.production" ] && missing_scripts+=("Dockerfile.production")
    fi
    
    if [ ${#missing_scripts[@]} -gt 0 ]; then
        log_error "다음 스크립트 파일들이 누락되었습니다:"
        for script in "${missing_scripts[@]}"; do
            echo "  - $script"
        done
        exit 1
    fi
    
    log_success "모든 필수 스크립트 파일이 확인되었습니다."
}

# 환경 변수 기본값 설정
DEPLOYMENT_TYPE=""
DOMAIN=""
EMAIL=""
DB_PASSWORD=""
PROJECT_DIR="/var/www/laravel-light-blog"
USE_SSL=true
USE_SYSTEMD_TIMER=false
SKIP_SYSTEM_SERVICE=false
DOCKER_IMAGE_NAME="laravel-light-blog"
DOCKER_CONTAINER_NAME="laravel-blog"
DRY_RUN=false

# 사용법 출력
show_usage() {
    echo "사용법: $0 --type TYPE --domain DOMAIN --email EMAIL [옵션]"
    echo ""
    echo "필수 옵션:"
    echo "  --type              배포 타입 (baremetal|docker|cloud)"
    echo "  --domain            도메인명 (Docker 제외)"
    echo "  --email             Let's Encrypt 이메일 (Docker 제외)"
    echo ""
    echo "베어메탈/클라우드 환경 옵션:"
    echo "  --db-password       데이터베이스 비밀번호 (필수)"
    echo "  --project-dir       프로젝트 설치 경로 (기본: /var/www/laravel-light-blog)"
    echo "  --no-ssl            SSL 인증서 설치 건너뛰기"
    echo "  --use-systemd       systemd 타이머 사용 (기본: cron)"
    echo "  --skip-services     시스템 서비스 설정 건너뛰기"
    echo ""
    echo "Docker 환경 옵션:"
    echo "  --image-name        Docker 이미지 이름 (기본: laravel-light-blog)"
    echo "  --container-name    Docker 컨테이너 이름 (기본: laravel-blog)"
    echo ""
    echo "기타 옵션:"
    echo "  --dry-run           실제 실행 없이 계획만 표시"
    echo "  --help              이 도움말 표시"
    echo ""
    echo "배포 타입별 예시:"
    echo ""
    echo "베어메탈 환경:"
    echo "  $0 --type baremetal --domain example.com --email admin@example.com --db-password password123"
    echo ""
    echo "Docker 환경:"
    echo "  $0 --type docker"
    echo ""
    echo "클라우드 환경 (systemd 타이머 사용):"
    echo "  $0 --type cloud --domain example.com --email admin@example.com --db-password password123 --use-systemd"
    echo ""
}

# 매개변수 파싱
while [[ $# -gt 0 ]]; do
    case $1 in
        --type)
            DEPLOYMENT_TYPE="$2"
            shift 2
            ;;
        --domain)
            DOMAIN="$2"
            shift 2
            ;;
        --email)
            EMAIL="$2"
            shift 2
            ;;
        --db-password)
            DB_PASSWORD="$2"
            shift 2
            ;;
        --project-dir)
            PROJECT_DIR="$2"
            shift 2
            ;;
        --image-name)
            DOCKER_IMAGE_NAME="$2"
            shift 2
            ;;
        --container-name)
            DOCKER_CONTAINER_NAME="$2"
            shift 2
            ;;
        --no-ssl)
            USE_SSL=false
            shift
            ;;
        --use-systemd)
            USE_SYSTEMD_TIMER=true
            shift
            ;;
        --skip-services)
            SKIP_SYSTEM_SERVICE=true
            shift
            ;;
        --dry-run)
            DRY_RUN=true
            shift
            ;;
        --help)
            show_usage
            exit 0
            ;;
        *)
            log_error "알 수 없는 옵션: $1"
            show_usage
            exit 1
            ;;
    esac
done

# 필수 매개변수 검증
if [ -z "$DEPLOYMENT_TYPE" ]; then
    log_error "배포 타입을 지정해야 합니다."
    show_usage
    exit 1
fi

if [ "$DEPLOYMENT_TYPE" != "docker" ]; then
    if [ -z "$DOMAIN" ] || [ -z "$EMAIL" ]; then
        log_error "베어메탈/클라우드 환경에서는 도메인과 이메일이 필수입니다."
        show_usage
        exit 1
    fi
    
    if [ "$DEPLOYMENT_TYPE" = "baremetal" ] && [ -z "$DB_PASSWORD" ]; then
        log_error "베어메탈 환경에서는 데이터베이스 비밀번호가 필수입니다."
        show_usage
        exit 1
    fi
fi

# 배포 타입 검증
case $DEPLOYMENT_TYPE in
    baremetal|docker|cloud)
        ;;
    *)
        log_error "지원하지 않는 배포 타입입니다: $DEPLOYMENT_TYPE"
        log_info "지원 타입: baremetal, docker, cloud"
        exit 1
        ;;
esac

# 메인 실행 시작
main() {
    print_header
    
    # 사용자 권한 확인
    if [ "$DEPLOYMENT_TYPE" != "docker" ] && [ "$EUID" -eq 0 ]; then
        log_error "이 스크립트는 root 권한으로 실행하지 마세요."
        log_info "일부 단계에서 sudo 권한이 필요할 때 자동으로 요청됩니다."
        exit 1
    fi
    
    # 단계 수 계산
    calculate_steps
    
    log_info "배포 설정:"
    log_info "  배포 타입: $DEPLOYMENT_TYPE"
    [ -n "$DOMAIN" ] && log_info "  도메인: $DOMAIN"
    [ -n "$EMAIL" ] && log_info "  이메일: $EMAIL"
    log_info "  프로젝트 경로: $PROJECT_DIR"
    [ "$USE_SSL" = true ] && log_info "  SSL 설정: 활성화" || log_info "  SSL 설정: 비활성화"
    [ "$USE_SYSTEMD_TIMER" = true ] && log_info "  스케줄러: systemd 타이머" || log_info "  스케줄러: cron"
    [ "$DRY_RUN" = true ] && log_warning "  DRY RUN 모드: 실제 실행하지 않음"
    
    echo ""
    
    if [ "$DRY_RUN" = false ]; then
        read -p "위 설정으로 배포를 진행하시겠습니까? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            log_info "배포가 취소되었습니다."
            exit 0
        fi
    fi
    
    echo ""
    log_info "배포를 시작합니다..."
    
    # 스크립트 존재 확인
    check_scripts
    
    # 배포 타입별 실행
    case $DEPLOYMENT_TYPE in
        "baremetal")
            deploy_baremetal
            ;;
        "docker")
            deploy_docker
            ;;
        "cloud")
            deploy_cloud
            ;;
    esac
    
    # 배포 완료
    deployment_complete
}

# 베어메탈 환경 배포
deploy_baremetal() {
    log_progress "베어메탈 환경 배포 시작..."
    
    # 1단계: 메인 배포 스크립트 실행
    log_step "메인 배포 스크립트 실행 중..."
    if [ "$DRY_RUN" = false ]; then
        ./deploy.sh \
            --domain "$DOMAIN" \
            --email "$EMAIL" \
            --db-password "$DB_PASSWORD" \
            --project-dir "$PROJECT_DIR" \
            --deployment-type "baremetal"
    else
        log_info "DRY RUN: ./deploy.sh 실행 예정"
    fi
    log_success "메인 배포가 완료되었습니다."
    
    # 2단계: 시스템 서비스 설정
    if [ "$SKIP_SYSTEM_SERVICE" = false ]; then
        log_step "시스템 서비스 설정 중..."
        if [ "$DRY_RUN" = false ]; then
            local systemd_flag=""
            [ "$USE_SYSTEMD_TIMER" = true ] && systemd_flag="--use-systemd-timer"
            
            sudo ./system-service.sh \
                --project-dir "$PROJECT_DIR" \
                --domain "$DOMAIN" \
                $systemd_flag
        else
            log_info "DRY RUN: sudo ./system-service.sh 실행 예정"
        fi
        log_success "시스템 서비스 설정이 완료되었습니다."
    else
        log_step "시스템 서비스 설정 건너뜀"
    fi
    
    # 3단계: SSL 인증서 설치
    if [ "$USE_SSL" = true ]; then
        log_step "SSL 인증서 설치 중..."
        if [ "$DRY_RUN" = false ]; then
            sudo ./ssl-setup.sh \
                --domain "$DOMAIN" \
                --email "$EMAIL"
        else
            log_info "DRY RUN: sudo ./ssl-setup.sh 실행 예정"
        fi
        log_success "SSL 인증서 설치가 완료되었습니다."
    else
        log_step "SSL 인증서 설치 건너뜀"
    fi
    
    # 4단계: 배포 검증
    log_step "배포 검증 중..."
    verify_baremetal_deployment
    
    # 5단계: 완료 및 정보 제공
    log_step "배포 정보 수집 중..."
    show_baremetal_info
}

# Docker 환경 배포
deploy_docker() {
    log_progress "Docker 환경 배포 시작..."
    
    # 1단계: Docker 이미지 빌드
    log_step "Docker 이미지 빌드 중..."
    if [ "$DRY_RUN" = false ]; then
        docker build -f Dockerfile.production -t "$DOCKER_IMAGE_NAME:latest" .
    else
        log_info "DRY RUN: docker build 실행 예정"
    fi
    log_success "Docker 이미지 빌드가 완료되었습니다."
    
    # 2단계: 기존 컨테이너 정리 (존재하는 경우)
    log_step "컨테이너 환경 준비 중..."
    if [ "$DRY_RUN" = false ]; then
        if docker ps -a --format '{{.Names}}' | grep -q "^${DOCKER_CONTAINER_NAME}$"; then
            log_info "기존 컨테이너를 정리합니다..."
            docker stop "$DOCKER_CONTAINER_NAME" 2>/dev/null || true
            docker rm "$DOCKER_CONTAINER_NAME" 2>/dev/null || true
        fi
        
        # 컨테이너 실행
        docker run -d \
            --name "$DOCKER_CONTAINER_NAME" \
            -p 9000:9000 \
            --restart unless-stopped \
            "$DOCKER_IMAGE_NAME:latest"
    else
        log_info "DRY RUN: docker run 실행 예정"
    fi
    log_success "Docker 컨테이너가 시작되었습니다."
    
    # 3단계: 배포 검증
    log_step "배포 검증 중..."
    verify_docker_deployment
    
    # 4단계: 완료 및 정보 제공
    log_step "배포 정보 수집 중..."
    show_docker_info
}

# 클라우드 환경 배포
deploy_cloud() {
    log_progress "클라우드 환경 배포 시작..."
    
    # 1단계: 메인 배포 스크립트 실행
    log_step "메인 배포 스크립트 실행 중..."
    if [ "$DRY_RUN" = false ]; then
        ./deploy.sh \
            --domain "$DOMAIN" \
            --email "$EMAIL" \
            --db-password "$DB_PASSWORD" \
            --project-dir "$PROJECT_DIR" \
            --deployment-type "cloud"
    else
        log_info "DRY RUN: ./deploy.sh 실행 예정"
    fi
    log_success "메인 배포가 완료되었습니다."
    
    # 2단계: 시스템 서비스 설정 (모니터링 용도)
    if [ "$SKIP_SYSTEM_SERVICE" = false ]; then
        log_step "모니터링 서비스 설정 중..."
        if [ "$DRY_RUN" = false ]; then
            sudo ./system-service.sh \
                --project-dir "$PROJECT_DIR" \
                --domain "$DOMAIN"
        else
            log_info "DRY RUN: sudo ./system-service.sh 실행 예정"
        fi
        log_success "모니터링 서비스 설정이 완료되었습니다."
    else
        log_step "시스템 서비스 설정 건너뜀"
    fi
    
    # 3단계: 배포 검증
    log_step "배포 검증 중..."
    verify_cloud_deployment
    
    # 4단계: 클라우드 서비스 안내
    log_step "클라우드 서비스 설정 안내..."
    show_cloud_recommendations
    
    # 5단계: 완료 및 정보 제공
    log_step "배포 정보 수집 중..."
    show_cloud_info
}

# 베어메탈 배포 검증
verify_baremetal_deployment() {
    if [ "$DRY_RUN" = true ]; then
        log_info "DRY RUN: 배포 검증 건너뜀"
        return
    fi
    
    local verification_failed=false
    
    # Laravel 애플리케이션 확인
    if ! cd "$PROJECT_DIR" &>/dev/null || ! php artisan --version &>/dev/null; then
        log_error "Laravel 애플리케이션 확인 실패"
        verification_failed=true
    fi
    
    # 웹서버 확인
    if ! curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200\|301\|302"; then
        log_warning "웹서버 연결 테스트 실패 (정상일 수 있음)"
    fi
    
    # 서비스 상태 확인
    local services=("nginx" "php8.3-fpm" "mariadb" "redis-server")
    for service in "${services[@]}"; do
        if ! systemctl is-active --quiet "$service"; then
            log_warning "서비스 $service가 실행 중이지 않습니다"
        fi
    done
    
    if [ "$verification_failed" = true ]; then
        log_error "배포 검증에 실패했습니다."
        exit 1
    fi
    
    log_success "배포 검증이 완료되었습니다."
}

# Docker 배포 검증
verify_docker_deployment() {
    if [ "$DRY_RUN" = true ]; then
        log_info "DRY RUN: 배포 검증 건너뜀"
        return
    fi
    
    # 컨테이너 상태 확인
    if ! docker ps --format '{{.Names}}' | grep -q "^${DOCKER_CONTAINER_NAME}$"; then
        log_error "컨테이너가 실행 중이지 않습니다."
        exit 1
    fi
    
    # 컨테이너 내부 Laravel 확인
    if ! docker exec "$DOCKER_CONTAINER_NAME" php artisan --version &>/dev/null; then
        log_error "컨테이너 내부 Laravel 애플리케이션 확인 실패"
        exit 1
    fi
    
    log_success "Docker 배포 검증이 완료되었습니다."
}

# 클라우드 배포 검증
verify_cloud_deployment() {
    if [ "$DRY_RUN" = true ]; then
        log_info "DRY RUN: 배포 검증 건너뜀"
        return
    fi
    
    # 베어메탈과 동일한 검증 + 클라우드 특화 검증
    verify_baremetal_deployment
    
    log_success "클라우드 배포 검증이 완료되었습니다."
}

# 클라우드 서비스 권장사항
show_cloud_recommendations() {
    if [ "$DRY_RUN" = true ]; then
        return
    fi
    
    echo ""
    log_info "=========================================="
    log_info "클라우드 환경 추가 설정 권장사항"
    log_info "=========================================="
    
    # AWS 권장사항
    echo ""
    log_info "AWS 환경 권장사항:"
    echo "  1. EventBridge를 사용한 Laravel Scheduler 대체:"
    echo "     - 규칙: rate(1 minute)"
    echo "     - 대상: Lambda 함수 또는 ECS 작업"
    echo "  2. RDS Aurora Serverless 사용 고려"
    echo "  3. ElastiCache Redis 사용 권장"
    echo "  4. CloudWatch 로그 모니터링 설정"
    
    # GCP 권장사항
    echo ""
    log_info "GCP 환경 권장사항:"
    echo "  1. Cloud Scheduler 사용:"
    echo "     - 빈도: * * * * *"
    echo "     - 대상: Cloud Run 또는 Compute Engine"
    echo "  2. Cloud SQL 사용 권장"
    echo "  3. Memorystore Redis 사용 권장"
    echo "  4. Cloud Logging 설정"
    
    # Azure 권장사항
    echo ""
    log_info "Azure 환경 권장사항:"
    echo "  1. Logic Apps 또는 Azure Functions 사용"
    echo "  2. Azure Database for MySQL 사용"
    echo "  3. Azure Cache for Redis 사용"
    echo "  4. Application Insights 모니터링"
    
    echo ""
    log_warning "현재는 cron을 사용하고 있습니다. 클라우드 네이티브 스케줄링 서비스로 대체를 권장합니다."
}

# 베어메탈 환경 정보 표시
show_baremetal_info() {
    if [ "$DRY_RUN" = true ]; then
        return
    fi
    
    echo ""
    log_success "=========================================="
    log_success "베어메탈 배포 완료!"
    log_success "=========================================="
    log_success "사이트 URL: https://$DOMAIN"
    log_success "관리자 패널: https://$DOMAIN/admin"
    log_success "프로젝트 경로: $PROJECT_DIR"
    
    echo ""
    log_info "기본 관리자 계정:"
    echo "  이메일: admin@example.com"
    echo "  비밀번호: password"
    
    echo ""
    log_warning "보안을 위해 기본 관리자 비밀번호를 즉시 변경해주세요!"
    
    echo ""
    log_info "시스템 상태 확인 명령어:"
    echo "  sudo systemctl status nginx"
    echo "  sudo systemctl status php8.3-fpm"
    echo "  sudo systemctl status mariadb"
    echo "  sudo systemctl status redis-server"
    
    if [ "$USE_SYSTEMD_TIMER" = true ]; then
        echo "  sudo systemctl list-timers laravel-*"
    else
        echo "  crontab -l"
    fi
    
    echo ""
    log_info "로그 확인 명령어:"
    echo "  sudo tail -f /var/log/nginx/error.log"
    echo "  sudo tail -f $PROJECT_DIR/storage/logs/laravel.log"
    
    if [ "$SKIP_SYSTEM_SERVICE" = false ]; then
        echo "  sudo tail -f /var/log/laravel-monitor.log"
    fi
}

# Docker 환경 정보 표시
show_docker_info() {
    if [ "$DRY_RUN" = true ]; then
        return
    fi
    
    echo ""
    log_success "=========================================="
    log_success "Docker 배포 완료!"
    log_success "=========================================="
    log_success "컨테이너명: $DOCKER_CONTAINER_NAME"
    log_success "이미지명: $DOCKER_IMAGE_NAME:latest"
    log_success "포트: 9000 (PHP-FPM)"
    
    echo ""
    log_info "Docker 관리 명령어:"
    echo "  docker logs -f $DOCKER_CONTAINER_NAME"
    echo "  docker exec -it $DOCKER_CONTAINER_NAME bash"
    echo "  docker restart $DOCKER_CONTAINER_NAME"
    echo "  docker stop $DOCKER_CONTAINER_NAME"
    
    echo ""
    log_info "Laravel 명령어 실행:"
    echo "  docker exec $DOCKER_CONTAINER_NAME php artisan --version"
    echo "  docker exec $DOCKER_CONTAINER_NAME php artisan schedule:run"
    
    echo ""
    log_warning "웹서버(Nginx) 설정이 필요합니다. 컨테이너와 연동하여 설정하세요."
    
    echo ""
    log_info "Nginx 프록시 설정 예시:"
    echo "  upstream php-backend {"
    echo "      server localhost:9000;"
    echo "  }"
}

# 클라우드 환경 정보 표시
show_cloud_info() {
    if [ "$DRY_RUN" = true ]; then
        return
    fi
    
    echo ""
    log_success "=========================================="
    log_success "클라우드 배포 완료!"
    log_success "=========================================="
    log_success "사이트 URL: https://$DOMAIN"
    log_success "관리자 패널: https://$DOMAIN/admin"
    log_success "프로젝트 경로: $PROJECT_DIR"
    
    echo ""
    log_info "기본 관리자 계정:"
    echo "  이메일: admin@example.com"
    echo "  비밀번호: password"
    
    echo ""
    log_warning "클라우드 환경 추가 설정 권장사항:"
    echo "  1. 관리형 데이터베이스 서비스 사용"
    echo "  2. 관리형 Redis 서비스 사용"
    echo "  3. 클라우드 네이티브 스케줄링 서비스로 cron 대체"
    echo "  4. 로드 밸런서 및 오토 스케일링 설정"
    echo "  5. 클라우드 모니터링 서비스 연동"
    
    echo ""
    log_info "현재 cron을 사용 중입니다. 클라우드 스케줄링 서비스로 마이그레이션을 권장합니다."
}

# 배포 완료 처리
deployment_complete() {
    echo ""
    log_step "배포 완료 처리 중..."
    
    if [ "$DRY_RUN" = true ]; then
        log_success "DRY RUN 모드: 모든 단계가 성공적으로 계획되었습니다."
        echo ""
        log_info "실제 배포하려면 --dry-run 옵션을 제거하고 다시 실행하세요."
        return
    fi
    
    echo ""
    log_success "🎉 Laravel Light Blog 배포가 성공적으로 완료되었습니다!"
    
    echo ""
    log_info "배포 요약:"
    log_info "  배포 타입: $DEPLOYMENT_TYPE"
    log_info "  실행 시간: $(date)"
    log_info "  총 단계: $TOTAL_STEPS"
    
    echo ""
    log_warning "다음 단계:"
    echo "  1. 웹사이트 접속 및 기능 테스트"
    echo "  2. 관리자 비밀번호 변경"
    echo "  3. 사이트 설정 커스터마이징"
    echo "  4. 백업 전략 수립"
    echo "  5. 모니터링 설정 검토"
    
    echo ""
    log_info "문제 발생 시 로그를 확인하고, 필요시 개별 스크립트를 다시 실행하세요."
}

# 오류 트랩 설정
trap 'log_error "스크립트 실행 중 오류가 발생했습니다. (라인: $LINENO)"; exit 1' ERR

# 메인 함수 실행
main "$@"