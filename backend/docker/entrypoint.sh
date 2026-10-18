#!/bin/sh
# Starts the backend in the "app" Docker service.
#
# - Creates .env from .env.example on the first run and points it at the compose services.
# - Runs migrations; seeds demo data only when the database is empty.
# - Serves the app on 0.0.0.0:8000 so phones on the same Wi-Fi can reach it.
#
# Author: PH.Tuan · Created: 2026-10-19
set -e

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

# `php artisan serve` does not pass container environment variables to the web workers,
# so write the compose service addresses into .env.
for name in DB_HOST DB_PORT REDIS_HOST MAIL_HOST APP_URL; do
    value=$(printenv "$name" || true)
    if [ -n "$value" ]; then
        if grep -q "^$name=" .env; then
            sed -i "s#^$name=.*#$name=$value#" .env
        else
            echo "$name=$value" >> .env
        fi
    fi
done

php artisan storage:link --force >/dev/null 2>&1 || true
php artisan migrate --force

if [ "$(php artisan tinker --execute='echo \App\Models\AdminUser::query()->count();' 2>/dev/null | tail -n 1)" = "0" ]; then
    php artisan db:seed --force
fi

exec php artisan serve --host=0.0.0.0 --port=8000
