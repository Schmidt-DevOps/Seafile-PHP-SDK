# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0]

This version will not be backwards compatible with Version 1.x. 

### Todo
- Abstract SharedLinkPermissions
- Switch to .env instead of JSON config

### Added
- Will support PHP 7.2+ only.
- Requires PHPUnit 8+
- Added CHANGELOG.md
- Added `auth_ping.php` for testing the authorization token
- Started supporting Web API v2.1
- Added support for Share-Links API resource
- Added support for multiple permissions for shared links via sdo/bitmask

### Changed
- Updated the docs regarding obtaining an authorization token
- SharedLinkType updated for Web API v2.1

### Removed
- `obtain_api_token.sh` script
- Support for Shared-Link API resource (succeeded by Share-Links API resource)

## [1.0.1] - 2017-06-23

First stable release

