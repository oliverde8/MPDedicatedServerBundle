# Symfony : Maniaplanet Dedicated Server Bundle
A bundle that creates a simple json api with application cache to simplify access to dedicated server information. 

It also has some built in blocks to use on your website

You may use this for 2 reasons ; 
* as a intermediate API to get information on your servers. As this caches the information you won't need to worry about affecting the dedicated server
* To built your website showing information about your server. 

The bundle comes with a jQUery plugin that will make it easy for you to display information from your server on any of your webpages. (check the demo page)

##Installation 
You of course need a working symfony installation; 

add this to your composer file : 
```
"oliverde8/mp-dedicated-server-bundle": "dev-master"
```
_Version numbers for better support will be added once first phase of developpements is finished_

YOu also need to add this to your AppKernel.php
```
new oliverde8\MPDedicatedServerBundle\oliverde8MPDedicatedServerBundle(),
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
```

You can add as many servers as you wish, if you have only one server you can remove the second one from the config. 

You will also need to setup a cache to be used : 

Add this to the end of your file if you have not set up a cache provider before. 
```
doctrine_cache:
    providers:
        oliverde8_mp_dedicated_server__info:
            type: file_system
            namespace: info_cache
```

to check if everything works well you can use the demo page, in order to do this we need to add a route. So add this to the routing_dev.yml
```
# oliverde8MPDedicatedServerBundle demo routes (to be removed)
oliverde8_mp_dedicated_server_demo:
    path:     /demo/mp/server/info/{login}
    defaults: { _controller: oliverde8MPDedicatedServerBundle:Demo:serverInfo, login: _ }
```

You should be ready to go. 

Check the demo page to see if everything work well : 
```
app_dev.php/demo/mp/server/info/
```

## Acces the API
You can access the api from this url

```
/mp/server/info/<login>.json
```

## More Information 
If you check the code you will see that what I call login isn't used anywhere in the code in that purpose, in reality it is just a key to identify the servers. 
You may use something else as well

## TO DO
This bundle is still being worked on, that is why there is no releases yet. 
* Clean up the code
* Comment the code 
* Add configuration elements. (cache ttl's & cache key's) 
* Add event & hooks to the jquery plugin
* Make the jquery plugin more configurable
* Add missing data to the api return json. 
