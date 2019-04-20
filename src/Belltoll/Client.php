<?php
/**
 * Belltoll Client class file
 *
 * @package Belltoll
 */

namespace Belltoll;

use Qi_Console_Client;

/**
 * Belltoll Client
 *
 * @package Belltoll
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class Client extends Qi_Console_Client
{
    const VERSION = '1.1';

    const TIME_FORMAT_HOUR = 'g';
    const TIME_FORMAT_MINUTE = 'i';

    /**
     * Path where audio files are
     *
     * @var string
     */
    protected $_audioPath = '';

    /**
     * Time
     *
     * @var int
     */
    protected $_time = 0;

    /**
     * Set audio path
     *
     * @param string $path Path
     * @return object Self
     */
    public function setAudioPath($path)
    {
        $this->_audioPath = $path;
        return $this;
    }

    /**
     * Get audio path
     *
     * @return string
     */
    public function getAudioPath()
    {
        return $this->_audioPath;
    }

    /**
     * Set the time
     *
     * @param string|int $time Time
     * @return object Self
     */
    public function setTime($time = null)
    {
        if (null === $time) {
            $time = time();
        } else {
            $time = $this->parseTime($time);
        }

        $this->_time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return string
     */
    public function getTime($format = 'Y-m-d H:i:s')
    {
        if (false === $format) {
            return $this->_time;
        }

        return date($format, $this->_time);
    }

    /**
     * Parse time
     *
     * @param string $input Input value
     * @return int
     */
    protected function parseTime($input)
    {
        if (is_numeric($input) && $input > 2359) {
            // Detect timestamp
            return $input;
        }

        preg_match('#(\d+):(\d+)#', $input, $matches);

        // Assuming hour:minute
        if (count($matches) == 3) {
            return mktime($matches[1], $matches[2], 0);
        }

        // Assuming just minutes
        if ($input < 60 || (int) $input == 0) {
            return mktime(0, (int) $input, 0);
        }

        // Attempt to parse it with strtotime
        $time = strtotime($input);

        if ($time == 0) {
            return time();
        } else {
            return $time;
        }
    }

    /**
     * Execute the bell toll
     *
     * @return int
     */
    public function execute()
    {
        if ($this->_args->version) {
            $this->_displayMessage(sprintf('belltoll %s', self::VERSION));
            return 0;
        }

        if ($this->_args->help) {
            $this->displayUsage();
            return 0;
        }

        $is_quiet = (bool) $this->_args->quiet;
        $is_verbose = (bool) $this->_args->verbose && !$is_quiet;

        if ($this->_audioPath == '') {
            $this->_audioPath = BELLTOLL_ROOT . '/audio';
        }

        if ($this->_args->time) {
            $this->setTime($this->_args->time);
        } else {
            if ($this->_time == 0) {
                $this->setTime();
            }
        }

        if (!$is_quiet) {
            $this->_displayMessage('Audio path: ' . $this->getAudioPath());
            $this->_displayMessage('Using time: ' . $this->getTime());
        }

        // select audio file based on time
        $file = $this->_selectAudioFile();

        !$is_quiet && $this->_displayMessage('Selected audio file: ' . $file);

        $cmd = sprintf("mpg123%s %s", (!$is_verbose ? ' -q' : ''), $file);
        !$is_quiet && $this->_displayMessage('Command: ' . $cmd);

        if (!defined('BELLTOLL_NOEXECUTE')) {
            passthru($cmd);
        }

        return 0;
    }

    /**
     * Select appropriate audio file based on time
     *
     * @return string
     */
    protected function _selectAudioFile()
    {
        $minutes = $this->getTime(self::TIME_FORMAT_MINUTE);

        if (!in_array($minutes, array('15', '30', '45', '00'))) {
            throw new \Exception("It's not time for a bell.");
        }

        $filename = $minutes;

        if ($minutes == '00') {
            $filename = sprintf("%s-%s", $filename, $this->getTime(self::TIME_FORMAT_HOUR));
        }

        $file = sprintf("%s/%s.mp3", $this->getAudioPath(), $filename);

        if (!file_exists($file)) {
            throw new \Exception(sprintf("File '%s' not found!", $file));
        }

        return $file;
    }

    public function displayUsage()
    {
        print "belltoll " . self::VERSION . "\n";
        print "Usage: belltoll [options]\n";
        print "\n";
        print "Options:\n";
        print "  -h [--help] : Display help\n";
        print "  -q [--quiet] : No output\n";
        print "  -v [--verbose] : Include more verbose output\n";
        print "  -t [--time] <time> : Use specific time instead of current time\n";
        print "  --version : Show version and exit\n";
    }
}
