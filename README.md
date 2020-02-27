## Populate Data to database by Faker
---
Simple PHP Script to populate data into database

## Installation

Step 1: Git clone this repo
Step 2: Composer install 

#### Usage: 

 - Adjust connection configuration in `src/populate.php`
 - run: `php src/populate.php "TABLE_NAME" "NO_OF_RECORDS"`
 - Example: `php src/populate.php "users" 200`
 
 
#### Limitation: 
Currently only Postgres database is supported. In next version we will include other database too and the code will be more generic.

peace  :boom:  