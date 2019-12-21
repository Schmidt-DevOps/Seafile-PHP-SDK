# Seafile PHP SDK

This is a PHP package for accessing [Seafile Web API](https://www.seafile.com/).

## German Web Application Developer Available for Hire!

No marketing skills whatsoever, but low rates, nearly 20 years of experience, and german work attitude.

Get in touch now: https://sdo.sh/DevOps/#contact

[![Build Status](https://api.travis-ci.org/rene-s/Seafile-PHP-SDK.svg)](https://travis-ci.org/Schmidt-DevOps/Seafile-PHP-SDK)
[![Test Coverage](https://codeclimate.com/github/rene-s/Seafile-PHP-SDK/badges/coverage.svg)](https://codeclimate.com/github/rene-s/Seafile-PHP-SDK/coverage)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## What is Seafile?

- Open Source Cloud Storage for your teams and organizations
- Built in File Encryption, better Protecting your Privacy
- Collaboration around Files, file locking and other features make collaboration easy.

## How to get Started

To get started with Seafile PHP SDK, you may either set up your own private Seafile server (see [https://www.seafile.com/en/product/private_server/](https://www.seafile.com/en/product/private_server/)) or obtain seacloud.cc account
[https://seacloud.cc](https://seacloud.cc). Because the SDK is in its infancy it's highly recommended to set up a test server or create a test account.

It's not advisable yet to use your real server/account if you already got one.

After you have created your test account continue to the next step.

## Roadmap and notes on development

Please note that this SDK currently is under active development and that things might change rather drastically.

If you are looking for stability please refer to stable tags.

## Obtain API token

Please refer to the [Seafile docs](https://download.seafile.com/published/web-api/home.md#user-content-Quick%20Start) on how to obtain an API token.

This also applies to the token required for functional tests (`TEST_SERVER_AUTHORIZATION_TOKEN`).

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
# composer.phar dump-autoload -o # not required anymore
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update seafile-php-sdk using composer:

 ```bash
composer.phar update
# composer.phar dump-autoload -o # not required anymore
 ```

## Using Seafile PHP SDK

Hint: Have a look at ```bin/example.php``` -- everything this SDK can do is covered there!

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
$libraryResource = new Library($client);
$libs = $libraryResource->getAll();

foreach ($libs as $lib) {
    printf("Name: %s, ID: %s, is encrypted: %s\n", $lib->name, $lib->id, $lib->encrypted ? 'YES' : 'NO');
}
```

### List directory contents

```php
$directoryResource = new Directory($client);
$lib = $libraryResource->getById('some library ID of yours');
$items = $directoryResource->getAll($lib, '/'); // 2nd param is the name of the directory or '/' 

foreach ($items as $item) {
    printf("%s: %s (%d bytes)\n", $item->type, $item->name, $item->size);
}
```

### Check if directory item exists

```php
$parentDir = '/'; // DirectoryItem must exist within this directory
$directory = 'DirectoryName';
if($directoryResource->exists($lib, $directoryItemName, $parentDir) === false) {
 //  directory item does not exist
}
```
Be aware that because Seafile Web API does not provide a function to do this check on its own, all items of the directory will get loaded for iteration. So that's not very efficient.

### Create directory
```php
$parentDir = '/'; // Create directory within this folder
$directory = 'DirectoryName'; // name of the new Directory
$recursive = false; // recursive will create parentDir if not already existing
$success = $directoryResource->create($lib, $directory, $parentDir, $recursive);
```

### Download file from unencrypted library

```php
$dir = '/'; // dir in the library
$saveTo = '/tmp/'. $item->name; // save file to this local path
$fileResource = new File($client);
$downloadResponse = $fileResource->downloadFromDir($lib, $item, $saveTo, $dir);
```

### Download file from encrypted library

Trying to download a file from an encrypted library without unlocking it first would
inevitably fail, so just unlock (API docs say "decrypt") the library before attempting:

```php
$success = $libraryResource->decrypt($libId, ['query' => ['password' => $password]]);
// rest is the same as 'Download file from unencrypted library', see above
```

### Upload file

```php

$fileToUpload = '/path/to/file/to/be/uploaded.zip';
$dir = '/'; // directory in the library to save the file in
$response = $fileResource->upload($lib, $fileToUpload, $dir);
$uploadedFileId = json_decode((string)$response->getBody());
```

### Update file

```php
$response = $fileResource->update($lib, $newFilename, '/');
$updatedFileId = json_decode((string)$response->getBody());
```

### Get file details

```php
$directoryItem = $fileResource->getFileDetail($lib, '/' . basename($fullFilePath));
```

### Get API user account info

```php
$accountResource = new Account($client);

$accountType = $accountResource->getInfo();

print_r($accountType->toArray());
```

### Get all accounts

```php
$accountResource = new Account($client);

$accountTypes = $accountResource->getAll();

foreach ($accountTypes as $accountType) {
    print_r($accountType->toArray());
}
```

### Create account

```php
$newAccountType = (new AccountType)->fromArray([
    'email' => 'someone@example.com',
    'password' => 'password',
    'name' => 'Hugh Jazz',
    'note' => 'I will not waste chalk',
    'institution' => 'Duff Beer Inc.'
]);

$success = $accountResource->create($newAccountType);
```

### Update account

```php
$updateAccountType = (new AccountType)->fromArray([
    'name' => 'Divine Hugh Jazz',
    'email' => 'someone@example.com'
]);

$success = $accountResource->update($updateAccountType);
```

### Get account info by email address

```php
$accountResource = new Account($client);

$accountType = $accountResource->getByEmail('someone@example.com');

print_r($accountType->toArray());
```

### Delete account

```php
$accountResource = new Account($client);

$accountType = (new AccountType)->fromArray([
    'email' => 'someone@example.com'
]);

$success = $accountResource->remove($accountType);
```

or

```php
$accountResource = new Account($client);

$success = $accountResource->removeByEmail('someone@example.com');
```

### Get avatar of an account

```php
$accountType = (new AccountType)->fromArray([
   'email' => 'someone@example.com'
]);

$avatarResource = new Avatar($client);

print_r($avatarResource->getUserAvatar($accountType)->toArray());
```

or

```php
print_r($avatarResource->getUserAvatarByEmail('someone@example.com')->toArray());
```

### Create and remove shared link

```php
$libraryResource = new Library($client);
$directoryResource = new Directory($client);
$fileResource = new File($client);
$sharedLinkResource = new SharedLink($client);

// create share link for a file
$expire = 5;
$shareType = SharedLinkType::SHARE_TYPE_DOWNLOAD;
$p = "/" . basename($newFilename);
$password = 'qwertz123';

$shareLinkType = $sharedLinkResource->create($lib, $p, $expire, $shareType, $password);

// remove shared link
$success = $sharedLinkResource->remove($shareLinkType);
```

### Get all starred files, star and unstar file

```php
$libraryResource = new Library($client);
$starredFileResource = new StarredFile($client);

// get all starred files
$dirItems = $starredFileResource->getAll();

// unstar all starred files
foreach ($dirItems as $dirItem) {
    $lib = $libraryResource->getById($dirItem->repo);
    $starredFileResource->unstar($lib, $dirItem);
}

// re-star all files
foreach ($dirItems as $dirItem) {
    $lib = $libraryResource->getById($dirItem->repo);
    $starredFileResource->star($lib, $dirItem);
}
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

## Issues

- Please let me know of issues.

## Dependencies

- PHP >=7.0 64 bits
- Guzzle 6

## Seafile Web API V2 Support Matrix

| Resource               | Support grade |
| ---------------------- | ------------- |
| Account                | :large_blue_circle::large_blue_circle::large_blue_circle::white_circle: |
| Starred Files          | :large_blue_circle::large_blue_circle::large_blue_circle::large_blue_circle:          |
| Group                  | :large_blue_circle::white_circle::white_circle::white_circle:   |
| File Share Link        | :large_blue_circle::large_blue_circle::white_circle::white_circle:       |
| Library/Library        | :large_blue_circle::large_blue_circle::white_circle::white_circle:       |
| Library/File           | :large_blue_circle::large_blue_circle::white_circle::white_circle:       |
| Library/Directory      | :large_blue_circle::large_blue_circle::white_circle::white_circle:       |
| Library/Multiple Files | :large_blue_circle::large_blue_circle::large_blue_circle::large_blue_circle:          |
| Avatar                 | :large_blue_circle::large_blue_circle::large_blue_circle::large_blue_circle:          |
| Events                 | Yet to be done, [contact me](rene+_gth@sdo.sh) |
| Organization           | Yet to be done, [contact me](rene+_gth@sdo.sh) |


## Seafile Web API V2.1 Support Matrix

| Resource               | Support grade |
| ---------------------- | ------------- |
| Web API v2.1           | Yet to be done, [contact me](rene+_gth@sdo.sh) |

## Seafile server compatibility

Tested with:

- ~~Seafile Server 5.1.3 for generic Linux/Debian Jessie~~
- ~~Seafile Server 5.1.3 for generic Linux/Debian Wheezy~~
- ~~Seafile Server 5.1.4 for generic Linux/Ubuntu Xenial~~
- ~~Seafile Server 6.0.3 for generic Linux/Ubuntu Xenial~~
- Seafile Server 6.3.4 for generic Linux/Ubuntu Xenial

## Contributing

Please note that this package still is in its infancy. Only a part of the API has been implemented so far.

**Pull requests are welcome**. Please adhere to some very basic and simple principles:

- Follow "separation of concern" on all levels: 1 issue == 1 pull request. Do not cover multiple issues in a pull request.
- Unit tests raise the chance of your pull request getting accepted.
- The same goes for [PHPDoc](https://en.wikipedia.org/wiki/PHPDoc) blocks.

## Tests

There are two types of tests:

1. Unit tests that test a code unit without external dependencies and no data manipulation. Please always provide at least unit tests when contributing.
1. Functional tests that run against a live server instance (=may have external dependencies) and also alter data. Disabled and thus skipped by default. Please refer to `/phpunit/php` in `phpunit.xml.dist` for information on how to enable functional tests.

## Links

- https://seafile.com
- https://download.seafile.com/published/seafile-manual/develop/web_api_v2.1.md
- https://sdo.sh
- https://luckycloud.de/de/ (Seafile server hosting in Germany)
- https://www.yoursecurecloud.de/ (Seafile server hosting in Germany)

## License

[MIT](https://raw.githubusercontent.com/rene-s/seafile-php-sdk/master/LICENSE) &copy; 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG
