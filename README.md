# Laravel Inertia Chat

## Overview

Laravel Inertia Chat is a web application built on top of laravel 10, inertia vue, laravel websocket:

## INSTALLATION

-   Clone the repo

-   Go into the repo

-   run below commands in terminal

```sh
composer install
php artisan key:generate
cpy .env.example .env
npm install
```

-   Create database

-   Put Database connection inside .env file

-   Set BROADCAST_DRIVER in .env to "pusher"

-   Set in .env the below Settings:

```code
PUSHER_APP_ID=chatroom
PUSHER_APP_KEY=chatroom_key
PUSHER_APP_SECRET=chatroom_secret
PUSHER_HOST=127.0.0.1
# PUSHER_PORT=443
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
```

-   Run the below commands in your terminal

```sh
php artisan migrate
php artisan serve
php artisan websockets:serve
npm run dev
```

-   Register in multiple browser with multiple users

-   Login in different browsers under different users

-   ENJOY THE CHAT APP!
