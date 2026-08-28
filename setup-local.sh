#!/bin/bash
# ============================================
# BonusHub — Local Dev Setup Script
# ============================================
set -e

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_DIR"

echo "🚀 BonusHub Local Setup"
echo "========================"

# 1. Check Docker
echo ""
echo "📦 Checking Docker..."
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker not running or needs sudo."
    echo "   Try: sudo usermod -aG docker \$USER && newgrp docker"
    echo "   Or run this script with: sudo bash setup-local.sh"
    exit 1
fi
echo "✅ Docker OK"

# 2. Copy .env if not exists
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env created from .env.example"
else
    echo "✅ .env already exists"
fi

# 3. Update .env for local Docker dev
echo ""
echo "⚙️  Configuring .env for local Docker..."
sed -i 's/APP_ENV=local/APP_ENV=local/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=true/' .env
sed -i 's|APP_URL=http://localhost|APP_URL=http://localhost:8080|' .env
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=mysql/' .env
sed -i 's/# DB_PORT=3306/DB_PORT=3306/' .env
sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=bonushub/' .env
sed -i 's/# DB_USERNAME=root/DB_USERNAME=bonushub/' .env
sed -i 's/# DB_PASSWORD=/DB_PASSWORD=bonushub/' .env
echo "✅ .env configured for Docker MySQL"

# 4. Install composer dependencies via Docker
echo ""
echo "⏳ Installing PHP dependencies (composer install)..."
docker run --rm \
    -v "$(pwd):/app" \
    -w /app \
    composer:latest \
    install --ignore-platform-reqs
echo "✅ Composer install done"

# 5. Install npm dependencies
echo ""
echo "⏳ Installing Node dependencies..."
npm install
echo "✅ npm install done"

# 6. Generate app key
echo ""
echo "🔑 Generating Laravel app key..."
docker run --rm \
    -v "$(pwd):/app" \
    -w /app \
    --env-file .env \
    composer:latest \
    php artisan key:generate
echo "✅ App key generated"

# 7. Start Docker containers (Laravel Sail)
echo ""
echo "🐳 Starting Docker containers..."
./vendor/bin/sail up -d
echo "✅ Containers started"

# 8. Wait for MySQL to be ready
echo ""
echo "⏳ Waiting for MySQL to be ready..."
sleep 15

# 9. Run migrations
echo ""
echo "🗄️  Running migrations..."
./vendor/bin/sail artisan migrate --force
echo "✅ Migrations done"

# 10. Seed database (optional)
read -p "🌱 Run seeders? (y/N): " seed
if [[ "$seed" =~ ^[Yy]$ ]]; then
    ./vendor/bin/sail artisan db:seed --force
    echo "✅ Seeders done"
fi

# 11. Build frontend
echo ""
echo "🎨 Building frontend assets..."
./vendor/bin/sail npm run build
echo "✅ Frontend built"

echo ""
echo "============================================"
echo "🎉 Setup COMPLETE!"
echo "============================================"
echo ""
echo "🌐 App:     http://localhost:8080"
echo "🗄️  MySQL:   localhost:3306"
echo "📧 Mailpit: http://localhost:8025"
echo ""
echo "📋 Useful commands:"
echo "   ./vendor/bin/sail up -d      # Start containers"
echo "   ./vendor/bin/sail down       # Stop containers"
echo "   ./vendor/bin/sail artisan    # Run artisan commands"
echo "   ./vendor/bin/sail npm run dev # Dev mode (hot reload)"
echo "   ./vendor/bin/sail tinker     # Laravel tinker"
echo "   ./vendor/bin/sail logs       # View logs"
echo ""
