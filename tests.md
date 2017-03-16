How to run tests
====

```
# install php-cs-fixer and phpstan
composer tools

# go to the project's root directory, but NOT the tests subdirectory 
cd <project_dir>

# install dependencies
composer update

# fix coding style
composer fix

# static analysis
composer analyse

# run tests
composer test
```

Advanced usage
----

You can use these commands to do more specific tasks.

```
# generate necessary files to run the tests
./vendor/bin/codecept build

# run all tests
./vendor/bin/codecept run

# run the specific suite
./vendor/bin/codecept run <suite>

# run specific test
./vendor/bin/codecept run <file>
```
