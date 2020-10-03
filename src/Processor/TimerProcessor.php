<?php

namespace Gyaaniguy\Monolog\Processor;

/**
 * Class TimerProcessor
 * @package Glopgar\Monolog\Processor
 */
class TimerProcessor
{
    /**
     * @var integer with the decimals to format the times
     */
    private $timerPrecision;

    /**
     * @var array
     */
    private $storePreviousData = array();
    private $timers = array();

    /**
     * @param int $timerPrecision
     * @param string $timeFormat
     */
    public function __construct($timerPrecision = 2)
    {
        $this->timerPrecision = 3;
    }

    /**
     * @param array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        if (!isset($record['context']['timer'])) {
            return $record;
        }

        if (is_array($record['context']['timer'])) {
            $originalTimer = $record['context']['timer'];
            $newTimer = [];
            foreach ($originalTimer as &$timerName) {
                $newTimer[$timerName] = $this->handleTimer($timerName);
            }
            $record['context']['timer'] = $newTimer;
        } elseif (is_string($record['context']['timer']) && $record['context']['timer']) {
            $originalTimer = $record['context']['timer'];
            $record['context']['timer'] = [$originalTimer => $this->handleTimer($originalTimer)];
        }
        return $record;
    }

    /**
     * @return array with the timers info
     */
    public function getTimers()
    {
        return $this->timers;
    }

    /**
     * @param $timerName
     * @return array
     */
    private function handleTimer($timerName)
    {
        if (!isset($this->storePreviousData[$timerName])) {
            $this->storePreviousData[$timerName] = [
                'lastTime' => null,
                'count' => 1,
                'start' => microtime(true)
            ];
            $out = ['Start'];

        } else {
            $res =[];
            $currentTime = microtime(true);
            $lastTime = !empty($this->storePreviousData[$timerName]['lastTime']) ? $this->storePreviousData[$timerName]['lastTime'] : $this->storePreviousData[$timerName]['start'];

            $sinceStart = $currentTime - $this->storePreviousData[$timerName]['start'];
            $sinceLast = $currentTime - $lastTime;

            $this->storePreviousData[$timerName]['lastTime'] = $currentTime;
            $this->storePreviousData[$timerName]['count']++;

            $res['Total'] = number_format($sinceStart, $this->timerPrecision);
            $res['SinceLast'] = number_format($sinceLast, $this->timerPrecision);
            $res['Count'] = $this->storePreviousData[$timerName]['count'];

            $out = $res;
        }
        $this->timers[$timerName] = $out;
        return $out;
    }

    function trailingZeros($num)
    {
        if(floor($num) == $num) {
            return number_format($num);
        } else {
            return $num;
        }
    }
}
