node {
    stage("Unit testing") {
        checkout scm

        docker.image('composer').inside {
            stage("Prepare") {
                sh 'rm -rf ./build/{logs,pdepend} 2> /dev/null'
                sh 'mkdir -p ./build/{logs,pdepend}'
                sh 'chmod +x ./bin/*.sh'
                sh 'COMPOSER_HOME=/tmp/.composer ./bin/prepare_tests.sh'
            }

            stage("Run tests") {
                sh './bin/run_tests.sh'
            }
        }
    }

    // Clean up workspace
    step([$class: 'WsCleanup'])
}