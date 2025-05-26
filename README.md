1. Виконати docker compose up -d
2. Додати до hosts запис    127.0.0.1   7eminar.localhost
3. Зайти в контейнер php-fpm
4. Виконати cp .env.example .env
5. Змінити в .env ключі PUSHER_APP_ID, PUSHER_APP_KEY, PUSHER_APP_SECRET, SENTRY_LARAVEL_DSN
6. Виконати composer install
7. Виконати npm install
8. Виконати php artisan migrate
9. Виконати php artisan db:seed
10. Виконати php artisan es:index-comments
11. Виконати php artisan test
12. Запустити чергу php artisan queue:work

Потенційні проблеми
1. У випадку якщо якийсь контейнер не піднявся - можуть бути проблеми з портами. 
Достатньо замінити в docker-compose.yaml у відповідному контейнері в блоці "ports:" порт вказаний до :
Після цього знову виконати docker compose up -d
2. Може не працювати пошук через обмеження пам'яті (враховуючи конфігурацію малоімовірно).
Якщо диск зайнятий майже на 100% потрібно видалити зайві файли

Додаткова інформація
Ключі PUSHER_APP_ID, PUSHER_APP_KEY, PUSHER_APP_SECRET можна отримати після реєстрації на pusher.com
Потрібно створити застосунок (регіон eu). Після створення можна буде переглянути ключі у вкладці "App Keys"
