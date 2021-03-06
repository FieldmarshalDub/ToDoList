ToDoList
=====================
Для запуска требуется docker

Клонируйте проект и установите модуль:
```bash
git submodule init
```
```bash
git submodule update
```
Выполните команду:
```
composer install
```
Установите ключ шифрования командой
```bash
php artisan key:generate
```
Войдите в папку laradock и переименуйте env-exampleв .env.:
```bash
cp env-example .env
```
Откройте .env файл папки laradock и установите следующее:
```
MYSQL_VERSION=8.0
MYSQL_DATABASE=app
MYSQL_USER=root
MYSQL_PASSWORD=root
MYSQL_PORT=3306
MYSQL_ROOT_PASSWORD=root
MYSQL_ENTRYPOINT_INITDB=./mysql/docker-entrypoint-initdb.d
```

Запустите необходимые контейнеры:
```bash
docker-compose up -d nginx mysql phpmyadmin workspace
```
Для запуска предварительно создайте базу данных app и выполните миграцию
```bash
docker-compose exec workspace bash
```
```bash
php artisan migrate
```


Для запуска тестов, в workspace используйте команду:
```bash
phpunit
```
