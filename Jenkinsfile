node {
    def app

    stage('Clone repository') {
        checkout scm
    }

    stage('Build image') {
        app = docker.build("composer")
    }

    stage('Prepare tests') {
        app.inside {
            sh 'rm -rf ./build/{logs,pdepend}'
            sh 'mkdir -p ./build/{logs,pdepend}'
            sh './bin/prepare_tests.sh'
        }
    }

    stage('Run tests') {
        app.inside {
            sh './bin/run_tests.sh'
        }
    }
}