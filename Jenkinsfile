pipeline {
    agent {
        docker 'composer'
    }
    triggers {
        pollSCM 'H/5 * * * *'
    }
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        stage('Build') {
            steps {
                sh 'rm -rf ./build/{logs,pdepend} 2> /dev/null'
                sh 'mkdir -p ./build/{logs,pdepend}'
                sh 'chmod +x ./bin/*.sh'
                sh 'COMPOSER_HOME=/tmp/.composer ./bin/prepare_tests.sh'
            }
        }
        stage('Run') {
            steps {
                sh './bin/run_tests.sh'
            }
        }
    }
    post {
        always {
            deleteDir() /* clean up our workspace */
        }
    }
}