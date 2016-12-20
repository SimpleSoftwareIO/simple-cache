Simple Cache
========================

[![Build Status](https://travis-ci.org/SimpleSoftwareIO/simple-cache.svg?branch=master)](https://travis-ci.org/SimpleSoftwareIO/simple-cache)
[![Latest Stable Version](https://poser.pugx.org/simplesoftwareio/simple-cache/v/stable.svg)](https://packagist.org/packages/simplesoftwareio/simple-cache)
[![Latest Unstable Version](https://poser.pugx.org/simplesoftwareio/simple-cache/v/unstable.svg)](https://packagist.org/packages/simplesoftwareio/simple-cache)
[![License](https://poser.pugx.org/simplesoftwareio/simple-cache/license.svg)](https://packagist.org/packages/simplesoftwareio/simple-cache)
[![Total Downloads](https://poser.pugx.org/simplesoftwareio/simple-cache/downloads.svg)](https://packagist.org/packages/simplesoftwareio/simple-cache)

## This is pre-released software.  Use at your own risk.

- [Introduction](#docs-introduction)
- [Configuration](#docs-configuration)
- [Usage](#docs-usage)

<a id="docs-configuration"></a>
## Configuration

#### Composer

First, add the Simple Cache package to your `require` in your `composer.json` file:

	"require": {
		"simplesoftwareio/simple-cache": "dev-master"
	}

Next, run the `composer update` command.  

<a id="docs-usage"></a>
## Usage

The cacheable trait may be used by adding the trait to the Eloquent model of your choice.

    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    use SimpleSoftwareIO\Cache\Cacheable;
    
    class User extends Model
    {
        use Cacheable;
    }

Yes, it really is that simple to use.  The settings will use the default Cache store set up in your Laravel application.  Further, models will be cached for 30 minutes by default.

### Properties

#### cacheLength

You may adjust the default cache length by modifying the `cacheLength` property on the model.

    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    use SimpleSoftwareIO\Cache\Cacheable;
    
    class User extends Model
    {
        use Cacheable;
        protected $cacheLength = 60; //Will cache for 60 minutes
    }
    
#### cacheStore

The configured cache store may also be adjusted by modifying the `cacheStore` property.  The cache store will need to be set up in your application's `config/cache.php` configuration file.

    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    use SimpleSoftwareIO\Cache\Cacheable;
    
    class User extends Model
    {
        use Cacheable;
        protected $cacheStore = 'redis'; //Will use the configured `redis` store set up in your `config/cache.php` file.
    }
    
#### cacheBusting

Cache busting will automatically invalid the cache when an `insert/update/delete` command is ran by your models.  You can enable this feature by setting the `cacheBusting` property to `true` on your Eloquent model.  By default this feature is disabled.

    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    use SimpleSoftwareIO\Cache\Cacheable;
    
    class User extends Model
    {
        use Cacheable;
        protected $cacheBusting = true;
    
    }
    
>Be careful!  Eloquent Model's with a high amount of insert/update/delete traffic should not use the cache busting feature.  The large amount of changes will invalid the model too often and cause the cache to be useless.  It is better to set a lower cache length to invalid the results frequently if up to date data is required.

### Methods

#### bust()

The `bust` method will enable cache busting for the model

    User::bust();  //Cache busting is enabled.
    
#### dontBust()

The `dontBust` method will disable cache busting for the model.

    User::dontBust();  //Cache busting is disabled.

#### isBusting()

`isBusting` will return the current status of the `cacheBusting` property.

    if(User::isBusting()) {
        // Is cache busting
    }
    
#### remember($length)

`remember` will set the length of time in minutes to remember an Eloquent query.

    User::remember(45)->where('id', 4')->get();
    
#### rememberForever()

`rememberForever` will remember a query forever.  Well, technically 10 years but lets pretend it is forever eh?

    User::rememberForever()->where('id', 4')->get();
    
#### dontRemember()

And lastly, `dontRemember` will not cache a query result.

    User::dontRemember(0)->where('id', 4')->get();