<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\ClassForbiddenNameCheck;
use PHPStan\Rules\ClassNameCheck;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use const PHP_VERSION_ID;

/**
 * @extends RuleTestCase<RequireImplementsDefinitionTraitRule>
 */
class RequireImplementsDefinitionTraitRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		$reflectionProvider = $this->createReflectionProvider();

		return new RequireImplementsDefinitionTraitRule(
			$reflectionProvider,
			new ClassNameCheck(
				new ClassCaseSensitivityCheck($reflectionProvider, true),
				new ClassForbiddenNameCheck(),
			),
			true,
		);
	}

	public function testRule(): void
	{
		$enumError = 'PHPDoc tag @phpstan-require-implements cannot contain non-interface type IncompatibleRequireImplements\SomeEnum.';
		$enumTip = null;
		if (PHP_VERSION_ID < 80100) {
			$enumError = 'PHPDoc tag @phpstan-require-implements contains unknown class IncompatibleRequireImplements\SomeEnum.';
			$enumTip = 'Learn more at https://phpstan.org/user-guide/discovering-symbols';
		}

		$expectedErrors = [
			[
				'PHPDoc tag @phpstan-require-implements cannot contain non-interface type IncompatibleRequireImplements\SomeTrait.',
				8,
			],
			[
				$enumError,
				13,
				$enumTip,
			],
			[
				'PHPDoc tag @phpstan-require-implements contains unknown class IncompatibleRequireImplements\TypeDoesNotExist.',
				18,
				'Learn more at https://phpstan.org/user-guide/discovering-symbols',
			],
			[
				'PHPDoc tag @phpstan-require-implements cannot contain non-interface type IncompatibleRequireImplements\SomeClass.',
				24,
			],
			[
				'PHPDoc tag @phpstan-require-implements contains non-object type int.',
				29,
			],
			[
				'PHPDoc tag @phpstan-require-implements contains non-object type *NEVER*.',
				34,
			],
		];

		$this->analyse([__DIR__ . '/data/incompatible-require-implements.php'], $expectedErrors);
	}

}
