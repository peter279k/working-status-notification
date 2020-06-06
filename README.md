# working-status-notification

## Introduction

This is notification for working status

## Usage

- Install dependencies via `composer install`
- Editing `sender.sh` and set required environment variables
- Executing `sender.sh` BASH script
- And it can receive mail contents about tomorrow working date status

## Cronjob Integration

It can use following cronjob expression to do work status notification.

```
00 23 * * * cd /home/user_name/working-status-notification/ && bash /home/user_name/working-status-notification/sender.sh
```
