<?php

declare(strict_types=1);

namespace Craftzing\Redemption\Spies;

use Craftzing\Redemption\Constraints\AssertionFailsWithDescription;
use Craftzing\Redemption\Constraints\AssertionPasses;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;

use function array_reverse;
use function sprintf;

final class SpyCallableTest extends TestCase
{
    private const NOT_CALLED =
        '[Craftzing\\Redemption\\Spies\\SpyCallable] was not called.';
    private const SHOULD_NOT_HAVE_BEEN_CALLED_WITH_ARGUMENTS =
        '[Craftzing\\Redemption\\Spies\\SpyCallable] should not have been called with any arguments, ' .
        'but it was called with %d.';
    private const ARGUMENT_MISSING =
        '[Craftzing\\Redemption\\Spies\\SpyCallable] did not receive %d argument(s) when called.';
    private const UNEXPECTED_ARGUMENT_VALUE =
        '[Craftzing\\Redemption\\Spies\\SpyCallable] did not receive the expected value' .
        ' for argument number %d when called.';

    /**
     * @test
     */
    public function itCanBeInitialisedWithoutAReturnValue(): void
    {
        $callable = new SpyCallable();

        $this->assertIsCallable($callable);
        $this->assertNull($callable());
    }

    public function anyValue(): Generator
    {
        yield 'String' => ['Some value'];
        yield 'Boolean' => [true];
        yield 'Integer' => [369];
        yield 'Array' => [[]];
        yield 'Null' => [null];
        yield 'Object' => [new DateTimeImmutable()];
    }

    /**
     * @test
     * @dataProvider anyValue
     */
    public function itCanBeInitialisedWithAReturnValue($anyValue): void
    {
        $callable = SpyCallable::withReturnValue($anyValue);

        $this->assertInstanceOf(SpyCallable::class, $callable);
        $this->assertSame($anyValue, $callable());
    }

    public function assertWasCalled(): Generator
    {
        yield 'It fails when it was not called yet' => [
            new AssertionFailsWithDescription(self::NOT_CALLED),
        ];

        yield 'It passes when it was called without any arguments' => [
            new AssertionPasses(),
            fn (SpyCallable $callable) => $callable(),
        ];

        yield 'It passes when it was called with any number of arguments' => [
            new AssertionPasses(),
            fn (SpyCallable $callable) => $callable('some', 'arguments'),
        ];
    }

    /**
     * @test
     * @dataProvider assertWasCalled
     */
    public function itCanAssertThatItWasCalled(Constraint $passesOrFails, callable $actOnCallable = null): void
    {
        $callable = new SpyCallable();

        if ($actOnCallable) {
            $actOnCallable($callable);
        }

        $this->assertThat(function () use ($callable): void {
            $callable->assertWasCalled();
        }, $passesOrFails);
    }

    public function assertWasCalledWithoutArguments(): Generator
    {
        yield 'It fails when it was not called yet' => [
            new AssertionFailsWithDescription(self::NOT_CALLED),
            function (SpyCallable $callable) {},
        ];

        yield 'It passes when it was called without any arguments' => [
            new AssertionPasses(),
            fn (SpyCallable $callable) => $callable(),
        ];

        yield 'It fails when it was called with any number of arguments' => [
            new AssertionFailsWithDescription(sprintf(self::SHOULD_NOT_HAVE_BEEN_CALLED_WITH_ARGUMENTS, 2)),
            fn (SpyCallable $callable) => $callable('some', 'arguments'),
        ];
    }

    /**
     * @test
     * @dataProvider assertWasCalledWithoutArguments
     */
    public function itCanAssertThatItWasCalledWithoutArguments(
        Constraint $passesOrFails,
        callable $actOnCallable = null
    ): void {
        $callable = new SpyCallable();

        if ($actOnCallable) {
            $actOnCallable($callable);
        }

        $this->assertThat(function () use ($callable): void {
            $callable->assertWasCalledWithoutArguments();
        }, $passesOrFails);
    }

    public function assertWasCalledWithArguments(): Generator
    {
        yield 'It fails when it was not called yet' => [
            new AssertionFailsWithDescription(self::NOT_CALLED),
            function (SpyCallable $callable) {},
        ];

        yield 'It fails when it was called with a missing arguments' => [
            new AssertionFailsWithDescription(sprintf(self::ARGUMENT_MISSING, 1)),
            fn (SpyCallable $callable) => $callable(),
        ];

        yield 'It fails when it was called with a different argument value' => [
            new AssertionFailsWithDescription(sprintf(self::ARGUMENT_MISSING, 1)),
            fn (SpyCallable $callable) => $callable(),
        ];

        yield 'It fails when it was called with the expected arguments in a different order' => [
            new AssertionFailsWithDescription(sprintf(self::UNEXPECTED_ARGUMENT_VALUE, 1)),
            fn (SpyCallable $callable, array $arguments) => $callable(...array_reverse($arguments)),
        ];

        yield 'It passes when it was called with the expected arguments' => [
            new AssertionPasses(),
            fn (SpyCallable $callable, array $arguments) => $callable(...$arguments),
        ];

        yield 'It passes when it was called with more than the expected arguments' => [
            new AssertionPasses(),
            fn (SpyCallable $callable, array $arguments) => $callable(...[...$arguments, 'additional argument']),
        ];
    }

    /**
     * @test
     * @dataProvider assertWasCalledWithArguments
     */
    public function itCanAssertThatItWasCalledWithArguments(
        Constraint $passesOrFails,
        callable $actOnCallable = null
    ): void {
        $callable = new SpyCallable();
        $argumentsToCallWith = ['some', 'arguments'];

        if ($actOnCallable) {
            $actOnCallable($callable, $argumentsToCallWith);
        }

        $this->assertThat(function () use ($callable, $argumentsToCallWith): void {
            $callable->assertWasCalledWithArguments(...$argumentsToCallWith);
        }, $passesOrFails);
    }
}
