# bibliotek
web application for library management
exam project for "Programmazione per il web" course, Information Engineering, UnivAq

## install requirements

`composer install`

### or install with docker

`docker run --rm -it -v "$(pwd):/app" composer/composer install`

## generate database schema

ref: https://www.doctrine-project.org/projects/doctrine-orm/en/3.2/tutorials/getting-started.html

`php bin/doctrine orm:schema-tool:create`

### to drop entire database

`php bin/doctrine orm:schema-tool:drop --force`

## local testing

`docker compose up -d`

now visits http://localhost:8080/