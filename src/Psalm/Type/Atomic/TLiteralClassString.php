<?php
namespace Psalm\Type\Atomic;

use Psalm\CodeLocation;
use Psalm\StatementsSource;
use function preg_quote;
use function preg_replace;
use function stripos;
use function strtolower;

class TLiteralClassString extends TLiteralString
{
    /**
     * @param string $value string
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return 'class-string';
    }

    /**
     * @return string
     */
    public function getKey(bool $include_extra = true)
    {
        return 'class-string(' . $this->value . ')';
    }

    /**
     * @param  string|null   $namespace
     * @param  array<string> $aliased_classes
     * @param  string|null   $this_class
     * @param  int           $php_major_version
     * @param  int           $php_minor_version
     *
     * @return string
     */
    public function toPhpString(
        $namespace,
        array $aliased_classes,
        $this_class,
        $php_major_version,
        $php_minor_version
    ) {
        return 'string';
    }

    /**
     * @return bool
     */
    public function canBeFullyExpressedInPhp()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getId(bool $nested = false)
    {
        return $this->value . '::class';
    }

    /**
     * @return string
     */
    public function getAssertionString()
    {
        return $this->getKey();
    }

    /**
     * @param  string|null   $namespace
     * @param  array<string> $aliased_classes
     * @param  string|null   $this_class
     * @param  bool          $use_phpdoc_format
     *
     * @return string
     */
    public function toNamespacedString(
        ?string $namespace,
        array $aliased_classes,
        ?string $this_class,
        bool $use_phpdoc_format
    ) {
        if ($this->value === 'static') {
            return 'static::class';
        }

        if ($this->value === $this_class) {
            return 'self::class';
        }

        if ($namespace && stripos($this->value, $namespace . '\\') === 0) {
            return preg_replace(
                '/^' . preg_quote($namespace . '\\') . '/i',
                '',
                $this->value
            ) . '::class';
        }

        if (!$namespace && stripos($this->value, '\\') === false) {
            return $this->value . '::class';
        }

        if (isset($aliased_classes[strtolower($this->value)])) {
            return $aliased_classes[strtolower($this->value)] . '::class';
        }

        return '\\' . $this->value . '::class';
    }

    /**
     * @param  StatementsSource $source
     * @param  CodeLocation     $code_location
     * @param  array<string>    $suppressed_issues
     * @param  array<string, bool> $phantom_classes
     * @param  bool             $inferred
     *
     * @return false|null
     */
    public function check(
        StatementsSource $source,
        CodeLocation $code_location,
        array $suppressed_issues,
        array $phantom_classes = [],
        bool $inferred = true,
        bool $inherited = false,
        bool $prevent_template_covariance = false
    ) {
        if ($this->checked) {
            return;
        }

        if (\Psalm\Internal\Analyzer\ClassLikeAnalyzer::checkFullyQualifiedClassLikeName(
            $source,
            $this->value,
            $code_location,
            null,
            $suppressed_issues,
            $inferred,
            false,
            true,
            $this->from_docblock
        ) === false
        ) {
            return false;
        }

        $this->checked = true;
    }
}
