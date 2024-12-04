#!/usr/bin/env python3

import click
import os
import subprocess
from dotenv import dotenv_values
from rich.console import Console

console = Console()
script_directory = os.path.dirname(__file__)
working_directory = os.path.abspath(os.path.join(script_directory, '..'))
os.chdir(working_directory)

configuration = dotenv_values('.env')

docker_compose_files = [
    'docker-compose.yaml'
]

docker_compose_files = ' -f '.join(docker_compose_files)
container_workdir = '/www'


@click.command()
@click.option('--service', default='php_bundle_3', help='Service on which you want to attach.')
@click.option('--command', default='"/bin/bash"', help='A command to execute when attaching to container.')
def attach(service, command):
    console.print(
        'Leaving host environment and attaching to service {}...'.format(service),
        width=80,
        style="yellow"
    )

    result = subprocess.run(
        'docker-compose -f {} ps -q {}'.format(docker_compose_files, service),
        stdout=subprocess.PIPE,
        shell=True
    )

    container = result.stdout.decode('utf-8').strip()

    if '' == container:
        console.print(
            'Service {} is not running, have you even started it?'.format(service),
            width=80,
            style="red"
        )
        exit(1)

    os.system('docker exec -w {} -it {} bash -c "{}"'.format(container_workdir, container, command))


if __name__ == '__main__':
    attach()
