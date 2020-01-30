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
FILE=laravelMiniGames.tar.gz
if test -f "$FILE"; then
    rm laravelMiniGames.tar.gz
fi
sudo tar -czvf laravelMiniGames.tar.gz --exclude=\'node_modules\' --exclude=\'vendor\' laravelMiniGames_master/.
sudo mv laravelMiniGames.tar.gz /var/www/laravel/personal/laravelMiniGame/laravelMiniGames.tar.gz
'''
      }
    }

    stage('Deploy') {
      steps {
        sh '''cd /var/www/laravel/personal/laravelMiniGame
sudo tar -xzvf laravelMiniGames.tar.gz
sudo rm laravelMiniGames.tar.gz

'''
      }
    }

  }
}