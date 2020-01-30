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

    stage('Copy Build') {
      steps {
        sh '''cd ..
sudo cp -a laravelMiniGames_master/. /var/www/laravel/personal/laravelMiniGame'''
      }
    }

    stage('Deploy') {
      steps {
        sh 'php -v'
      }
    }

  }
}