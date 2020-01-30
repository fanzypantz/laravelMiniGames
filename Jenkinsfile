pipeline {
  agent any
  stages {
    stage('Init') {
      steps {
        sh '''php artisan key:generate
cp .env.example .env

'''
      }
    }

    stage('Prepare Zip') {
      steps {
        sh '''cd ..
FILE=laravelMiniGames.tar.gz
if test -f "$FILE"; then
    rm laravelMiniGames.tar.gz
fi
tar -czvf laravelMiniGames.tar.gz laravelMiniGames_master/ --exclude=/node_modules --exclude=/vendor
mv laravelMiniGames.tar.gz /var/www/laravel/personal/laravelMiniGame/laravelMiniGames.tar.gz'''
      }
    }

    stage('Deploy') {
      steps {
        sh '''cd /var/www/laravel/personal/laravelMiniGame
tar -xzvf laravelMiniGames.tar.gz
rm laravelMiniGames.tar.gz

'''
      }
    }

  }
}