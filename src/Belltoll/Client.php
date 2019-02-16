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
     * Set the time
     *
     * @param string|int $time Time
     * @return object Self
     */
    public function setTime($time = null)
    {
        if (null === $time) {
            if ($this->_args->time) {
                $time = $this->parseTime($this->_args->time);
            } else {
                $time = time();
            }
        }

        $this->_time = $time;

        return $this;
    }

    /**
     * Parse time
     *
     * @param string $input Input value
     * @return int
     */
    protected function parseTime($input)
    {
        $time = 0;

        preg_match('#(\d+):(\d+)#', $input, $matches);

        if (count($matches) == 3) {
            $time = mktime($matches[1], $matches[2], 0);
        } else {
            $time = mktime(0, (int) $input, 0);
        }

        return $time;
    }

    /**
     * Execute the bell toll
     *
     * @return int
     */
    public function execute()
    {
        if ($this->_time == 0) {
            $this->setTime();
        }

        $this->_displayMessage('Audio path: ' . $this->_audioPath);
        $this->_displayMessage('Using time: ' . date('Y-m-d H:i:s', $this->_time));

        // select audio file based on time
        $file = $this->_selectAudioFile();

        $this->_displayMessage('Selected audio file: ' . $file);

        $cmd = sprintf("mpg123 -q %s", $file);
        $this->_displayMessage('Command: ' . $cmd);

        passthru($cmd);

        return 0;
    }

    /**
     * Select appropriate audio file based on time
     *
     * @return void
     */
    protected function _selectAudioFile()
    {
        $minutes = date('i', $this->_time);
        $hour    = date('g', $this->_time);

        if (!in_array($minutes, array('15', '30', '45', '00'))) {
            throw new \Exception("It's not time for a bell.");
        }

        $filename = $this->_audioPath . DIRECTORY_SEPARATOR . $minutes;

        if ($minutes == '00') {
            $filename .= '-' . $hour;
        }

        $filename .= '.mp3';

        return $filename;
    }
}
