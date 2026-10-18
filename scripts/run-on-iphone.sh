#!/bin/bash
# Builds and installs the Vivu Stay app on an iPhone connected to this Mac, with the backend running in Docker.
#
# Usage (from the repository root, on macOS):  ./scripts/run-on-iphone.sh
#
# Steps:
#   1. Check Xcode, install Homebrew tools (Flutter, CocoaPods, Docker Desktop) when missing.
#   2. Start PostgreSQL, Redis, Mailpit and the Laravel backend in Docker (http://<mac>.local:8000).
#   3. Build the Flutter app and run it on the connected iPhone, pointed at this Mac's backend.
#
# Author: PH.Tuan · Created: 2026-10-19
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
info() { printf '\n\033[1;31m▶ %s\033[0m\n' "$1"; }
fail() { printf '\n\033[1;31m✖ %s\033[0m\n' "$1"; exit 1; }

[[ "$(uname)" == "Darwin" ]] || fail "This script must run on macOS (iOS apps can only be built with Xcode)."

info "1/5 Checking Xcode"
if ! xcode-select -p >/dev/null 2>&1 || [[ ! -d /Applications/Xcode.app ]]; then
  open "macappstores://apps.apple.com/app/xcode/id497799835"
  fail "Install Xcode from the App Store (opened), launch it once, then run this script again."
fi
sudo xcode-select --switch /Applications/Xcode.app/Contents/Developer
sudo xcodebuild -license accept
xcodebuild -runFirstLaunch >/dev/null 2>&1 || true

info "2/5 Installing developer tools with Homebrew (Flutter, CocoaPods, Docker)"
if ! command -v brew >/dev/null 2>&1; then
  fail "Install Homebrew first: https://brew.sh — then run this script again."
fi
command -v flutter >/dev/null 2>&1 || brew install --cask flutter
command -v pod >/dev/null 2>&1 || brew install cocoapods
if ! command -v docker >/dev/null 2>&1; then
  brew install --cask docker
fi
open -a Docker
until docker info >/dev/null 2>&1; do printf '.'; sleep 2; done

info "3/5 Starting the backend in Docker"
cd "$REPO_ROOT"
docker compose --profile app up -d --build --wait
MAC_HOST="$(scutil --get LocalHostName).local"
API_BASE_URL="http://${MAC_HOST}:8000/api/v1"
until curl -fs "http://localhost:8000/up" >/dev/null; do printf '.'; sleep 2; done
echo "Backend: http://${MAC_HOST}:8000/admin  ·  API: ${API_BASE_URL}"

info "4/5 Preparing the Flutter project"
cd "$REPO_ROOT/mobile"
flutter pub get
(cd ios && pod install --repo-update)

if ! grep -q "DEVELOPMENT_TEAM" ios/Runner.xcodeproj/project.pbxproj; then
  open ios/Runner.xcworkspace
  cat <<'EOF'

First run only — set up code signing in Xcode (just opened):
  1. Select "Runner" (left) › tab "Signing & Capabilities".
  2. Tick "Automatically manage signing", choose your Apple ID as Team
     (Xcode › Settings › Accounts › "+" if it is not listed).
  3. If the bundle ID is taken, change it, e.g. vn.vivustay.vivuStay.<yourname>.
Then run this script again.
EOF
  exit 0
fi

info "5/5 Running on the iPhone"
DEVICE_ID="$(flutter devices --machine | python3 -c 'import json,sys; d=[x for x in json.load(sys.stdin) if x.get("targetPlatform")=="ios" and not x.get("emulator")]; print(d[0]["id"] if d else "")')"
[[ -n "$DEVICE_ID" ]] || fail "No iPhone found. Unlock it, tap \"Trust This Computer\", enable Settings › Privacy & Security › Developer Mode, then retry."

echo "iPhone and Mac must be on the same Wi-Fi. When iOS asks for Local Network access, tap Allow."
flutter run -d "$DEVICE_ID" --dart-define=API_BASE_URL="$API_BASE_URL"
