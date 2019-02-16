# Bell Toll

This is a PHP script (command line) that will chime like a clock tower. It has audio files for the 15-minute, 30-minute, 45-minute bell tolls, plus each hour.

## Installation

Checkout code to your computer. A good place is `/usr/local/share/php/bell-toll`

Make a symlink to the belltoll script.

```
$ ln -s /usr/local/share/php/bell-toll/belltoll /usr/local/bin/belltoll
```

## Suggested Usage

Add to your crontab `$ crontab -e`

```
00,15,30,45 * * * * /usr/local/bin/belltoll >> /dev/null 2>&1
```
