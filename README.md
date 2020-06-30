# Minigames app

This repo contains an app that have multiple minigames including chess and a games of thrones inspired snakes and ladders game.
 
### Prerequisites

This project is using laravel with vue

```
PHP >= 7.2.0 with composer
node.js >= 8.9 with npm
pdo_sqlite enabeled in php.ini
```

### Installing

Should be fairly simple to get up and running

```
create a mysql database called on port 3306: laravelMiniGames or change the .env to match your own server
```


```
composer install
npm run install
php artisan migrate --seed
php artisan key:generate
php artisan storage:link
```

### Run server

For simple local server host.

```
php artisan serve
```

### Dummy login
Multiple dummy login available if database was seeded

```
login: test@gmail.com
password: password
```
```
login: test@test.com
password: password
```
```
login: andreas@tollanes.com
password: password
```

### Possible isses
Some issues can happen while setting it up

```
no link between public and resources
solution: 
run "php artisan storage:link"
```

```
routes are not working
solution:
run "php artisan cache:clear" 
run "php artisan route:cache"
```


```
pusher messages does not get sent
solution:
disable ad blocker on the site
```
