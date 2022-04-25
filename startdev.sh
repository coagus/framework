#!/bin/sh
echo "CREATE CONTAINER..."
docker run -it -d -p 8080:80 -p 8043:443 -p 8006:3306 --name=ventas -v "$(pwd)":/var/www/html coagus/lamp:0.5
sleep 10
echo "CREATE DATA BASE..."
docker exec -i ventas mysql coagus_dev < ./mdl/sql/createdb.sql
echo "Finished!... go to http://localhost:8080"