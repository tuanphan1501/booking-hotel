#!/usr/bin/env bash
# Release APK build in Docker (no local Flutter SDK needed).
# Output + full log: mobile/build/apk-out/. Gradle, pub and NDK are cached in named volumes.
# Usage: mobile/scripts/build-apk.sh [API_BASE_URL]
set -eo pipefail

API_BASE_URL="${1:-http://192.168.3.139:8000/api/v1}"
MOBILE_DIR="$(cd "$(dirname "$0")/.." && (pwd -W 2>/dev/null || pwd))"
OUT_DIR="$MOBILE_DIR/build/apk-out"

mkdir -p "$OUT_DIR"
docker rm -f vivustay-apk >/dev/null 2>&1 || true

MSYS_NO_PATHCONV=1 docker run --name vivustay-apk \
  -v "$MOBILE_DIR:/src" \
  -v "$OUT_DIR:/out" \
  -v vivustay-gradle:/root/.gradle \
  -v vivustay-pub-cache:/root/.pub-cache \
  -v vivustay-ndk:/opt/android-sdk-linux/ndk \
  ghcr.io/cirruslabs/flutter:stable sh -c "set -e
    mkdir /app && cd /src
    tar --exclude=./build --exclude=./.dart_tool -cf - . | tar -xf - -C /app
    cd /app
    # Docker Desktop has ~4 GB; the repo's 8 GB Gradle heap gets OOM-killed.
    sed -i 's/^org.gradle.jvmargs=.*/org.gradle.jvmargs=-Xmx2G -XX:MaxMetaspaceSize=512m/' android/gradle.properties
    flutter pub get
    flutter build apk --release --dart-define=API_BASE_URL=$API_BASE_URL
    cp build/app/outputs/flutter-apk/app-release.apk /out/vivustay-0.1.0.apk" \
  2>&1 | tee "$OUT_DIR/build.log"

ls -la "$OUT_DIR/vivustay-0.1.0.apk"
