<?php
namespace JulesGraus\Quatsch\Pattern\Explainers\AsciiTableCharacterSets;

interface AsciiTableCharacterSet
{
    public function topLeftCornerCharacter(): string;
    public function topRightCornerCharacter(): string;
    public function bottomLeftCornerCharacter(): string;
    public function bottomRightCornerCharacter(): string;
    public function tLeftCharacter(): string;
    public function tRightCharacter(): string;
    public function tTopCharacter(): string;
    public function tBottomCharacter(): string;
    public function crossCharacter(): string;
    public function verticalLineCharacter(): string;
    public function horizontalLineCharacter(): string;
}
