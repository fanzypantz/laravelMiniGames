pipeline {
  agent any
  stages {
    stage('Init') {
      steps {
        sh '''composer install
npm install
cp .env.example .env
php artisan key:generate

'''
      }
    }

    stage('Copy Build') {
      steps {
        sh '''cd ..
sudo rm -rf /var/www/laravel/personal/laravelMiniGame/*
sudo cp -a laravelMiniGames_master/. /var/www/laravel/personal/laravelMiniGame'''
      }
    }

    stage('Deploy') {
      steps {
        sh '''sudo chown -R www-data:www-data /var/www
sudo service apache2 reload
cd /var/www/laravel/personal/laravelMiniGame
php artisan migrate'''
      }
    }

  }
}