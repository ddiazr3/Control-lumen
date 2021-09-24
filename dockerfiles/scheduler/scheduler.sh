#!/bin/bash

# Run scheduler
while [ true ]
do
  cd /var/www
  php artisan schedule:run --verbose --no-interaction
  sleep 60
done
