<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Pattern\Concerns;


use JulesGraus\Quatsch\Pattern\Enums\Type;
use JulesGraus\Quatsch\Pattern\Pattern;

trait HasQuantifiers
{
    use CompilesPatterns;

    /** @var array<int, Pattern> $subPatterns */
    private array $subPatterns = [];

    public function times(int $count): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '{' . $count . '}',
            'matches the previous token exactly '.$count.' times',
            Type::TIMES
        );
        return $this;
    }

    public function timesOrMore(int $count): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '{' . $count . ',}',
            'matches the previous '.$count.' times or more',
            Type::TIMES_OR_MORE
        );
        return $this;
    }

    public function optionally(): self
    {
        $this->subPatterns[] = self::stringToPattern('?',
            'matches zero on one times',
            Type::OPTIONALLY
        );
        return $this;
    }

    public function atLeastTimes(int $count): self
    {
        $this->subPatterns[] = self::stringToPattern('{' . $count . ',}',
            'matches the previous token at least '.$count.' times',
            Type::AT_LEAST_TIMES
        );
        return $this;
    }

    public function betweenTimes(int $from, int $to): self
    {
        $this->subPatterns[] = self::stringToPattern('{' . $from . ',' . $to .'}',
            'matches the previous token between '.$from.' and '.$to.' times',
            Type::BETWEEN_TIMES
        );
        return $this;
    }


    public function oneOrMoreTimes(): self
    {
        $this->subPatterns[] = self::stringToPattern('+',
            'matches the previous token one or more times',
            Type::ONE_OR_MORE_TIMES
        );

        return $this;
    }

    public function zeroOrMoreTimes(): self
    {
        $this->subPatterns[] = self::stringToPattern('*',
    'matches the previous token zero or more times',
            Type::ZERO_OR_MORE_TIMES
        );
        return $this;
    }

    public function asManyTimesAsPossible(): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '*',
            'matches the previous token as many times as possible',
            Type::AS_MANY_TIMES_AS_POSSIBLE
        );
        return $this;
    }

    public function asLeastTimesAsPossible(): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '*?',
            'matches the previous token as least times as possible',
            Type::AS_LEAST_TIMES_AS_POSSIBLE
        );
        return $this;
    }
}
