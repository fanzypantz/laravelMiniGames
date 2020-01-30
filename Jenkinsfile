pipeline {
  agent any
  stages {
    stage('Init') {
      steps {
        sh '''composer install
cp .env.example .env
php artisan key:generate

'''
      }
    }

    stage('Prepare Zip') {
      steps {
        sh '''cd ..
cp -a laravelMiniGames_master/. /var/www/laravel/personal/laravelMiniGame'''
      }
    }

    stage('Deploy') {
      steps {
        sh 'php -v'
      }
    }

  }
}