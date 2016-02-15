# Symfony : Maniaplanet Dedicated Server Bundle
A bundle that creates a simple json api with application cache to simplify access to dedicated server information. 

[![Latest Stable Version](https://poser.pugx.org/oliverde8/mp-dedicated-server-bundle/v/stable)](https://packagist.org/packages/oliverde8/mp-dedicated-server-bundle) [![Total Downloads](https://poser.pugx.org/oliverde8/mp-dedicated-server-bundle/downloads)](https://packagist.org/packages/oliverde8/mp-dedicated-server-bundle) [![Latest Unstable Version](https://poser.pugx.org/oliverde8/mp-dedicated-server-bundle/v/unstable)](https://packagist.org/packages/oliverde8/mp-dedicated-server-bundle) [![License](https://poser.pugx.org/oliverde8/mp-dedicated-server-bundle/license)](https://packagist.org/packages/oliverde8/mp-dedicated-server-bundle)

It also has some built in blocks to use on your website

You may use this for 2 reasons ; 
* as a intermediate API to get information on your servers. As this caches the information you won't need to worry about affecting the dedicated server
* To built your website showing information about your server. 

The bundle comes with a jQUery plugin that will make it easy for you to display information from your server on any of your webpages. (check the demo page)

##Installation 
You of course need a working symfony installation; 

###Dependencies
First we need need to add doctrine cache to our installation if not already done. 
Add it first to your composer : 
```
"doctrine/doctrine-cache-bundle": "~1.0",
```

You also need to activate the bundles it in AppKernel : 
```
new \Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
new \Kitpages\SemaphoreBundle\KitpagesSemaphoreBundle(),
```

You need to check if monolog bundle is active 
```
Symfony\Bundle\MonologBundle\MonologBundle()
```

###Install the bundle
Add this to your composer file : 
```
"oliverde8/mp-dedicated-server-bundle": "dev-master"
```
_Version numbers for better support will be added once first phase of developpements is finished_

YOu also need to add this to your AppKernel.php
```
new oliverde8\MPDedicatedServerBundle\oliverde8MPDedicatedServerBundle(),
```

We will also need to define routes, you may change the prefix to whatever you wish
```
oliverde8_mp_dedicated_server:
    resource: "@oliverde8MPDedicatedServerBundle/Resources/config/routing.yml"
    prefix:   /mp/dedicated/api/
```

### Configuration

Edit you config.yml and add this in it : 
```
#Maniaplanet Dedicated seever configuration
oliverde8_mp_dedicated_server:
    servers :
        login:
            name : "My super server"
            host : "127.0.0.1"
            port : "5001"
            user : "SuperAdmin"
            password : "SuperAdmin"
        login_2:
            name : "My super server #2"
            host : "127.0.0.2"
            port : "5002"
            user : "SuperAdmin"
            password : "SuperAdmin"
    cache:
            info_timeout: 60
            map_timeout: 360
            map_retry_timeout: 60
            chat_timeout: 10
```

You can add as many servers as you wish, if you have only one server you can remove the second one from the config. 

As you can see you can configure cache ttl times as well. Higher ttl values means that the website will connect less often to the dedicated server. Lower values may cause lags. 

You will also need to setup a cache to be used : 

Add this to the end of your file if you have not set up a cache provider before. 
```
doctrine_cache:
    providers:
        oliverde8_mp_dedicated_server__info:
            type: file_system
            namespace: info_cache
```

We also need to set up the kitpages semaphore settings. 
```
kitpages_semaphore:
    sleep_time_microseconds: 100000
    dead_lock_microseconds: 30000000
```

The lock prevents multiple connections to the dedicated server if 2 users connect at both have skip cache. First to get the semaphore will do the call the other one will need to wait.

### Finish Installation 
just run a composer update now and it should do the magic. 

## The Demo
To check if everything works well you can use the demo page, in order to do this we need to add a route. We may add it to production or to dev. 
So add this to the routing_dev.yml
```
# oliverde8MPDedicatedServerBundle demo routes (to be removed)
oliverde8_mp_dedicated_server_demo:
    path:     /demo/{login}
    defaults: { _controller: oliverde8MPDedicatedServerBundle:Demo:serverInfo, login: _ }
```

We also need to define the assets for the demo. Find the `# Assetic Configuration` section in config.yml and add `oliverde8MPDedicatedServerBundle` to the list of bundles.
It should look like this : 
```
assetic:
    debug:          "%kernel.debug%"
    use_controller: false
    bundles:        [ oliverde8MPDedicatedServerBundle ]
```

You should be ready to go. 

Check the demo page to see if everything work well : 
```
app_dev.php/demo
```

### About the Demo
Even throught I call it a demo if you don't wish to spend time building a website to show information about your servers you may use this page. 
I am going to work on it some more to that it is nicer to display & has all the necesessart funtionality. 

You will of course need to change the route parameters to make it accessible in production with a nicer URL. 

## Acces the API
You can access the api from this urls

```
/mp/dedicated/api/<login>/info.json
/mp/dedicated/api/<login>/maps.json
/mp/dedicated/api/<login>/chat.json
```

### More About Api & Cache
The api gathers at the moment only server information. This consist basically of these information
* Server Options
 * Server Name 
 * Server Comment
 * Game Mode
 * Environment running on
 * ladderServerLimitMax
 * ...
* List of players
 * nickname
 * login 
 * ...
* List of spectators
* Current Map
* List of Maps
 * I missing some map information at the moment. 
* chat lines
 
To gather this data as fast as possible by default Server Options, List of Players & Current Map is updated every minute. 

Map list on the other hand is only retrieved every hour.

Chat lines is retrieved every 10 seconds. You can of course configure this. 

## Using the ready install
If you get the already installed version you will need to edit the configuration to add your own servers

In app/config.yml find this :

```
#Maniaplanet Dedicated seever configuration
oliverde8_mp_dedicated_server:
    servers :
```

Here you need to setup each server one by one. By default there is 2 example configured. 

Once you are done delete app/cache directory if on windows; on linux simple use app/console cache:clear --env="prod" 

And then open the web directory in your navigator. 

## More Information 
If you check the code you will see that what I call login isn't used anywhere in the code in that purpose, in reality it is just a key to identify the servers. 
You may use something else as well

## TO DO
This bundle is still being worked on, that is why there is no releases yet. 
* Clean up the code
* Comment the code
* Add event & hooks to the jquery plugin
* Make the jquery plugin more configurable
* Add missing data to the api return json? waiting for requests
