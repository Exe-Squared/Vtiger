# Vtiger (Laravel 5 Package)
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-1-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->
Use the Vtiger webservice (REST) API from within Laravel for the following operations.

- Create
- Retrieve
- Update
- Delete
- Search
- Query
- Describe

See [Third Party App Integration (REST APIs)](http://community.vtiger.com/help/vtigercrm/developers/third-party-app-integration.html)

## Installation, Configuration and Usage

### Installing
1. In order to install the Vtiger package in your Laravel project, just run the composer require command from your terminal:

    ```
    composer require "clystnet/vtiger ^1.4"
    ```

    > *If you are using Laravel >= 5.5 you don’t need to do steps 2 and 3.*

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
- In your application, edit *config/vtiger.php* and replace the following array values
  - Set the url to the https://{DOMAIN_NAME}/webservice.php
  - Set the username and accesskey with your CRM username and access key.
  - Set persistconnection to false if you want a fresh login with each request

    |key              |value                                |
    |-----------------|-------------------------------------|
    |url              |http://www.example.com/webservice.php|
    |username         |API                                  |
    |accesskey        |irGsy9HB0YOZdEA                      |
    |persistconnection|true                                 |
    |max_retries      |10                                   |

> Because I've experienced problems getting the sessionid from the CRM when multiple users are accessing the CRM at the same time, the solution was to store the sessionid into a file within Laravel application.
> Instead of getting the token from the database for each request using the webservice API, a check is made against the expiry time in the file. If the expiry time has expired, a token is requested from the CRM and file is updated with the new token and updated expiry time.

### Usage

In your controller include the Vtiger package
```php
use Vtiger;
```

#### Create

To insert a record into the CRM, first create an array of data to insert. Don't forget the added the id of the `assigned_user_id` (i.e. '4x12') otherwise the insert will fail as `assigned_user_id` is a mandatory field.
```php
$data = array(
    'assigned_user_id' => '',
);
```
To do the actual insert, pass the module name along with the json encoded array to the *create* function.

```php
Vtiger::create($MODULE_NAME, json_encode($data));
```

#### Retrieve

To retrieve a record from the CRM, you need the id of the record you want to find (i.e. '4x12').
```php
$id = '4x12';

$obj = Vtiger::retrieve($id);

// do someting with the result
var_dump($obj);
```

#### Update

The easiest way to update a record in the CRM is to retrieve the record first.
```php
$id = '4x12';

$obj = Vtiger::retrieve($id);
```

Then update the object with updated data.
```php
$obj->result->field_name = 'Your new value';

$update = Vtiger::update($obj->result);
```

#### Delete

To delete a record from the CRM, you need the id of the record you want to delete (i.e. '4x12').
```php
$id = '4x12';

$obj = Vtiger::retrieve($id);

// do someting with the result
var_dump($obj);
```

#### Lookup

This function uses the Vtiger Lookup API endpoint to search for a single piece of information within multiple columns of a Vtiger module. This function is often multitudes faster than the search function.


```php
    $dataType = 'phone';
    $phoneNumber = '1234567890';
    $module = 'Leads';
    $columns = ['phone', 'fax']; //Must be an array
    
    Vtiger::lookup($dataType, $phoneNumber, $module, $columns);
```

#### Search

This function is a sql query builder wrapped around the query function. Accepts instance of laravels QueryBuilder.
```php
$query = DB::table('Leads')->select('id', 'firstname', 'lastname')->where('firstname', 'John');

$obj = Vtiger::search('Leads', $query);

//loop over result
foreach($obj->result as $result) {
    // do something
}
```

By default the function will quote but not escape your inputs, if you wish for your data to not be quoted, set the 3rd paramater to false like so:
```php
$obj = Vtiger::search('Leads', $query, false);
```

Also keep in mind that Vtiger has several limitations on it's sql query capabilities. You can not use conditional grouping i.e "where (firstname = 'John' AND 'lastname = 'Doe') OR (firstname = 'Jane' AND lastname = 'Smith') will fail.


#### Query

To use the [Query Operation](http://community.vtiger.com/help/vtigercrm/developers/third-party-app-integration.html#query-operation), you first need to create a SQL query.
```php
$query = "SELECT * FROM ModuleName;";
```

Then run the query...
```php
$obj = Vtiger::query($query);

//loop over result
foreach($obj->result as $result) {
    // do something
}
```

### Describe

To describe modules in the CRM run this with the module name

```php
$moduleDescription = (Vtiger:describe("Contacts"))->result;
```

## Contributing

Please report any issue you find in the issues page. Pull requests are more than welcome.

## Authors

* **Adam Godfrey** - [Clystnet](https://www.clystnet.com)
* **Chris Pratt** - [Clystnet](https://www.clystnet.com)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="https://github.com/cjcox17"><img src="https://avatars0.githubusercontent.com/u/1725282?v=4" width="100px;" alt=""/><br /><sub><b>Clyde Cox</b></sub></a><br /><a href="https://github.com/Clystnet/Vtiger/commits?author=cjcox17" title="Code">💻</a></td>
  </tr>
</table>

<!-- markdownlint-enable -->
<!-- prettier-ignore-end -->
<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!