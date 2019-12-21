# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0]

This version will not be backwards compatible with Version 1.x. 

### Todo
- Abstract SharedLinkPermissions

### Added
- CHANGELOG.md
- `auth_ping.php` for testing the authorization token
- Basic support for Web API v2.1
- Support for Share-Links API resource
- Support for multiple permissions for Share-Links API resource
- Functional tests

### Changed
- Now supports PHP 7.2+ only.
- Now requires PHPUnit 8+
- Docs regarding obtaining an authorization token
- SharedLinkType updated for Web API v2.1
- Restructured the tests
- Restructured test configuration
- `phpunit.xml.dist` massively updated -- if you have a custom one, please also update. Otherwise the tests will be broken.
- Renamed package from `rsd/seafile-php-sdk` to `sdo/seafile-php-sdk`
- Moved git repository from https://github.com/rene-s to https://github.com/Schmidt-DevOps

### Removed
- `obtain_api_token.sh` script
- Support for Shared-Link API resource (succeeded by Share-Links API resource)
- Many example scripts (please refer to the functional tests instead)

## [1.0.1] - 2017-06-23

First stable release

