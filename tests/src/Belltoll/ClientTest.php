<?php

use PHPUnit\Framework\TestCase;
use Belltoll\Client;

define('BELLTOLL_NOEXECUTE', true);

final class ClientTest extends TestCase
{
    public function testInit()
    {
        $client = $this->createObject();
        $this->assertInstanceOf('Belltoll\\Client', $client);
    }

    public function testSetAudioPath()
    {
        $client = $this->createObject();

        $client->setAudioPath('xxx');
        $actual = $client->getAudioPath();
        $this->assertEquals('xxx', $actual);
    }

    public function testSetTime()
    {
        $client = $this->createObject();

        $client->setTime(23);
        $actual = $client->getTime();
        $this->assertEquals(date('Y-m-d 00:23:00'), $actual);
    }

    public function testSetTimeFalse()
    {
        $client = $this->createObject();

        $client->setTime(false);
        $actual = $client->getTime();
        $this->assertEquals(date('Y-m-d 00:00:00'), $actual);
    }

    public function testSetTimeEmpty()
    {
        $client = $this->createObject();

        $client->setTime();
        $actual = $client->getTime();
        $this->assertEquals(date('Y-m-d H:i:s'), $actual);
    }

    public function testGetTime()
    {
        $client = $this->createObject();

        $client->setTime();
        $actual = $client->getTime(false);
        $this->assertEquals(time(), $actual);
    }

    public function testParseTimeSettingNumeric()
    {
        $client = $this->createObject();

        $client->setTime(1843);
        $actual = $client->getTime();
        $this->assertEquals(date('Y-m-d 18:43:00'), $actual);
    }

    public function testParseTimeSettingNumericNonsense()
    {
        $client = $this->createObject();

        $client->setTime(200);
        $actual = $client->getTime();
        $this->assertEquals(date('Y-m-d H:i:s'), $actual);
    }

    public function testParseTimeSettingJustTimeComponent()
    {
        $client = $this->createObject();

        $client->setTime('8:43');
        $actual = $client->getTime();
        $this->assertEquals(date('Y-m-d 08:43:00'), $actual);
    }

    public function testParseTimeSettingFullDate()
    {
        $client = $this->createObject();

        $client->setTime('2019-02-28 14:00:07');
        $actual = $client->getTime();

        // (It only cares about the time)
        $this->assertEquals(date('2019-m-d 14:00:00'), $actual);
    }

    public function testParseTimeSettingTimestamp()
    {
        $client = $this->createObject();

        $client->setTime(time());
        $actual = $client->getTime();

        $this->assertEquals(date('Y-m-d H:i:s'), $actual);
    }

    public function testParseTimeSettingZero()
    {
        $client = $this->createObject();

        $client->setTime(0);
        $actual = $client->getTime();

        $this->assertEquals(date('Y-m-d 00:00:00'), $actual);
    }

    public function testParseTimeSettingZeroString()
    {
        $client = $this->createObject();

        $client->setTime('00');
        $actual = $client->getTime();

        $this->assertEquals(date('Y-m-d 00:00:00'), $actual);
    }

    public function testParseTimeMilitary()
    {
        $client = $this->createObject();

        $client->setTime('1400');
        $actual = $client->getTime();

        $this->assertEquals(date('Y-m-d 14:00:00'), $actual);
    }

    public function testParseTimeMilitaryAm()
    {
        $client = $this->createObject();

        $client->setTime('0230');
        $actual = $client->getTime();

        $this->assertEquals(date('Y-m-d 02:30:00'), $actual);
    }

    public function testParseTimeGarbage()
    {
        $client = $this->createObject();

        $client->setTime('nfdisajfdsahirewjirew');
        $actual = $client->getTime();

        $this->assertEquals(date('Y-m-d 00:00:00'), $actual);
    }

    public function testParseTimeGarbageWithColon()
    {
        $client = $this->createObject();

        $client->setTime('iurewireu:reuie');
        $actual = $client->getTime();

        $this->assertEquals(date('Y-m-d 00:00:00'), $actual);
    }

    public function testExecute()
    {
        $client = $this->createObject();

        $client->setTime('15');

        ob_start();
        $result = $client->execute();
        $output = ob_get_clean();

        $this->assertEquals(0, $result);
    }

    public function testExecuteSettingTimeViaArgs()
    {
        $client = $this->createObject(['prog', '--time', '15:30']);

        ob_start();
        $result = $client->execute();
        $output = ob_get_clean();

        $this->assertStringContainsString('15:30:00', $output);
        $this->assertEquals(0, $result);
    }

    public function testExecuteSettingTimeSpecificallyDoesntOverwrite()
    {
        $client = $this->createObject();
        $client->setTime('15:45');

        ob_start();
        $result = $client->execute();
        $output = ob_get_clean();

        $this->assertStringContainsString('15:45:00', $output);
        $this->assertEquals(0, $result);
    }

    public function testExecuteAutosetTime()
    {
        $client = $this->createObject(['prog', '-q']);

        $time = date('H:i:s');
        $minutes = date('i');

        $will_chime = false;
        if (in_array($minutes, array('15', '30', '45', '00'))) {
            $will_chime = true;
            ob_start();
            $result = $client->execute();
            $output = ob_get_clean();
            $this->assertStringContainsString($time, $output);
            $this->assertEquals(0, $result);
        } else {
            $this->expectException(\Exception::class);
            $result = $client->execute();
        }
    }

    public function testExecuteOnHour()
    {
        $client = $this->createObject();
        $client->setTime('13:00');

        ob_start();
        $result = $client->execute();
        $output = ob_get_clean();

        $this->assertStringContainsString('13:00:00', $output);
        $this->assertEquals(0, $result);
    }

    public function testExecuteNobell()
    {
        $client = $this->createObject(['prog', '-q']);
        $client->setTime('13:04');

        $this->expectException(\Exception::class, "It's not time for a bell");
        $result = $client->execute();
    }

    public function testExecuteWrongAudioPath()
    {
        $client = $this->createObject(['prog', '-q']);
        $client->setTime('13:45');
        $client->setAudioPath('foobar');

        $this->expectException(\Exception::class, "not found!");
        $result = $client->execute();
    }

    public function testVersion()
    {
        $client = $this->createObject(['prog', '--version']);

        ob_start();
        $result = $client->execute();
        $output = ob_get_clean();

        $this->assertStringContainsString('belltoll ' . Client::VERSION, $output);
        $this->assertEquals(0, $result);
    }

    public function testHelp()
    {
        $client = $this->createObject(['prog', '--help']);

        ob_start();
        $result = $client->execute();
        $output = ob_get_clean();

        $this->assertStringContainsString('belltoll ' . Client::VERSION, $output);
        $this->assertStringContainsString('--time', $output);
        $this->assertEquals(0, $result);
    }

    /**
     * Create the object under test
     *
     * @param array $args Args to mimic argv
     * @return Client
     */
    private function createObject($args = [])
    {
        $rules = array(
            'help|h'     => 'Show help',
            'verbose|v'  => 'Include more verbose output',
            'quiet|q'    => 'Print less messages',
            'version'    => 'Show version',
            'time|t:'    => 'Time to use',
        );

        $argv = new \Qi_Console_ArgV($args, $rules);
        $terminal = new \Qi_Console_Terminal();
        $client = new Client($argv, $terminal);
        //$client->setAudioPath($root . '/audio');

        return $client;
    }
}
