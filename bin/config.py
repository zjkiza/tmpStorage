container_php = 'php_bundle_3'
container_db = 'mysql_bundle_3'

waiting_db_connection = True
phpunit_code_error_bypass = False

containers = [
    container_php,
    container_db
]

container_work_dir = '/www'

docker_compose_files_list = [
    'docker-compose.yaml'
]

commands = {
    'composer install': 'composer install',
    'composer run phpunit': 'XDEBUG_MODE=coverage composer run phpunit',
    'composer run phpstan': 'composer run phpstan',
    'composer run psalm': 'composer run psalm',
    'composer run phpmd': 'composer run phpmd',
}
