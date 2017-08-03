pipeline {
  agent any

  stages {
    stage('Checkout') {
      steps {
        checkout scm
        sh 'rm -rf ./build/{logs,pdepend}'
        sh 'mkdir -p ./build/{logs,pdepend}'
        sh './bin/prepare_tests.sh'
      }
    }

    stage('Unit tests') {
      steps {
        sh './bin/run_tests.sh'
      }
    }
  }
}