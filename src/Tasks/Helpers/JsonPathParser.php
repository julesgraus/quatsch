<?php

namespace JulesGraus\Quatsch\Tasks\Helpers;

class JsonPathParser
{
    public function parse(string $input): array
    {
        $result = [];

        $input = preg_replace_callback('/\[(["\']?)(.*?)\1\]/', function ($matches) {
            return '.' . $matches[2];
        }, $input);


        $parts = explode('.', $input);

        foreach ($parts as $part) {
            if (is_numeric($part)) {
                $result[] = (int)$part; // Add as an integer if the part is numeric
            } else {
                $result[] = trim($part, ' "\''); // Remove quotes from string parts
            }
        }

        return $result;
    }
}