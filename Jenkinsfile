pipeline {
  agent any
  stages {
    stage('Init') {
      steps {
        sh '''npm install
composer install
cp .env.example .env

'''
      }
    }

    stage('Deploy') {
      steps {
        sh '''cd ..
FILE=laravelMiniGames.tar.gz
if test -f "$FILE"; then
    rm laravelMiniGames.tar.gz
fi
tar -czvf laravelMiniGames.tar.gz /laravelMiniGames_master'''
      }
    }

  }
}