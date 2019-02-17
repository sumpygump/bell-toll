# Bell Toll

This is a PHP script (command line) that will chime like a clock tower. It has audio files for the 15-minute, 30-minute, 45-minute bell tolls, plus each hour.

## Installation

Install with composer using the following command

```
composer global require sumpygump/belltoll
```

Make sure that `~/.composer/vendor/bin/` is on your `$PATH`

Or symlink it to the the `/usr/local/bin`

```
$ ln -s ~/.composer/vendor/bin/belltoll /usr/local/bin/belltoll
```

## Usage

When you invoke the program it will chime like a bell tower if it is the right time. Simple run `belltoll`. If it is not the right time it will say "It's not time for a bell."

The program relies on `mpg123` to be installed on your machine to actually play the audio files.

You can pass the `-t` or `--time` parameter to force the program to think it is a specific time (for testing). `belltoll -t 1:30`

Use the `-q` or `--quiet` to make the program have no output.

### Install as cron task

Since it relies on running at the right time, it is suggested to add it to your crontab. Use the command `$ crontab -e` to edit yours. See the file `crontab.example` for additional details.

```
00,15,30,45 * * * * /usr/local/bin/belltoll >> /dev/null 2>&1
```
