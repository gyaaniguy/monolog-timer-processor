<?php

namespace Gyaaniguy\Monolog\Processor;

use Monolog\Handler\TestHandler;
use Monolog\Logger;

/**
 * Namespaced function that takes precedence over the global one
 *
 * @return float
 */
function microtime()
{
    return MicrotimeStub::getMicrotime();
}

class MicrotimeStub
{
    /**
     * @var null|double
     */
    private static $microtime = null;

    /**
     * @return double
     */
    public static function getMicrotime()
    {
        if (null === self::$microtime) {
            return \microtime(true);    // the global one
        } else {
            return self::$microtime;
        }
    }

    /**
     * @param double $microtime
     */
    public static function setMicrotime($microtime)
    {
        self::$microtime = $microtime;
    }

    public static function resetMicrotime()
    {
        self::setMicrotime(null);
    }
}


class TimerProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestHandler
     */
    private $testHandler;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Configure Subject Under Test
     *
     * @param string $level threshold for the TestHandler
     */
    private function configureSut($level = Logger::DEBUG)
    {
        $sut = new TimerProcessor();
        $this->testHandler = new TestHandler($level);
        $this->logger = new \Monolog\Logger('test', array($this->testHandler));
        $this->logger->pushProcessor($sut);
        return $sut;
    }

    public function testTimer()
    {
        $sut = $this->configureSut();
        MicrotimeStub::setMicrotime(1470000000);
        $this->logger->log(Logger::DEBUG, "test", ['timer' => ['tag1']]);
        MicrotimeStub::setMicrotime(1470000001);
        $this->logger->log(Logger::DEBUG, "test", ['timer' => ['tag1']]);

        $records = $this->testHandler->getRecords();
        $this->assertEquals(
            array(
                'timer' => [
                    'tag1' => [
                        'Total' => 1,
                        'SinceLast' => 1,
                        'Count' => 2,
                    ]
                ]
            ),
            $records[1]['context']
        );

    }

    public function testMultipleTimers()
    {
        $sut = $this->configureSut();

        MicrotimeStub::setMicrotime(1470000000);
        $this->logger->log(Logger::DEBUG, "test", array('timer' => ['foo','bar']));

        MicrotimeStub::setMicrotime(1470000001);
        $this->logger->log(Logger::DEBUG, "test", array('timer' => ['foo']));

        MicrotimeStub::setMicrotime(1470000002);
        $this->logger->log(Logger::DEBUG, "test", array('timer' => ['foo','bar']));

        $records = $this->testHandler->getRecords();
        $this->assertEquals(
            array(
                'timer' => array(
                    'foo' => array(
                        'Total' => 2,
                        'SinceLast' => 1,
                        'Count' => 3
                    ),
                    'bar' => array(
                        'Total' => 2,
                        'SinceLast' => 2,
                        'Count' => 2
                    )
                )
            ),
            $records[2]['context']
        );

        MicrotimeStub::setMicrotime(1470000005);
        $this->logger->log(Logger::DEBUG, "test", array('timer' => 'foo'));

        $records = $this->testHandler->getRecords();
        $this->assertEquals(
            array(
                'timer' => array(
                    'foo' => array(
                        'Total' => 5,
                        'SinceLast' => 3,
                        'Count' => 4
                    )
                )
            ),
            $records[3]['context']
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'Total' => 5,
                    'SinceLast' => 3,
                    'Count' => 4
                ),
                'bar' => array(
                    'Total' => 2,
                    'SinceLast' => 2,
                    'Count' => 2
                )
            ),
            $sut->getTimers()
        );
    }

    public function testAccumulatedTime()
    {
        $sut = $this->configureSut();

        MicrotimeStub::setMicrotime(1470000000);
        $this->logger->log(Logger::DEBUG, "test", array('timer' => array('foo')));

        MicrotimeStub::setMicrotime(1470000001);
        $this->logger->log(Logger::DEBUG, "test", array('timer' => 'foo'));

        MicrotimeStub::setMicrotime(1470000004);
        $this->logger->log(Logger::DEBUG, "test", array('timer' => array('foo')));
        MicrotimeStub::setMicrotime(1470000007);
        $this->logger->log(Logger::DEBUG, "test", array('timer' => array('foo')));


        $records = $this->testHandler->getRecords();
        $this->assertEquals(
            array(
                'timer' => array(
                    'foo' => array(
                        'Total' => 7,
                        'SinceLast' => 3,
                        'Count' => 4

                    )
                )
            ),
            $records[3]['context']
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'Total' => 7,
                    'SinceLast' => 3,
                    'Count' => 4
                )
            ),
            $sut->getTimers()
        );
    }

}
