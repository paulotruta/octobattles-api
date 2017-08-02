# Octobattles - API

A pure PHP implementation of an API in order to centrally manage the octobattles game logic and data.

All endpoints support the common request methods GET, POST, PUT, DELETE, OPTIONS

Improve your octopus with your real life coding xp form github, and battle your coworkers!

## Setup

The following instructions assume apache is installed in the system.

- Clone the repo to your public server directory.
- Edit the file /etc/httpd/conf/extra/httpd-vhosts.conf and add the following:
    `<VirtualHost *:80>
        ServerAdmin your@email.com
        DocumentRoot "/home/<userfolder>/public_html/octobattles-api"
        ServerName api.octobattles.com
        ServerAlias www.api.octobattles.com
        ErrorLog "/var/log/httpd/octobattlesapi-error-log"
        CustomLog "/var/log/httpd/octobattlesapi-access-log" common
    </VirtualHost>` 
- Edit /etc/hosts file and add:
    `127.0.0.1  api.octobattles.com` 
- Restart apache: `systemctl restart httpd`
- Create an empty database using mysql (`CREATE DATABASE octobattles-api`).
- Change the DBUSER, DBNAME, DBPASS globals in the index.php file.
- Import every .sql file present in the /models/migrations/ folder.
- Ready to go! `api.octobattles.com/v1.0/characters.json` should respond 200.  

## Project Structure

This project is composed of two main files, namely index.php and Api.php

- index.php receives all the requests to this project, handling autoloading and properly routing to the appropriate classes.
- Api.php contains the main API class logic, extendable by each class in the "endpoints" folder.

## Endpoints

- **characters/<$character_id>** 
- **battles/<$battle_id>**
- **languages/<$character_id>/<$language_name>**
- **types/<$type_name>**

[Click here to accees endpoint documentation with examples.](https://documenter.getpostman.com/view/2508915/octobattles-api/6mz5wax) 

Each endpoint configuration can be found in the "endpoints" folder, inside the php file with the filename as the respective endpoint name.
Each one of these files implement a class for the Endpoint, having as methods the operations allowed over that endpoint, such as GET, POST, PUT, etc.

## Models

In order to persist data, a MySQL database is used containing tables for each model representation. Each php file in the models folder contains a class that extends the libs/Orm class and is representative of the respective database table, with class instance variables as meta information about the table, namely:
 
- protected $table_name (The table name as created in the database. By default, it is populated on construction with the class name in lowercase.)
- Any number of variables defined as public in each class represents a table field.

### Migrations

This folder contains .sql files that should be imported prior to project usage, using either the command line or PHPMyAdmin for example. Each time a model class changes its configuration, the developer should go and reflect the changes in the respective .sql file in this folder.

## Libs

This folder contains any number of php files with helper libraries for the API project. All libraries contained in this project were handwritten from scratch. Research links are present as initial comments in each helper file.

### ORM

A class that implements the base ORM used by the model classes. Every database connection made throught the API project is done using either this class or a child model class.

This class implements the following methods:
- **save()** (Saves a class instance public variables into persistance)
- **model_from_raw( array $data )** (Receives a raw array of data to populate an Orm child class)
- **model_from_db( int $id )**
- **raw_from_db( int $id )**
- **find( array $data|string $custom_where)** (by exact value or via custom WHERE statement)
- **delete()** (Removes the persistent associated data entry from the database)

### LogDebug

A class that allows logging to console, output and optionally persist logs. Instatiate with a message and any context that should be also saved to help troubleshoot problems.

## Unit Tests

Still not available. Instructions will appear here once ready.