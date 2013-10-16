@ECHO OFF

call "vendor/bin/phpcs.bat" -p --standard=vendor/arachne/coding-style/ruleset.xml src
call "vendor/bin/phpcs.bat" -p --standard=vendor/arachne/coding-style/ruleset.xml tests/unit
call "vendor/bin/phpcs.bat" -p --standard=vendor/arachne/coding-style/ruleset.xml tests/integration
call "vendor/bin/codecept.bat" build
call "vendor/bin/codecept.bat" run
