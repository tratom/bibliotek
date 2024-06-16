# bibliotek
web application for library management
exam project for "Programmazione per il web" course, Information Engineering, UnivAq

## install requirements

`composer install`

### or install with docker

`docker compose up -d`
`docker run --rm --network host -it -v "$(pwd):/app" composer/composer install`

## generate database schema

ref: https://www.doctrine-project.org/projects/doctrine-orm/en/3.2/tutorials/getting-started.html

`php bin/doctrine orm:schema-tool:create`

### to drop entire database

`php bin/doctrine orm:schema-tool:drop --force`


now visits http://localhost:8080/