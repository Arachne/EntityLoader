@ECHO OFF

call "vendor/bin/phpcs.bat" -p --standard=vendor/arachne/coding-style/ruleset.xml src
call "vendor/bin/phpcs.bat" -p --standard=vendor/arachne/coding-style/ruleset.xml --ignore=_temp tests
call "vendor/bin/codecept.bat" build
call "vendor/bin/codecept.bat" run
