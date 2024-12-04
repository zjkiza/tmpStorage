#!/usr/bin/env python3

import os
from dotenv import dotenv_values
from rich.console import Console
from rich.markdown import Markdown

console = Console()
script_directory = os.path.dirname(__file__)
working_directory = os.path.abspath(os.path.join(script_directory, '..'))
monorepo_directory = os.path.abspath(os.path.join(script_directory, '..', '..', '..', '..'))
os.chdir(working_directory)

configuration = dotenv_values('.env')

docker_compose_files = [
    'docker-compose.yaml'
]

docker_compose_files = ' -f '.join(docker_compose_files)

os.system('docker-compose -f {} up -d --build > /dev/null'.format(docker_compose_files))
