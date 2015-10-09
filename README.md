# Seafile PHP SDK

This is a PHP package for accessing [Seafile Web API](http://example.com).

## German Web Application Developer Available for Hire!

No marketing skills whatsoever, but low rates, nearly 20 years of experience, and german work attitude.

Get in touch now: https://www.reneschmidt.de/blog/impressum/

[![Build Status](https://api.travis-ci.org/rene-s/Seafile-PHP-SDK.svg)](https://travis-ci.org/rene-s/Seafile-PHP-SDK)

## What is Seafile?

- Open Source Cloud Storage for your teams and organizations
- Built in File Encryption, better Protecting your Privacy
- Collaboration around Files, file locking and other features make collaboration easy.

## Dependencies

- PHP >=5.5
- Guzzle 6

## Contributing

Please note that this package still is in its infancy. Only a small part of the API has been implemented so far.

Pull requests are welcome. Please try to adhere to my coding standards, see file bin/run_tests.sh and https://github.com/rene-s/psr-2-rsd/blob/master/psr-2-rsd_ruleset.xml for more info.

## Links

- http://seafile.com
- https://www.seafile-server.org/ (Seafile server hosting in Germany)
- http://manual.seafile.com/develop/web_api.html#seafile-web-api-v2
- https://reneschmidt.de

## Obtain API token

```bash
mkdir ~/.seafile-php-sdk
curl -d "username=you@example.com&password=123456" https://your.seafile-server.com/api2/auth-token/ > ~/.seafile-php-sdk/api-token.json
```

Hint: if user name contains a "+" char, replace the char with "%2B" (hex ascii for "+"). Just so you know.

## Installing Seafile-PHP-SDK

The recommended way to install seafile-php-sdk is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of seafile-php-sdk:

```bash
composer.phar require rsd/seafile-php-sdk
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update seafile-php-sdk using composer:

 ```bash
composer.phar update
 ```

## Using Seafile PHP SDK

### Connecting to Seafile

First, you need to include the API token (see above):

```php
$tokenFile = getenv("HOME") . "/.seafile-php-sdk/api-token.json";

$token = json_decode(file_get_contents($tokenFile));

$client = new Client(
    [
        'base_uri' => 'https://your.seafile-server.com',
        'debug' => false,
        'headers' => [
            'Authorization' => 'Token ' . $token->token
        ]
    ]
);
```

### List available libraries

```php
$libraryDomain = new Library($client);
$libs = $libraryDomain->getAll();

foreach ($libs as $lib) {
    printf("Name: %s, ID: %s, is encrypted: %s\n", $lib->name, $lib->id, $lib->encrypted ? 'YES' : 'NO');
}
```

### List files and directories of a library

```php
$directoryDomain = new Directory($client);
$lib = $libraryDomain->getById('some library ID of yours');
$items = $directoryDomain->getAll($lib);

foreach ($items as $item) {
    printf("%s: %s (%d bytes)\n", $item->type, $item->name, $item->size);
}
```
### Download unencrypted file

```php
$dir = '/'; // dir in the library
$saveTo = '/tmp/'. $item->name; // save file to this local path
$fileDomain = new File($client);
$downloadResponse = $fileDomain->download($lib, $item, $dir, $saveTo);
```

### Download encrypted file

Downloading a file from an encrypted library without password would
inevitably fail, so just give the password before attempting:

```php
$lib->password = 'library encryption password of yours';
// rest is the same as 'Download unencrypted file', see above
```

### Upload file

```php

$fileToUpload = '/path/to/file/to/be/uploaded.zip';
$dir = '/'; // directory in the library to save the file in
$uploadResponse = $fileDomain->upload($lib, $fileToUpload, $dir);
```

### Debugging and how to enable logging of requests and responses

This example requires monolog. Log entries and Guzzle debug info will be written to stdout.

```php

$logger = new Logger('Logger');

$stack = HandlerStack::create();
$stack->push(
    Middleware::log(
        $logger,
        new MessageFormatter("{hostname} {req_header_Authorization} - {req_header_User-Agent} - [{date_common_log}] \"{method} {host}{target} HTTP/{version}\" {code} {res_header_Content-Length} req_body: {req_body} response_body: {res_body}")
    )
);

$client = new Client(
    [
        'base_uri' => 'https://your.seafile-server.com',
        'debug' => true,
        'handler' => $stack,
        'headers' => [
            'Authorization' => 'Token ' . $token->token
        ]
    ]
);
```

## License

[MIT](https://raw.githubusercontent.com/rene-s/seafile-php-sdk/master/LICENSE) &copy; 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG

