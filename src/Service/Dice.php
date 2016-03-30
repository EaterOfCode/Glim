<?php

namespace Eater\Glim\Service;

class Dice extends Main
{
    /**
     * @param string $die
     * @return int
     */
    public function throwDie($die)
    {
        return array_sum(array_map([$this, 'throwDice'], explode(" ", $die)));
    }

    /**
     * @param string $dice
     * @return int
     */
    public function throwDice($dice)
    {
        $success = preg_match("~^([0-9]+)(?:d([0-9]+))?(?:(\\+|\\-)([0-9]+))?$~i", $dice, $matches);

        if (!$success) {
            return 0;
        }

        $sum = 0;

        if (!empty($matches[1]) && empty($matches[2])) {
            $sum = intval($matches[1]);
        } else {
            $amountOfDie = intval($matches[1]);
            $eyes        = intval($matches[2]);
            
            for ($i = 0; $i < $amountOfDie; $i++)
            {
                $sum += rand(1, $eyes);
            }
        }
        
        if (!empty($matches[3])) {
            if ($matches[3] === "-") {
                $sum -= intval($matches[4]);
            } else if ($matches[3] === "+") {
                $sum += intval($matches[4]);
            }
        }

        return $sum;
    }
}