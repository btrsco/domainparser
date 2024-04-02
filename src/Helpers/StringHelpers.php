<?php

namespace DomainParser\Helpers;

class StringHelpers
{
    public static function between($haystack, $start, $end): string
    {
        $startPos = strpos($haystack, $start);
        $startPos += strlen($start);
        $endPos   = strpos($haystack, $end, $startPos) - $startPos;

        return substr($haystack, $startPos, $endPos);
    }
}
