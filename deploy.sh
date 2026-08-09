#!/bin/bash
# ============================================================
# MHC Smart School - Docker Deploy Script
# Jalankan di server CasaOS: bash deploy.sh
# ============================================================

set -e

echo "🏫 MHC Smart School - Docker Deployment"
echo "========================================"

# 1. Salin .env.docker ke .env (jika belum ada .env)
if [ ! -f ".env" ]; then
    echo "📝 Membuat .env dari template .env.docker..."
    cp .env.docker .env
fi

# 2. Generate APP_KEY jika belum ada
if grep -q "APP_KEY=$" .env; then
    echo "🔑 Generate APP_KEY..."
    docker run --rm -v $(pwd):/app -w /app php:8.2-cli php artisan key:generate --force
fi

# 3. Build & start containers
echo "🐳 Build dan start Docker containers..."
docker-compose up -d --build

# 4. Tunggu MySQL ready
echo "⏳ Menunggu MySQL siap..."
sleep 15

# 5. Jalankan migrations dan seeder
echo "🗄️  Menjalankan database migrations..."
docker exec smartschool_app php artisan migrate --force

echo "🌱 Menjalankan database seeder..."
docker exec smartschool_app php artisan db:seed --force

# 6. Optimize Laravel untuk production
echo "⚡ Optimasi Laravel production..."
docker exec smartschool_app php artisan config:cache
docker exec smartschool_app php artisan route:cache
docker exec smartschool_app php artisan view:cache

# 7. Fix storage permissions
echo "📁 Fix storage permissions..."
docker exec smartschool_app chmod -R 775 storage bootstrap/cache
docker exec smartschool_app chown -R www-data:www-data storage bootstrap/cache

echo ""
echo "✅ Deployment selesai!"
echo "========================================"
echo "🌐 Akses aplikasi di: http://$(hostname -I | awk '{print $1}'):8590"
echo "🔑 Login admin: username=admin / password=password"
echo "🗄️  MySQL port: 33307 (untuk phpMyAdmin)"
echo ""
echo "📋 Useful commands:"
echo "  docker logs smartschool_app    # lihat log aplikasi"
echo "  docker exec -it smartschool_app bash  # masuk container"
echo "  docker-compose restart         # restart semua"
echo "  docker-compose down            # stop semua"
