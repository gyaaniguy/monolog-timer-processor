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
        if (!isset($this->timers[$timerName])) {
            $this->timers[$timerName] = [
                'lastTime' => null,
                'count' => 1,
                'start' => microtime(true)
            ];
            $out = ['Start'];

        } else {
            $currentTime = microtime(true);
            $lastTime = !empty($this->timers[$timerName]['lastTime']) ? $this->timers[$timerName]['lastTime'] : $this->timers[$timerName]['start'];

            $sinceStart = $currentTime - $this->timers[$timerName]['start'];
            $sinceLast = $currentTime - $lastTime;

            $this->timers[$timerName]['lastTime'] = $currentTime;
            $this->timers[$timerName]['count']++;

            $res['Total'] = $this->trailingZeros(number_format($sinceStart, $this->timerPrecision));
            $res['SinceLast'] = $this->trailingZeros(number_format($sinceLast, $this->timerPrecision));
            $res['Count'] = $this->timers[$timerName]['count'];

            $out = $res;
        }
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
