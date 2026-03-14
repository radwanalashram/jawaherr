# Invoicer - Laravel scaffold (MVP)

هذا المستودع يحتوي على scaffold مبدئي لتطبيق الفواتير/المخزون مبني على Laravel (PHP) وفق مواصفات MVP.

محتويات الحزمة:
- docker-compose.yml (Postgres + Adminer + Nginx + php-fpm)
- .env.example
- database/migrations/2026_03_14_000000_create_initial_schema.php
- database/seeders/DatabaseSeeder.php
- app/Models/* (User, Item, Invoice, InvoiceLine, StockMovement, ...)
- app/Http/Controllers/Api/* (AuthController, ItemController, InvoiceController)
- routes/api.php (نقاط نهاية API الأساسية)

تشغيل محلي سريع (بعد نسخ الملفات داخل مشروع Laravel):
1. انسخ .env.example إلى .env وعدّل القيم (DB_*)
2. composer install
3. php artisan key:generate
4. شغّل قاعدة البيانات (docker-compose up -d) أو تأكد أن Postgres متاح
5. php artisan migrate --seed
6. php artisan serve --host=0.0.0.0 --port=8000

نقطة دخول API (أمثلة):
- POST /api/auth/login { username, password }
- GET  /api/items
- POST /api/items
- POST /api/invoices { payload }
- GET  /api/invoices/{id}/print

ملاحظات:
- هذا scaffold مبدئي؛ طبقة التحقق من الصلاحيات، معاملات المخزون، قواعد العمل والتعامل مع الطباعة/إرسال الرسائل ستكمل لاحقاً.
- إن رغبت، أرفع الكود إلى مستودع GitHub وأعد CI/CD docker image. اذكر owner/name للمستودع إن رغبت بالرفع.
