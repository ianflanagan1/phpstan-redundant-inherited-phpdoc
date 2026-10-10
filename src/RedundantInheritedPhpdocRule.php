<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;

/**
 * Reports PHPDoc on a method that overrides or implements another, where PHPStan would
 * inherit it anyway, so:
 *
 * ```php
 * /**
 *  * Gets a value from the cache
 *  *
 *  * @param non-empty-string $key
 *  *\/
 * #[Override]
 * public function get(string $key): mixed
 * ```
 *
 * should be written with no docblock, leaving the documentation on the interface.
 *
 * Reported: a @param, @return or @throws tag (or its @phpstan- or @psalm- form) whose type
 * equals the inherited one, a description, and {@inheritDoc}. Inherited types come from the
 * parent class if it has the method, otherwise from the first interface that does, with its
 * templates resolved, and parameters are matched by position, as PHPStan inherits them.
 *
 * Left alone: tags whose type differs from the inherited one (a narrower return, a wider
 * parameter), tags with no inherited counterpart, other tags (@deprecated, @phpstan-impure),
 * constructors, and methods whose parent method is private.
 *
 * @implements Rule<InClassMethodNode>
 */
final readonly class RedundantInheritedPhpdocRule implements Rule
{
    public function __construct(
        private FileTypeMapper $fileTypeMapper,
    ) {
    }

    #[Override]
    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $method = $node->getOriginalNode();
        $docComment = $method->getDocComment();
        $name = $method->name->toString();

        if ($docComment === null || strtolower($name) === '__construct') {
            return [];
        }

        $inherited = self::inheritedMethod($node->getClassReflection(), $name);

        if ($inherited === null) {
            return [];
        }

        $phpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
            $scope->getFile(),
            $scope->getClassReflection()?->getName(),
            $scope->getTraitReflection()?->getName(),
            $name,
            $docComment->getText(),
        );
        $parent = sprintf('%s::%s()', $inherited->getDeclaringClass()->getName(), $inherited->getName());
        $variant = $inherited->getOnlyVariant();
        $errors = [];

        $parentParameters = $variant->getParameters();

        foreach ($method->params as $position => $param) {
            $paramName = $param->var instanceof Variable && is_string($param->var->name)
                ? $param->var->name
                : null;
            $tag = $paramName !== null
                ? $phpDoc->getParamTags()[$paramName] ?? null
                : null;

            if ($tag !== null && isset($parentParameters[$position])
                && $tag->getType()->equals($parentParameters[$position]->getType())) {
                $errors[] = self::tagError("@param for \$$paramName", $parent);
            }
        }

        if (self::repeats($phpDoc->getReturnTag()?->getType(), $variant->getReturnType())) {
            $errors[] = self::tagError('@return', $parent);
        }

        if (self::repeats($phpDoc->getThrowsTag()?->getType(), $inherited->getThrowType())) {
            $errors[] = self::tagError('@throws', $parent);
        }

        $errors = [...$errors, ...self::textErrors($phpDoc, $parent)];

        return array_map(
            static fn (RuleErrorBuilder $error): IdentifierRuleError => $error->line($docComment->getStartLine())->build(),
            $errors,
        );
    }

    /**
     * The method PHPStan inherits PHPDoc from: the parent class's, otherwise the first interface's
     */
    private static function inheritedMethod(ClassReflection $class, string $name): ?ExtendedMethodReflection
    {
        $parent = $class->getParentClass();

        if ($parent !== null && $parent->hasNativeMethod($name)) {
            $method = $parent->getNativeMethod($name);

            if (!$method->isPrivate()) {
                return $method;
            }
        }

        foreach ($class->getInterfaces() as $interface) {
            if ($interface->hasNativeMethod($name)) {
                return $interface->getNativeMethod($name);
            }
        }

        return null;
    }

    private static function repeats(?Type $own, ?Type $inherited): bool
    {
        return $own !== null && $inherited !== null && $own->equals($inherited);
    }

    /**
     * Errors for a description or {@inheritDoc}, at most one of each
     *
     * @return list<RuleErrorBuilder<IdentifierRuleError>>
     */
    private static function textErrors(ResolvedPhpDocBlock $phpDoc, string $parent): array
    {
        $hasDescription = false;
        $hasInheritDoc = false;

        foreach ($phpDoc->getPhpDocNodes() as $phpDocNode) {
            foreach ($phpDocNode->children as $child) {
                $text = match (true) {
                    $child instanceof PhpDocTextNode => trim($child->text),
                    $child instanceof PhpDocTagNode => $child->name,
                    default => '',
                };

                if (in_array(strtolower($text), ['{@inheritdoc}', '@inheritdoc'], true)) {
                    $hasInheritDoc = true;
                } elseif ($child instanceof PhpDocTextNode && $text !== '') {
                    $hasDescription = true;
                }
            }
        }

        $errors = [];

        if ($hasDescription) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'PHPDoc of a method overriding %1$s has a description; document %1$s instead, and put notes on this implementation inside the method.',
                $parent,
            ))->identifier('phpDoc.overrideDescription');
        }

        if ($hasInheritDoc) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'PHPDoc inheritDoc is redundant: PHPStan inherits the PHPDoc of %s; remove it.',
                $parent,
            ))->identifier('phpDoc.redundantInherited');
        }

        return $errors;
    }

    /**
     * @return RuleErrorBuilder<IdentifierRuleError>
     */
    private static function tagError(string $tag, string $parent): RuleErrorBuilder
    {
        return RuleErrorBuilder::message(sprintf(
            'PHPDoc tag %s repeats the type inherited from %s; remove it.',
            $tag,
            $parent,
        ))->identifier('phpDoc.redundantInherited');
    }
}
