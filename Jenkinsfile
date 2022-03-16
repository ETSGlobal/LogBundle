@Library('etsglobal@master') _

pipeline {
    agent none

    options {
        ansiColor 'xterm'
    }

    environment {
        GITHUB_CREDENTIALS = 'github-etsglobalbv-bot-token'
        DOCKER_REGISTRY = 'eu.gcr.io/etsglobal-management'
        DOCKER_REGISTRY_CREDENTIALS = 'gcr:etsglobal-management'
    }

    stages {
        stage('Test') {
            parallel {
                stage('PHPUnit: PHP 8.1 with latest dependencies') {
                    agent { label 'docker' }
                    steps {
                        script {
                            docker.withRegistry("https://${env.DOCKER_REGISTRY}", env.DOCKER_REGISTRY_CREDENTIALS) {
                                docker.image("${env.DOCKER_REGISTRY}/php:8.1.1-cli-dev").inside {
                                    sh "rm -Rf vendor/ composer.lock && composer install"
                                    sh "make unit-tests-ci"
                                }
                            }
                        }
                    }
                    post {
                        always {
                            script {
                                testResults.phpunit('build/reports/unit-tests.xml')
                            }
                        }
                    }
                }
                stage('Checkstyle') {
                    agent { label 'docker' }
                    steps {
                        script {
                            docker.withRegistry("https://${env.DOCKER_REGISTRY}", env.DOCKER_REGISTRY_CREDENTIALS) {
                                docker.image("${env.DOCKER_REGISTRY}/php:8.1.1-cli-dev").inside {
                                    sh "rm -Rf vendor/ composer.lock && composer install"
                                    sh "make phpcs-ci"
                                }
                            }
                        }
                    }
                    post {
                        always {
                            script {
                                sh "make phpcs-ci-report"
                                testResults.codesniffer('build/reports/phpcs.xml')
                            }
                        }
                    }
                }
                stage('Mess Detector') {
                    agent { label 'docker' }
                    steps {
                        script {
                            docker.withRegistry("https://${env.DOCKER_REGISTRY}", env.DOCKER_REGISTRY_CREDENTIALS) {
                                docker.image("${env.DOCKER_REGISTRY}/php:8.1.1-cli-dev").inside {
                                    sh "rm -Rf vendor/ composer.lock && composer install"
                                    sh "make phpmd-ci"
                                }
                            }
                        }
                    }
                    post {
                        always {
                            script {
                                sh "make phpmd-ci-report"
                                testResults.phpmd('build/reports/pmd.xml')
                            }
                        }
                    }
                }
                stage('Copy Paste Detector') {
                    agent { label 'docker' }
                    steps {
                        script {
                            docker.withRegistry("https://${env.DOCKER_REGISTRY}", env.DOCKER_REGISTRY_CREDENTIALS) {
                                docker.image("${env.DOCKER_REGISTRY}/php:8.1.1-cli-dev").inside {
                                    sh "rm -Rf vendor/ composer.lock && composer install"
                                    sh "make phpcpd-ci"
                                }
                            }
                        }
                    }
                    post {
                        always {
                            script {
                                sh "make phpcpd-ci-report"
                                testResults.phpcpd('build/reports/phpcpd.xml')
                            }
                        }
                    }
                }
                stage('PHPStan') {
                    agent { label 'docker' }
                    steps {
                        script {
                            docker.withRegistry("https://${env.DOCKER_REGISTRY}", env.DOCKER_REGISTRY_CREDENTIALS) {
                                docker.image("${env.DOCKER_REGISTRY}/php:8.1.1-cli-dev").inside {
                                    sh "rm -Rf vendor/ composer.lock && composer install"
                                    sh "make phpstan-ci"
                                }
                            }
                        }
                    }
                    post {
                        always {
                            script {
                                sh "make phpstan-ci-report"
                                testResults.phpstan('build/reports/phpstan.xml')
                            }
                        }
                    }
                }
            }
        }
    }
}
