# Travel API with Test and API Documentation

## Install Project
- Install packages by running this command in project directory:
  ```
  composer install
  ```
- Setup `DB_*` database variables in `.ENV` file
- Run migration and seed:
  ```
  php artisan migrate --seed
  ```

## Generate API Documentation using Scribe
- Run this command to generate API doc for this API:
  ```
  php artisan scribe:generate
  ```

## Running Test
- Run this command to run test:
  ```
  php artisan test
  ```

## Code Style Fix (using Laravel Pint)
- Run this command to check code style:
  ```
  ./vendor/bin/pint --test
  ```
- Run this command to apply code style fixes:
  ```
  ./vendor/bin/pint
  ./vendor/bin/phpstan analyse
  ```

## Code Analyse (using LaraStan)
- Run this command to analyse code:
  ```
  ./vendor/bin/phpstan analyse
  ```