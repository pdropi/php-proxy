# Simple PHP Proxy

This proxy script allows you to forward all HTTP/HTTPS requests to another server. Works for all common request types 
including GET, POST requests with files, PATCH and PUT requests. It has minimal set of requirements 
(PHP >=5.6, libcurl, gzip) which are available even on the smallest free hostings and has its own simple authorization 
and cookie support.

## How to use
* Copy the [simple_proxy.php](simple_proxy.php) script to publicly-accessible folder of a PHP web server (the script is standalone and has no PHP dependencies)
* Make a request targeting this script
* Add **Proxy-Auth** header with auth key [found here](https://github.com/pdropi/php-proxy/blob/master/simple_proxy.php#L45)
* Add **Proxy-Target-URL** header with URL to be requested by the proxy
* (Optional) Add **Proxy-Debug** header for debug mode

In order to protect using proxy by unauthorized users, consider changing `Proxy-Auth` token in [proxy source file](https://github.com/pdropi/php-proxy/blob/master/simple_proxy.php#L45) and in all your requests.

## How to use (via composer)
This might be useful when you want to redirect requests coming into your app. 

* Run `composer require zounar/php-proxy`
* Add `Proxy::run();` line to where you want to execute it (usually into a controller action)
  * In this example, the script is in `AppController` - `actionProxy`:
    ```
    use Zounar\PHPProxy\Proxy;

    class AppController extends Controller {

        public function actionProxy() {
            Proxy::$AUTH_KEY = '<your-new-key>';
            // Do your custom logic before running proxy
            $responseCode = Proxy::run();
            // Do your custom logic after running proxy
            // You can utilize HTTP response code returned from the run() method
        }
    }
    ```
* Make a cURL request to your web
  * In the example, it would be `http://your-web.com/app/proxy`
* Add **Proxy-Auth** header with auth key [found here](https://github.com/pdropi/php-proxy/blob/master/simple_proxy.php#L45)
* Add **Proxy-Target-URL** header with URL to be requested by the proxy
* (Optional) Add **Proxy-Debug** header for debug mode

In order to protect using proxy by unauthorized users, consider changing `Proxy-Auth` token by calling
`Proxy::$AUTH_KEY = '<your-new-key>';` before `Proxy::run()`. Then change the token in all your requests.

## Usage example
Following example shows how to execute GET request to https://www.github.com. Proxy script is at http://www.foo.bar/simple_proxy.php. All proxy settings are kept default, the response is automatically echoed.

```php
$request = curl_init('http://www.foo.bar/simple_proxy.php');

curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: change_this_on_both_side',
    'Proxy-Target-URL: https://www.github.com'
));

curl_exec($request);
```

## Debugging
In order to show some debug info from the proxy, add `Proxy-Debug: 1` header into the request. This will show debug info in plain-text containing request headers, response headers and response body.

```php
$request = curl_init('http://www.foo.bar/simple_proxy.php');

curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: change_this_on_both_side',
    'Proxy-Target-URL: https://www.github.com',
    'Proxy-Debug: 1'
));

curl_exec($request);
```

## Specifying User-Agent
Some sites may return different content for different user agents. In such case add `User-Agent` header to cURL request, it will be automatically passed to the request for target site. In this case it's Firefox 70 for Ubuntu.

```php
$request = curl_init('http://www.foo.bar/simple_proxy.php');

curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:70.0) Gecko/20100101 Firefox/70.0',
    'Proxy-Auth: change_this_on_both_side',
    'Proxy-Target-URL: https://www.github.com'
));

curl_exec($request);
```

## Error 301 Moved permanently
It might occur that there's a redirection when calling the proxy (not the target site), eg. during `http -> https` redirection. You can either modify/fix the proxy URL (which is recommended), or add `CURLOPT_FOLLOWLOCATION` option before `curl_exec`.

```php
$request = curl_init('http://www.foo.bar/simple_proxy.php');

curl_setopt($request, CURLOPT_FOLLOWLOCATION, true );
curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: change_this_on_both_side',
    'Proxy-Target-URL: https://www.github.com'
));

curl_exec($request);
```

## Save response into variable
The default cURL behavior is to echo the response of `curl_exec`. In order to save response into variable, all you have to do is to add `CURLOPT_RETURNTRANSFER` cURL option.

```php
$request = curl_init('http://www.foo.bar/simple_proxy.php');

curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: change_this_on_both_side',
    'Proxy-Target-URL: https://www.github.com'
));

$response = curl_exec($request);
```

## Example of use with apache and PHP reverse proxy:
You can redirect directly to proxy file via webserver. Reverse proxy can be done direct with most webservers, but, redirecting to php provides more options for handling the connection. Example with apache:

```
<VirtualHost>
...
<Location "/apppath">
      ProxyPass "http://localhost/simple_proxy.php"
      RequestHeader set Proxy-Auth: "change_this_on_both_side"
      RequestHeader set Proxy-Target-URL: "https://anotherlocation.com:port"
      ProxyPassReverse "http://localhost/simple_proxy.php"
</Location>
</VirtualHost>
```

## Example of use with apache, PHP reverse proxy and include() from another php file
```
<VirtualHost>
#...
<Location "/apppath">
      ProxyPass "http://localhost/simple_proxy_use_example.php"
      ProxyPassReverse "http://localhost/simple_proxy_use_example.php"
</Location>
#consider implementing aditional security
#<Proxy *>
    # AuthType Basic
   #   AuthName "Who are you"
  #    AuthUserFile /home/turismo/.htpasswd
 #     Require valid-user
#</Proxy>
</VirtualHost>

```
```
<?php
#simple_proxy_use_example.php
//Some security checks
 if(@$_SERVER['REMOTE_ADDR'] != '127.0.0.1') die('Your IP:'.@$_SERVER['REMOTE_ADDR'].' is not allowed'); //change '127.0.0.1' to real source
$_SERVER['HTTP_PROXY_AUTH']= 'change_this_on_both_side'; //content must be same of static $AUTH_KEY on simple_proyx.php

$_SERVER['HTTP_REVERSE_PROXY'] = 'anotherhost.com:3128';  //you can set real destination address to your service here. eg: localhost:8080 leave blank '' to disable

//filter this filename and send the rest of request_uri to target URL
	$_SERVER['HTTP_PROXY_TARGET_URL']= 'https://'.$_SERVER['HTTP_REVERSE_PROXY'].@explode($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'],2)[1];

include '/can_be_or_NOT_public/simple_proxy.php';
```

## Example of use with apache and include() from another php file

```
<VirtualHost>
#...
ScriptAlias /apppath /home/scripts/simple_proxy_use_example.php
</VirtualHost>

```

```
<?php
#simple_proxy_use_example.php
//Some security checks
 if(@$_SERVER['REMOTE_ADDR'] != '127.0.0.1') die('Your IP:'.@$_SERVER['REMOTE_ADDR'].' is not allowed'); //change '127.0.0.1' to real source
 // If this file is NOT on local net and is open on internet and is not reverse proxy, do not use only IP check as security check method 
 //if (@$_SERVER['HTTP_X_ADICIONAL_CHECK_ANY_NAME'] != 'hash_check_or_another') die ('Send a valid request header or change this');
$_SERVER['HTTP_PROXY_AUTH']= 'change_this_on_both_side'; //content must be same of static $AUTH_KEY on simple_proyx.php

$_SERVER['HTTP_REVERSE_PROXY'] = '';  //you can set real destination address to your service here. eg: localhost:8080 leave blank '' to disable

if (!empty($_SERVER['HTTP_REVERSE_PROXY'])) //filter this filename and send the rest of request_uri to target URL
	$_SERVER['HTTP_PROXY_TARGET_URL']= 'https://'.$_SERVER['HTTP_REVERSE_PROXY'].@explode($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'],2)[1];
else //get from url or set fixed
	$_SERVER['HTTP_PROXY_TARGET_URL']= 'https://'.$_SERVER['HTTP_HOST'].@explode($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'],2)[1];
//or not strip this script filename
//	$_SERVER['HTTP_PROXY_TARGET_URL']= 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
//or set fixed
//	$_SERVER['HTTP_PROXY_TARGET_URL']= 'http://realdestiny.com:port'.@explode($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'],2)[1];

//I you want, you can use below function and simple_proxy.php script will automatically recognize
//function response_body_treatment($cont){
	//Do the treatment you need
	//return str_replace('<i> i buy from big companies</i>', '<strong> i support small business</strong>', $cont);
//}

include '/can_be_NOT_public/simple_proxy.php';
```
