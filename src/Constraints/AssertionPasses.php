<?php

declare(strict_types=1);

namespace Craftzing\Redemption\Constraints;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;

use function gettype;
use function is_callable;
use function sprintf;

/**
 * @internal This class is not covered by the backward compatibility promise for Redemption
 */
final class AssertionPasses extends Constraint
{
    protected function matches($other): bool
    {
        if (! is_callable($other)) {
            $this->fail($other, sprintf(
                'Please make sure to pass a callable assertion for evaluation. Received [%s].',
                gettype($other),
            ));
        }

        $other();
        Assert::assertTrue(true);

        return true;
    }

    public function toString(): string
    {
        return 'passes';
    }
}
