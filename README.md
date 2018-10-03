## INSTALL
composer install
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate

## RUN
bin/console chat:server:start
bin/console server:run
