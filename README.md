# Diocesan API RESTFul

This repo must be installed/cloned from the docker container repo created for this project.

[Docker Container Repo](https://github.com/DiocesanInc/diocesan-docker-container) 

## Connect to the Backend Docker Container

Being into our **local** terminal (It doesn't matter the location) we could execute: 

```bash
docker exec -it diocesan-phpfpm bash
```

Being into the container, if you are not going to execute a command that requires the root permissions, we must to change to the user already created on this container **"me"**.

```bash
su me
cd /srv/api
```

## Dependencies Installation and Initial Configuration

0.- Validate that already have created the **.env** file copying and pasting the **.env.dist** file, and the **phpunit.xml** file copying and pasting the **phpunit.xml.dist** file. 

1.- Install the project libraries, being in the root of the backend project (the root directory of this repo):

```bash
composer install
```

*NOTE: After making the libraries installation you must be ensured that the phpunit.xml file wasn't override and if it was you should to copy the content from the **"phpunit.xml.dist"** and paste its content inside the **"phpunit.xml"** file.*

2.- Run the migrations to create the database structure:

2.1. Validate whether you need to execute migrations or not: 

```bash
bin/console doctrine:migrations:status

 == Configuration

    >> Name:                                               Application Migrations
    >> Database Driver:                                    pdo_pgsql
    >> Database Name:                                      diocesan_test
    >> Configuration Source:                               manually configured
    >> Version Table Name:                                 migration_versions
    >> Version Column Name:                                version
    >> Migrations Namespace:                               DoctrineMigrations
    >> Migrations Directory:                               /srv/api/src/Migrations
    >> Previous Version:                                   0
    >> Current Version:                                    2018-05-09 22:39:52 (20180509223952)
    >> Next Version:                                       Already at latest version
    >> Latest Version:                                     2018-05-09 22:39:52 (20180509223952)
    >> Executed Migrations:                                1
    >> Executed Unavailable Migrations:                    0
    >> Available Migrations:                               2
    >> New Migrations:                                     1
```

2.2. If you have some pending migrations, you could execute those migrations:

```bash
bin/console doctrine:migrations:migrate
```

Another thing to take into account should be the workflow for creating new migrations, the first step is to modify the entities you require with their proper @ORM annotations in order to make able to map those properties to doctrine. After those changes inside your entities already made we could execute the following commands.

This is for to be ensured about that all pending migrations are already executed before generate a new one and avoid duplications an therefore an error.
```bash
bin/console doctrine:migrations:migrate
```

Now you can generate a new migrations.
```bash
bin/console doctrine:migrations:diff
```

After generate a new migration you must execute it:
```bash
bin/console doctrine:migrations:migrate
```

If you cloned the repo in your local machine without the docker container, you must to have installed the PHP XDebug library.
Moreover you must to run the server executing the following command:

```bash
bin/console server:run
```

Other things you must have already installed are PostgreSQL, Elasticsearch and Redis and see their configurations inside the .env file.

Generate the SSH keys :

``` bash
$ mkdir -p config/jwt # For Symfony3+, no need of the -p option
$ openssl genrsa -out config/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

## PHPUnit and Unit Tests (Services and Other Methods)

## Behat and Feature Tests (Endpoints)

If you want to run all feature tests:
```bash
vendor/bin/behat
```

If you want to run a specific feature file:
```bash
vendor/bin/behat features/login.feature
```

If you want to run a specific scenario of a specific feature file:
```bash
vendor/bin/behat features/login.feature:18
```

### Coverage reporting

In order to perform a code coverage reporting it's necessary to install xdebug

then you can run:
```
bin/phpunit --coverage-html public/phpunit-report
``` 