# Vtiger (Laravel 5 Package)
Use the Vtiger webservice (REST) API from within Laravel for operations Create, Retrieve and Update.

See [Third Party App Integration (REST APIs)](http://community.vtiger.com/help/vtigercrm/developers/third-party-app-integration.html) 

## Installation, Configuration and Usage
----

### Installing
1. In order to install the Vtiger package in your Laravel project, just run the composer require command from your terminal:

    ```
    composer require "clystnet/vtiger"
    ```

    > *If you are using Laravel 5.5 you don’t need to do steps 2 and 3.*

2. Then in your config/app.php add the following to the providers array:

    ```
	Clystnet\Vtiger\VtigerServiceProvider::class,
    ```

3. In the same config/app.php add the following to the aliases array:

    ```
    'Vtiger' => Clystnet\Vtiger\Facades\Vtiger::class,
    ```

4. Publish the configuration file:

    ```
    php artisan vendor:publish --tag="vtiger"
    ```

### Configuration

- In Vtiger, create a new or select an existing user.
- Under *User Advanced Options* make note of the *username* and *access key*.
- In your application, edit *config/vtiger.php* and replace the following array values with your CRM username and access key. Also set the url to the webservice.php

    |key      |value                                |
    |---------|-------------------------------------|
    |url      |http://www.example.com/webservice.php|
    |username |API                                  |
    |accesskey|irGsy9HB0YOZdEA                      |

### Usage

## Contributing
----
Please report any issue you find in the issues page. Pull requests are more than welcome.

## Authors
----
* **Adam Godfrey** - [Clystnet](https://www.clystnet.com)

## License
----

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details















