<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests;

use IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\RedundantInheritedPhpdocRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

/**
 * @extends RuleTestCase<RedundantInheritedPhpdocRule>
 */
class RedundantInheritedPhpdocRuleTest extends RuleTestCase
{
    protected const string DATA = 'IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data';

    public function test_a_param_tag_repeating_the_inherited_type_is_reported_matching_parameters_by_position(): void
    {
        $parent = self::DATA . '\Param\Cache::get()';

        $this->analyse([__DIR__ . '/data/param.php'], [
            [self::paramMessage('key', $parent), 17],
            [self::paramMessage('default', $parent), 17],
            [self::paramMessage('name', $parent), 30],
        ]);
    }

    public function test_a_return_tag_repeating_the_inherited_type_is_reported(): void
    {
        $this->analyse([__DIR__ . '/data/return.php'], [
            [self::tagMessage('@return', self::DATA . '\ReturnTag\Finder::ids()'), 17],
        ]);
    }

    public function test_a_throws_tag_repeating_the_inherited_type_is_reported(): void
    {
        $this->analyse([__DIR__ . '/data/throws.php'], [
            [self::tagMessage('@throws', self::DATA . '\Throws\Loader::load()'), 23],
        ]);
    }

    public function test_a_tag_repeating_an_undocumented_parents_native_type_is_reported(): void
    {
        $parent = self::DATA . '\NativeParentType\Named::rename()';

        $this->analyse([__DIR__ . '/data/native-parent-type.php'], [
            [self::paramMessage('name', $parent), 14],
            [self::tagMessage('@return', $parent), 14],
        ]);
    }

    public function test_phpstan_and_psalm_prefixed_tags_are_reported_like_unprefixed_ones(): void
    {
        $parent = self::DATA . '\PrefixedTags\Cache::get()';

        $this->analyse([__DIR__ . '/data/prefixed-tags.php'], [
            [self::paramMessage('key', $parent), 18],
            [self::tagMessage('@return', $parent), 18],
        ]);
    }

    public function test_tags_whose_type_differs_from_the_inherited_one_are_allowed(): void
    {
        $this->analyse([__DIR__ . '/data/different-types.php'], []);
    }

    public function test_the_inherited_type_is_compared_with_the_parents_templates_resolved(): void
    {
        $this->analyse([__DIR__ . '/data/templates.php'], [
            [self::tagMessage('@return', self::DATA . '\Templates\Repository::find()'), 25],
        ]);
    }

    public function test_a_method_from_both_a_parent_class_and_an_interface_inherits_from_the_parent_class(): void
    {
        $this->analyse([__DIR__ . '/data/parent-class-and-interface.php'], [
            [self::paramMessage('count', self::DATA . '\ParentClassAndInterface\Base::take()'), 27],
        ]);
    }

    public function test_a_description_on_an_overriding_method_is_reported_but_not_a_tags_description(): void
    {
        $this->analyse([__DIR__ . '/data/description.php'], [
            [self::descriptionMessage(self::DATA . '\Description\Cache::ids()'), 17],
        ]);
    }

    public function test_inherit_doc_is_reported_whether_inline_or_a_tag(): void
    {
        $this->analyse([__DIR__ . '/data/inherit-doc.php'], [
            [self::inheritDocMessage(self::DATA . '\InheritDoc\Cache::clear()'), 16],
            [self::inheritDocMessage(self::DATA . '\InheritDoc\Cache::flush()'), 24],
        ]);
    }

    public function test_phpdoc_on_constructors_private_redeclarations_new_methods_and_other_tags_is_allowed(): void
    {
        $this->analyse([__DIR__ . '/data/not-overriding.php'], []);
    }

    public function test_a_trait_method_is_checked_against_the_method_it_overrides_in_the_using_class(): void
    {
        $this->analyse([__DIR__ . '/data/trait.php'], [
            [self::tagMessage('@return', self::DATA . '\TraitMethod\Cache::ids()'), 17],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new RedundantInheritedPhpdocRule(self::getContainer()->getByType(FileTypeMapper::class));
    }

    protected static function paramMessage(string $name, string $parent): string
    {
        return "PHPDoc tag @param for \$$name repeats the type inherited from $parent; remove it.";
    }

    protected static function tagMessage(string $tag, string $parent): string
    {
        return "PHPDoc tag $tag repeats the type inherited from $parent; remove it.";
    }

    protected static function descriptionMessage(string $parent): string
    {
        return "PHPDoc of a method overriding $parent has a description; document $parent instead, and put notes on this implementation inside the method.";
    }

    protected static function inheritDocMessage(string $parent): string
    {
        return "PHPDoc inheritDoc is redundant: PHPStan inherits the PHPDoc of $parent; remove it.";
    }
}
