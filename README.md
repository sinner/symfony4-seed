Generate the SSH keys :

``` bash
$ mkdir -p config/jwt # For Symfony3+, no need of the -p option
$ openssl genrsa -out config/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

## coverage reporting

In order to perform a code coverage reporting it's necessary to install xdebug

then you can run:
```
bin/phpunit --coverage-html public/phpunit-report
``` 