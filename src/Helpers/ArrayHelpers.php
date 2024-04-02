<?php

namespace DomainParser\Helpers;

use function PHPUnit\Framework\stringContains;

class ArrayHelpers
{
    public static function groupTlds(array $lines): array
    {
        $output = [];

        foreach ($lines as $value) {
            $value = trim($value);
            $value = idn_to_ascii($value);
            $value = str_replace('*.', '', $value);

            if ( ! stringContains($value, '.')) {
                $output[$value] = [$value];
                break;
            }

            $levels    = explode('.', $value);
            $lastLevel = end($levels);

            if ( ! isset($output[$lastLevel])) {
                $output[$lastLevel] = [];
            }

            $output[$lastLevel][] = $value;
        }

        return $output;
    }

    public static function removeStartsWith(array $haystack, array $needles): array
    {
        foreach ($haystack as $key => $value) {
            $value = trim($value);

            foreach ($needles as $needle) {
                if (str_starts_with($value, $needle)) {
                    unset($haystack[$key]);
                    break;
                }
            }

            if (empty($value)) {
                unset($haystack[$key]);
            }
        }

        return array_values($haystack);
    }
}
