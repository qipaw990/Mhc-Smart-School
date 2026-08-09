#!/bin/bash
# ============================================================
# MHC Smart School - Docker Deploy + Auto Git Pull Script
# Jalankan di server CasaOS: bash deploy.sh
# ============================================================

set -e

# ── Auto-detect docker compose command ─────────────────────
# Docker v2+ pakai 'docker compose' (spasi), v1 pakai 'docker-compose' (strip)
if docker compose version &>/dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
elif command -v docker-compose &>/dev/null; then
    DOCKER_COMPOSE="docker-compose"
else
    echo -e "\033[0;31m❌ Docker Compose tidak ditemukan! Install Docker Desktop atau docker-compose dulu.\033[0m"
    exit 1
fi

# ── Colors ─────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

echo -e "${BOLD}${CYAN}"
echo "╔══════════════════════════════════════════════╗"
echo "║   🏫  MHC Smart School — Docker Deploy       ║"
echo "║   SMK Muthia Harapan Cicalengka              ║"
echo "╚══════════════════════════════════════════════╝"
echo -e "${NC}"

# ── Step 0: Deteksi apakah deploy pertama atau update ──────
IS_FIRST_DEPLOY=false
if ! docker ps --format '{{.Names}}' 2>/dev/null | grep -q "smartschool_app"; then
    IS_FIRST_DEPLOY=true
    echo -e "${YELLOW}📦 Mode: DEPLOY PERTAMA${NC}"
else
    echo -e "${CYAN}🔄 Mode: UPDATE / REDEPLOY${NC}"
fi

echo ""

# ── Step 1: Git Pull ───────────────────────────────────────
echo -e "${BOLD}[1/7] 📥 Git Pull — Mengambil kode terbaru...${NC}"

if [ -d ".git" ]; then
    # Simpan perubahan lokal jika ada (stash)
    if ! git diff --quiet; then
        echo -e "  ${YELLOW}⚠️  Ada perubahan lokal, melakukan git stash...${NC}"
        git stash
        STASHED=true
    else
        STASHED=false
    fi

    # Fetch dan pull dari remote
    git fetch origin
    BEFORE_HASH=$(git rev-parse HEAD)
    git pull origin $(git rev-parse --abbrev-ref HEAD) --rebase
    AFTER_HASH=$(git rev-parse HEAD)

    if [ "$BEFORE_HASH" != "$AFTER_HASH" ]; then
        echo -e "  ${GREEN}✅ Kode berhasil diupdate!${NC}"
        echo -e "  ${CYAN}   Sebelum : ${BEFORE_HASH:0:8}${NC}"
        echo -e "  ${CYAN}   Sesudah : ${AFTER_HASH:0:8}${NC}"
        git log --oneline -3 | sed 's/^/     📝 /'
        CODE_CHANGED=true
    else
        echo -e "  ${GREEN}✅ Kode sudah up-to-date (tidak ada perubahan baru)${NC}"
        CODE_CHANGED=false
    fi

    # Kembalikan stash jika ada
    if [ "$STASHED" = true ]; then
        git stash pop 2>/dev/null || echo -e "  ${YELLOW}⚠️  Stash pop ada konflik, periksa manual${NC}"
    fi
else
    echo -e "  ${YELLOW}⚠️  Direktori ini bukan git repository. Lewati git pull.${NC}"
    CODE_CHANGED=true
fi

echo ""

# ── Step 2: Setup .env ────────────────────────────────────
echo -e "${BOLD}[2/7] 📝 Setup Environment...${NC}"

if [ ! -f ".env" ]; then
    if [ -f ".env.docker" ]; then
        cp .env.docker .env
        echo -e "  ${GREEN}✅ .env dibuat dari .env.docker${NC}"
    else
        echo -e "  ${RED}❌ .env.docker tidak ditemukan! Buat manual dulu.${NC}"
        exit 1
    fi
else
    echo -e "  ${GREEN}✅ .env sudah ada${NC}"
fi

echo ""

# ── Step 3: Build & Start Containers ─────────────────────
echo -e "${BOLD}[3/7] 🐳 Build & Start Docker Containers...${NC}"

if [ "$IS_FIRST_DEPLOY" = true ] || [ "$CODE_CHANGED" = true ]; then
    echo -e "  🔨 Building images (proses ini ±2-5 menit pertama kali)..."
    $DOCKER_COMPOSE up -d --build
    echo -e "  ${GREEN}✅ Containers berhasil dibangun dan dijalankan${NC}"
else
    echo -e "  ⏭️  Tidak ada perubahan kode, restart container saja..."
    $DOCKER_COMPOSE restart smartschool_app smartschool_nginx smartschool_queue
    echo -e "  ${GREEN}✅ Containers di-restart${NC}"
fi

echo ""

# ── Step 4: Tunggu Database ───────────────────────────────
echo -e "${BOLD}[4/7] ⏳ Menunggu MySQL siap...${NC}"

MAX_WAIT=60
WAITED=0
until docker exec smartschool_db mysqladmin ping -h localhost -u root -psmartschool_root_2026 --silent 2>/dev/null; do
    if [ $WAITED -ge $MAX_WAIT ]; then
        echo -e "  ${RED}❌ MySQL tidak siap setelah ${MAX_WAIT}s. Cek: docker logs smartschool_db${NC}"
        exit 1
    fi
    printf "  ."
    sleep 3
    WAITED=$((WAITED + 3))
done
echo -e "\n  ${GREEN}✅ MySQL siap! (menunggu ${WAITED}s)${NC}"

echo ""

# ── Step 5: Generate APP_KEY (jika pertama) ───────────────
echo -e "${BOLD}[5/7] 🔑 App Key & Artisan Commands...${NC}"

APP_KEY_VALUE=$(grep "^APP_KEY=" .env | cut -d '=' -f2)
if [ -z "$APP_KEY_VALUE" ] || [ "$APP_KEY_VALUE" = "" ]; then
    echo -e "  🔑 Generate APP_KEY..."
    docker exec smartschool_app php artisan key:generate --force
else
    echo -e "  ${GREEN}✅ APP_KEY sudah ada${NC}"
fi

# Migrate
echo -e "  🗄️  Menjalankan migrations..."
docker exec smartschool_app php artisan migrate --force
echo -e "  ${GREEN}✅ Migrations selesai${NC}"

# Seed hanya pada deploy pertama
if [ "$IS_FIRST_DEPLOY" = true ]; then
    echo -e "  🌱 Deploy pertama — menjalankan seeder..."
    docker exec smartschool_app php artisan db:seed --force
    echo -e "  ${GREEN}✅ Seeder selesai${NC}"
fi

echo ""

# ── Step 6: Optimize Production ──────────────────────────
echo -e "${BOLD}[6/7] ⚡ Optimasi Laravel Production...${NC}"

docker exec smartschool_app php artisan optimize:clear
docker exec smartschool_app php artisan config:cache
docker exec smartschool_app php artisan route:cache
docker exec smartschool_app php artisan view:cache
echo -e "  ${GREEN}✅ Cache config, route, dan view selesai${NC}"

echo ""

# ── Step 7: Fix Permissions ───────────────────────────────
echo -e "${BOLD}[7/7] 📁 Fix Storage Permissions...${NC}"
docker exec smartschool_app chmod -R 775 storage bootstrap/cache
docker exec smartschool_app chown -R www-data:www-data storage bootstrap/cache
echo -e "  ${GREEN}✅ Permissions sudah benar${NC}"

# ── Status Containers ─────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}"
echo "╔══════════════════════════════════════════════╗"
echo "║   ✅  DEPLOYMENT BERHASIL!                   ║"
echo "╚══════════════════════════════════════════════╝"
echo -e "${NC}"

SERVER_IP=$(hostname -I | awk '{print $1}')
echo -e "  🌐 ${BOLD}Akses Aplikasi : ${CYAN}http://${SERVER_IP}:8590${NC}"
echo -e "  🗄️  ${BOLD}MySQL Port     : ${CYAN}33307${NC}"
if [ "$IS_FIRST_DEPLOY" = true ]; then
echo -e "  🔑 ${BOLD}Login Admin    : ${CYAN}admin / password${NC}"
fi
echo ""
echo -e "${BOLD}📋 Status Containers:${NC}"
docker ps --format "  {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep smartschool || true
echo ""
echo -e "${BOLD}📋 Useful Commands:${NC}"
echo -e "  ${CYAN}docker logs smartschool_app -f${NC}       # live log aplikasi"
echo -e "  ${CYAN}docker exec -it smartschool_app bash${NC}  # masuk container"
echo -e "  ${CYAN}bash deploy.sh${NC}                       # update & redeploy"
echo ""

