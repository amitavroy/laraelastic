# A simple wrapper to use Elastic search with Laravel Eloquent models.

This package will allow you to use Elastic search along with your Eloquent models with ease. This package will create individual indexes for each model of your application and using the Eloquent events 'created', 'updated' and 'deleted' this package will keep your Elastic index in sync.

## Installation and usage
This package best works with Elastic search 6.0 and above.

To install, you just need to require the package using the following command

```
composer require amitavroy/laravel-elastic
``` 

## Setup and configuration
This package comes with a configuration file which you will need to publish and set the host array. This is how the package is going to access the Elastic search instance.

```
'hosts' => [
    'http://localhost:6200',
],
```

Also, you can define a prefix to your indexes in case you are using the same Elastic search instance for multiple application.

```
'prefix' => 'some_prefix_',
```

## Usage
This package provides a trait which you need to add to any Eloquent model that you want to be indexed and searched. And, you need to mention which fields of that model you want to search on. For example, you don't want to store and search the user's password, the remember token or some other sensitive data. 

```
<?php

namespace App;

use Amitav\LaravelElastic\Traits\Searchable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Searchable;

    protected $searchFields = [
        'name', 'address', 'phone'
    ];
}
```

With this configuration, if a new user is created, the content will get indexed. And the same holds true for update and delete.

Some additional methods which are available with any model using the Searchable traits are:

```
User::reindex();
```

This will clear the current Elastic search index created and re-index the entire content. 

And the most important method, is the search. On any Eloquent model which is using the Searchable trait, you can perform the following action:

```
User::search('keyword');
```

This will return you the Elastic search reponse with data points like the number of hits, the execution time, score and the search results. And, there is another option where you can get the result as a collection by passing a second argument as true.

```
User::search('keyword', true);
```
