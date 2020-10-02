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
        $this->timerPrecision = 2;
    }

    /**
     * @param array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        if (!isset($record['context']['timer']) || !is_array($record['context']['timer'])) {
            return $record;
        }

        foreach ($record['context']['timer'] as &$timerName) {

            if (!isset($this->timers[$timerName])) {
                $this->timers[$timerName] = [
                    'lastTime' => null,
                    'count' => 0,
                    'start' => microtime(true)
                ];
                $timerName .= ': Start';

            } else {
                if (isset($this->timers[$timerName]['start'])) {
                    $currentTime = microtime(true);
                    $lastTime = !empty($this->timers[$timerName]['lastTime']) ? $this->timers[$timerName]['lastTime'] : $this->timers[$timerName]['start'];

                    $sinceStart = $currentTime - $this->timers[$timerName]['start'];
                    $sinceLast = $currentTime - $lastTime;

                    $this->timers[$timerName]['lastTime'] = $currentTime;
                    $this->timers[$timerName]['count']++ ;


                    $timerName .= ': Start';
                    $timerName .= ': TotalTime:'.$sinceStart;
                    $timerName .= ': SinceLast:'.$sinceLast;
                    $timerName .= ': Count:'.$this->timers[$timerName]['count'];
                }
            }
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
}
