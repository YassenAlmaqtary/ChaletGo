# ChaletGo Mobile App

Flutter client for the ChaletGo platform built with GetX, Dio, and a modular MVC-inspired structure.

## Prerequisites

- Flutter SDK 3.5.x or newer
- Android/iOS tooling as needed
- Windows users: enable **Developer Mode** (required for plugin symlinks)

## Environment setup

`.env` is included with defaults—adjust as required:

```
API_BASE_URL=http://127.0.0.1:8000/api
DEFAULT_LOCALE=ar
```

## Install dependencies

```powershell
cd mobile_app
flutter pub get
```

## Run the app

```powershell
flutter run
```

## Code quality & tests

```powershell
flutter analyze
flutter test
```

## Project structure

- `lib/core` – bindings, config, services, theming
- `lib/data` – models and API providers
- `lib/modules` – feature modules (auth, chalets, splash)
- `lib/routes` – GetX routing definitions
- `lib/widgets` – reusable UI components

Extend the feature modules to cover bookings, payments, reviews, and owner/admin dashboards following the same pattern.
