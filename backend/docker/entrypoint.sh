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
# Values are quoted so passwords with spaces (Gmail app passwords) stay intact.
for name in DB_HOST DB_PORT REDIS_HOST APP_URL MAIL_HOST MAIL_PORT MAIL_SCHEME MAIL_USERNAME MAIL_PASSWORD \
    MAIL_FROM_ADDRESS GOOGLE_CLIENT_ID GOOGLE_CLIENT_SECRET; do
    value=$(printenv "$name" || true)
    if [ -n "$value" ]; then
        sed -i "/^$name=/d" .env
        printf '%s="%s"\n' "$name" "$value" >> .env
    fi
done

php artisan storage:link --force >/dev/null 2>&1 || true
php artisan migrate --force

if [ "$(php artisan tinker --execute='echo \App\Models\AdminUser::query()->count();' 2>/dev/null | tail -n 1)" = "0" ]; then
    php artisan db:seed --force
fi

exec php artisan serve --host=0.0.0.0 --port=8000
