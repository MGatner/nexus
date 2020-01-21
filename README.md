# Nexus Simulator

Heroes of the Storm simulator

## Requirements

**Nexus Simulator** is built on version 4 the CodeIgniter PHP framework. You will need to be
sure your environment meets all the
[system requirements](https://codeigniter4.github.io/CodeIgniter4/intro/requirements.html).
Framework requirements may change but here is a good start:

* PHP 7.2 or newer
* PHP extensions (`php -m`): intl, json, mbstring, mysqlnd, xml, curl
* You may also need a database with one of the framework's supported drivers

Framework requirements may depend on your choice of web host. See "Hosting with ..."
in the CodeIgniter [User Guide](https://codeigniter4.github.io/CodeIgniter4/installation/running.html).

You will also need [Composer](https://getcomposer.org/download/)
to install it and manage dependencies.

## Installation

1. Clone or download the repo
2. Rename **env** to **.env**, uncomment and fill `app.baseURL`
3. Install the framework, modules, and dependencies: `composer install`

## Database

The command line interface does not require a database, but if you want to use the web
interface there are some additional steps to configure a database.

1. Fill in `database.*` in your **.env** file (skip if using default SQLite3)
2. Migrate the database: `php spark migrate -all`
3. Seed the database: `php spark db:seed InitialSeeder`

## Running

Point the web server to the **public** directory in the project root. For development you
can serve it locally
([docs](https://codeigniter4.github.io/CodeIgniter4/installation/running.html)):

	php spark serve

You can also interface with the Command Line Interface using `spark`, e.g.:

	php spark simulate

## Modifying

*Coming soon*
