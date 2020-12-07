<?php

declare(strict_types=1);

namespace Craftzing\Redemption\Constraints;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\ExceptionMessage;
use PHPUnit\Framework\ExpectationFailedException;

use function gettype;
use function is_callable;
use function sprintf;

/**
 * @internal This class is not covered by the backward compatibility promise for Redemption
 */
final class AssertionFailsWithDescription extends Constraint
{
    private string $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

    protected function matches($other): bool
    {
        if (! is_callable($other)) {
            $this->fail($other, sprintf(
                'Please make sure to pass a callable assertion for evaluation. Received [%s].',
                gettype($other),
            ));
        }

        try {
            $other();
            $this->fail($other, "The assertion was expected to fail, but it didn't.");
        } catch (ExpectationFailedException $e) {
            Assert::assertThat(
                $e,
                new ExceptionMessage($this->description),
                'The assertion failed, but not with the expected message.',
            );
        }

        return true;
    }

    public function toString(): string
    {
        return "assertion failed with description `{$this->description}`";
    }
}
