<?php

declare(strict_types=1);

namespace Craftzing\Redemption\Spies\Inspections;

use Craftzing\Redemption\Constraints\AssertionFailsWithDescription;
use Craftzing\Redemption\Constraints\AssertionPasses;
use Craftzing\Redemption\Spies\SpyCallable;
use Generator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;

use function array_reverse;
use function sprintf;

final class CalledMethodsTest extends TestCase
{
    private const EXPECTED_ARGUMENTS = ['expected', 'arguments'];

    public function withoutArguments(): Generator
    {
        yield 'It fails when it was called with any arguments' => [
            new CalledMethod($this, 'methodName', 'some', 'arguments'),
            new AssertionFailsWithDescription(sprintf(
                '[%s::methodName()] should not have been called with any arguments, but it was called with 2.',
                self::class,
            )),
        ];

        yield 'It passes when it was called without any arguments' => [
            new CalledMethod($this, 'methodName'),
            new AssertionPasses(),
        ];
    }

    /**
     * @test
     * @dataProvider withoutArguments
     */
    public function itCanAssertAMethodWasCalledWithoutArguments(
        CalledMethod $calledMethod,
        Constraint $passesOrFails
    ): void {
        $this->assertThat(function () use ($calledMethod) {
            $calledMethod->withoutArguments();
        }, $passesOrFails);
    }

    public function withArguments(): Generator
    {
        yield 'It fails when it was called without arguments' => [
            new CalledMethod($this, 'methodName'),
            new AssertionFailsWithDescription(sprintf(
                '[%s::methodName()] did not receive 1 argument(s) when called.',
                self::class,
            )),
        ];

        yield 'It fails when it was called with a missing arguments' => [
            new CalledMethod($this, 'methodName', self::EXPECTED_ARGUMENTS[0]),
            new AssertionFailsWithDescription(sprintf(
                '[%s::methodName()] did not receive 2 argument(s) when called.',
                self::class,
            )),
        ];

        yield 'It fails when it was called with the expected arguments in a different order' => [
            new CalledMethod($this, 'methodName', ...array_reverse(self::EXPECTED_ARGUMENTS)),
            new AssertionFailsWithDescription(sprintf(
                '[%s::methodName()] did not receive the expected value for argument number 1 when called.',
                self::class,
            )),
        ];
    }

    /**
     * @test
     * @dataProvider withArguments
     */
    public function itCanAssertAMethodWasCalledWithArguments(
        CalledMethod $calledMethod,
        Constraint $passesOrFails
    ): void {
        $this->assertThat(function () use ($calledMethod) {
            $calledMethod->withArguments(...self::EXPECTED_ARGUMENTS);
        }, $passesOrFails);
    }
}
