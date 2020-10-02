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
            foreach ($record['context']['timer'] as &$timerName) {
                $timerName = $this->handleTimer($timerName);
            }
        } elseif (is_string($record['context']['timer']) && $record['context']['timer']) {
            $record['context']['timer'] = [$this->handleTimer($record['context']['timer'])];
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
        $out = [];
        if (!isset($this->timers[$timerName])) {
            $this->timers[$timerName] = [
                'lastTime' => null,
                'count' => 0,
                'start' => microtime(true)
            ];
            $out[$timerName] = ['Start'];

        } else {
            $currentTime = microtime(true);
            $lastTime = !empty($this->timers[$timerName]['lastTime']) ? $this->timers[$timerName]['lastTime'] : $this->timers[$timerName]['start'];

            $sinceStart = $currentTime - $this->timers[$timerName]['start'];
            $sinceLast = $currentTime - $lastTime;

            $this->timers[$timerName]['lastTime'] = $currentTime;
            $this->timers[$timerName]['count']++;

            $res['Total'] = number_format($sinceStart, $this->timerPrecision);
            $res['SinceLast'] = number_format($sinceLast, $this->timerPrecision);
            $res['Count'] = $this->timers[$timerName]['count'];

            $out[$timerName] = $res;
        }
        return $out;
    }
}
