#octobattles-api

A pure PHP implementation of an API in order to centrally manage the octobattles game logic and data.

All endpoints support the common request methods GET, POST, PUT, DELETE, OPTIONS

Improve your octopus with your real life coding xp form github, and battle your coworkers!

## Project Structure

This project is composed of two main files, namely index.php and Api.php

- index.php receives all the requests to this project, handling autoloading and properly routing to the appropriate classes.
- Api.php contains the main API class logic, extendable by each class in the "endpoints" folder.

## Endpoints

- Characters/<$character_id>
- Battles/<$battle_id>
- Languages/<$character_id>/<$language_name>

Each endpoint configuration can be found in the "endpoints" folder, inside the php file with the filename as the respective endpoint name.
Each one of these files implement a class for the Endpoint, having as methods the operations allowed over that endpoint, such as GET, POST, PUT, etc.

## Models

In order to persist data, a MySQL database is used containing tables for each model representation. Each php file in the models folder contains a class representative of the respective database table, with class instance variables as meta information about the table, namely:
 
- protected $table_name (The table name as created in the database. By default, it is populated on construction with the class name in lowercase.)
- Any number of variables defined as public in each class represents a table field.

    ### Migrations

    This folder contains .sql files that should be imported prior to project usage, using either the command line or PHPMyAdmin for example. Each time a model class changes its configuration, the developer should go and reflect the changes in the respective .sql file in this folder.

    ### Fixtures

    Fixtures are files named after the database table name, that contain a single array named $data representing the example database data that can be imported into the project using a model class method named "fixture_import".

    Running the "import_all.php" file inside the Fixtures folder imports all records from all fixtures into the database.
    If a fixture cannot be inserted due to database constraints, it is skipped.

## Libs

This folder contains any number of php files with helper libraries for the API project. All libraries contained in this project were handwritten from scratch. Research links are present as initial comments in each helper file.



