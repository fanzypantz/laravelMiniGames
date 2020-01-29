pipeline {
  agent any
  stages {
    stage('Init') {
      steps {
        sh '''npm install
composer install
cp .env.example .env
php artisan key:generate'''
      }
    }

  }
}