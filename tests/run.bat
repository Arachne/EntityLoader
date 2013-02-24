@ECHO OFF

cd ..
call composer self-update --no-interaction --quiet
call composer install --no-interaction --quiet --dev --optimize-autoloader
cd tests

call "../vendor/bin/phpcs.bat" -p --standard=../vendor/arachne/coding-style/ruleset.xml ../src
call "../vendor/bin/phpunit.bat" --process-isolation --bootstrap unit/bootstrap.php unit
