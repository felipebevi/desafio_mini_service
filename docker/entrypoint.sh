#!/bin/bash
set -e

# Criar projeto Laravel se não existir
if [ ! -f composer.json ]; then
    echo "🚀 Criando novo projeto Laravel..."
    composer create-project laravel/laravel:^11.0 temp-laravel
    shopt -s dotglob
    mv temp-laravel/* .
    rm -rf temp-laravel
    echo "⚙️  Configurando ambiente..."
    cp .env.example .env
    php artisan key:generate --no-interaction
fi

# Instalar dependências se necessário
if [ ! -d vendor ]; then
    echo "📦 Instalando dependências..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Criar banco de dados se necessário
if [ ! -f database/database.sqlite ]; then
    echo "🗄️  Criando banco de dados..."
    touch database/database.sqlite
fi

# Criar link simbólico para storage
echo "🔗 Criando link simbólico para storage..."
php artisan storage:link --force 2>/dev/null || true

# Iniciar servidor Laravel
echo "✅ Iniciando servidor Laravel..."
echo "🌐 Servidor disponível em http://localhost:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
