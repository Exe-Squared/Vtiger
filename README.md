# Vtiger (Laravel 5 Package)
Use the Vtiger webservice (REST) API from within Laravel for operations Create, Retrieve and Update.

See [Third Party App Integration (REST APIs)](http://community.vtiger.com/help/vtigercrm/developers/third-party-app-integration.html) 

## Installation, Configuration and Usage

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

> Because I've experienced problems getting the sessionid from the CRM when multiple users are accessing the CRM at the same time, the solution was to store the sessionid into a file within Laravel application.
> Instead of getting the token from the database for each request using the webservice API, a check is made against the expiry time in the file. If the expiry time has expired, a token is requested from the CRM and file is updated with the new token and updated expiry time.

### Usage

In your controller include the Vtiger package
```
use Vtiger;
```

#### Create

To insert a record into the CRM, first create an array of data to insert. Don't forget the added the id of the `assigned_user_id` (i.e. '4x12') otherwise the insert will fail as `assigned_user_id` is a mandatory field. 
```
$data = array(
    'assigned_user_id' => '',
);
```
To do the actual insert, pass the module name along with the json encoded array to the *create* function.

```
Vtiger::create($MODULE_NAME, json_encode($data));
```

#### Retrieve

To retrieve a record from the CRM, you need the id of the record you want to find (i.e. '4x12').
```
$id = '4x12';

$obj = Vtiger::retrieve($id);

// do someting with the result
var_dump($obj);
```

#### Update

The easiest way to update a record in the CRM is to retrieve the record first.
```
$id = '4x12';

$obj = Vtiger::retrieve($id);
```

Then update the object with updated data.
```
$obj->result->field_name = 'Your new value';

$update = Vtiger::update($obj->result);
```

#### Query

To use the [Query Operation](http://community.vtiger.com/help/vtigercrm/developers/third-party-app-integration.html#query-operation), you first need to create a SQL query.
```
$query = "SELECT * FROM ModuleName;";
```

Then run the query...
```
$obj = Vtiger::query($query);

//loop over result
foreach($obj->result as $result) {
    // do something
}
```

## Contributing

Please report any issue you find in the issues page. Pull requests are more than welcome.

## Authors

* **Adam Godfrey** - [Clystnet](https://www.clystnet.com)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details