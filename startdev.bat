@ECHO OFF
ECHO CREATE CONTAINER...
docker run -it -d -p 80:80 -p 443:443 -p 3306:3306 --name=dev -v %CD%:/var/www/html coagus/lamp:0.5
PING 127.0.0.1 -n 10 -w 1000 > NUL
ECHO CREATE DATA BASE...
docker exec -i dev mysql coagus_dev < %CD%\mdl\sql\createdb.sql
ECHO Finished!... go to http://localhost