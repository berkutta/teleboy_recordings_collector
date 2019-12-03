# Teleboy recordings collector

## Description

This is a very tiny and basic script which crawls your Teleboy Account for not yet downloaded recordings and downloads them together with metadata to your disk. This is very useful to get over the 365 days deletion period of Teleboy. You need a paid Account to be able to download recordings.

## Installation

* Complete config.inc.php with your Teleboy credentials.

* Run the following command every day via e.g. Cronjob:
```
php runner.php
```

(It looks like the Teleboy Login Endpoint is very strictly rate limited, it throws a 429 very fast. Thus I don't recomend to run this script faster than every some hours.)