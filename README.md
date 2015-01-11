Deep-Skies Project
========================

Implementation based on Symfony2

Install notes:

1) Install vendors and configure mysql connection:
------------------------

    $ composer install

2) Ensure the cache and logs directories are writable by the web server:
------------------------

    $ sudo chmod -R 777 app/cache
    $ sudo chmod -R 777 app/logs

3) Create the database:
------------------------

    $ app/console doctrine:database:create

4) Create the tables needed:
------------------------

    $ app/console doctrine:schema:update --force

5) Import the object and image_paths tables from (proj-root-dir/other-resources/):
------------------------

    $ mysql -u root -p
    mysql> use deep-skies-sym;
    mysql> source /path/to/file/other-resources/object.sql;
    mysql> source /path/to/file/other-resources/image_paths.sql;

6) Create a visible objects table for your location:
------------------------
    $ app/console dso:planner:createVisibleObjectsTable --latitude=46.767 --longitude=23.583 --dateTime=2014-08-01*22:50:00

7) Add the admin user:
------------------------

Make sure that the database schema is up to date:

    $ php app/console doctrine:schema:update --force

Add the administrator:

    $ php app/console fos:user:create superuser master@deep-skies.com choosepass --super-admin

Enjoy!