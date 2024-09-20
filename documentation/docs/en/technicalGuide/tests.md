### Unit tests

Unit tests are written in PHP and use (PHPUnit)[https://phpunit.de/].
To run unit tests, execute the following command in the main project directory:

```sh
bash bin/unit.sh
```

### Mutation tests
The tests are written in PHP and use (Infection)[https://infection.github.io/guide/].
To run mutation tests, execute the following command in the main project directory:

```sh
bash bin/infection.sh
```

### All tests


To run all tests, execute the following command in the main project directory:

```sh
bash bin/test.sh
```

### Configuration
The mutation tests are configured in the `.infection.json5` file.
