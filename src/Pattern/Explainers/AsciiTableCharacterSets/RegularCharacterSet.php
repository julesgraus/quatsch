<?php
namespace JulesGraus\Quatsch\Pattern\Explainers\AsciiTableCharacterSets;

class RegularCharacterSet implements AsciiTableCharacterSet
{

    public function topLeftCornerCharacter(): string
    {
        return '+';
    }

    public function topRightCornerCharacter(): string
    {
        return '+';
    }

    public function bottomLeftCornerCharacter(): string
    {
        return '+';
    }

    public function bottomRightCornerCharacter(): string
    {
        return '+';
    }

    public function tLeftCharacter(): string
    {
        return '+';
    }

    public function tRightCharacter(): string
    {
        return '+';
    }

    public function tBottomCharacter(): string
    {
        return '+';
    }

    public function tTopCharacter(): string
    {
        return '+';
    }

    public function crossCharacter(): string
    {
        return '+';
    }

    public function verticalLineCharacter(): string
    {
        return '|';
    }

    public function horizontalLineCharacter(): string
    {
        return '-';
    }
}
