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
rm *.gz
tar -czvf laravelMiniGames.tar.gz /laravelMiniGames_master'''
      }
    }

  }
}