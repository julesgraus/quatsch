<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Pattern;


use AllowDynamicProperties;
use JulesGraus\Quatsch\Pattern\Concerns\CompilesPatterns;
use JulesGraus\Quatsch\Pattern\Concerns\HasAnchors;
use JulesGraus\Quatsch\Pattern\Concerns\HasCharacterClasses;
use JulesGraus\Quatsch\Pattern\Concerns\HasGroups;
use JulesGraus\Quatsch\Pattern\Concerns\HasLookAround;
use JulesGraus\Quatsch\Pattern\Concerns\HasMetaPatterns;
use JulesGraus\Quatsch\Pattern\Concerns\HasQuantifiers;
use JulesGraus\Quatsch\Pattern\Concerns\InspectsAndTestsPatterns;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Enums\Type;

class Pattern
{
    use CompilesPatterns;
    use HasQuantifiers;
    use HasLookAround;
    use HasMetaPatterns;
    use HasAnchors;
    use HasCharacterClasses;
    use HasGroups;
    use InspectsAndTestsPatterns;

    public function __construct()
    {
        $this->type = Type::CONTAINS;
        $this->description = 'The start of the pattern';
    }

    public static function create(): Pattern
    {
        return new static();

    }

    private null|string $delimiter = '/';
    private string $ownPattern = '';
    private string $description = '';

    /** @var array<int, RegexModifier> */
    private array $modifiers = [];

    private Type $type;


    /** @var array<int, Pattern> $subPatterns */
    private array $subPatterns = [];
}
