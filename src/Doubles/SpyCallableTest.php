<?php

declare(strict_types=1);

namespace Craftzing\Redemption\Doubles;

use Generator;
use Illuminate\Support\Arr;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use function call_user_func;
use function random_int;

final class SpyCallableTest extends TestCase
{
    /**
     * @test
     */
    public function itCanBeInitialisedWithoutAReturnValue(): void
    {
        $instance = SpyCallable::withoutReturnValue();

        $this->assertNull($instance->returnValueWhenCalled);
    }

    public function returnValues(): Generator
    {
        yield 'Nullable' => [null];
        yield 'String' => ['foo'];
        yield 'Boolean' => [Arr::random([true, false])];
        yield 'Array' => [['foo']];
        yield 'Integer' => [random_int(0, PHP_INT_MAX)];

        yield 'Callable' => [
            function (): void {
                //
            },
        ];

        yield 'Class' => [
            new class {
                //
            },
        ];
    }

    /**
     * @test
     * @dataProvider returnValues
     */
    public function itCanBeInitialisedWithAReturnValue(mixed $returnValue): void
    {
        $instance = SpyCallable::withReturnValue($returnValue);

        $this->assertSame($returnValue, $instance->returnValueWhenCalled);
    }

    public function instances(): Generator
    {
        yield 'Without return value' => [
            SpyCallable::withoutReturnValue(),
            null,
        ];

        yield 'With return value' => [
            SpyCallable::withReturnValue($returnValue = 'foo'),
            $returnValue,
        ];
    }

    /**
     * @test
     * @dataProvider instances
     */
    public function itReturnsTheSpecifiedReturnValueWhenCalled(SpyCallable $instance, mixed $expectedReturnValue): void
    {
        $returnValueWhenCalled = call_user_func($instance);

        $this->assertSame($expectedReturnValue, $returnValueWhenCalled);
    }

    /**
     * @test
     * @dataProvider instances
     */
    public function itSucceedsToAssertItWasCalledWhenItWas(SpyCallable $instance): void
    {
        $instance();

        $instance->assertWasCalled();
    }

    /**
     * @test
     * @dataProvider instances
     */
    public function itFailsToAssertItWasCalledWhenItWasNot(SpyCallable $instance): void
    {
        // Note that we don't invoke the instance here...
        $this->expectExceptionObject(new ExpectationFailedException('SpyCallable was not called.'));

        $instance->assertWasCalled();
    }

    /**
     * @test
     * @dataProvider instances
     */
    public function itSucceedsToAssertItWasCalledWithArgumentsWhenItWas(SpyCallable $instance): void
    {
        $args = ['1', 2, [3], null];

        $instance(...$args);

        $instance->assertWasCalledWithArguments(...$args);
    }

    /**
     * @test
     * @dataProvider instances
     */
    public function itSucceedsToAssertItWasCalledWithoutArgumentsWhenItWas(SpyCallable $instance): void
    {
        $instance();

        $instance->assertWasCalledWithArguments();
    }

    /**
     * @test
     * @dataProvider instances
     */
    public function itFailsToAssertItWasCalledWithArgumentsWhenItWasCalledWithoutArguments(SpyCallable $instance): void
    {
        $args = ['1', 2, [3], null];

        $instance();

        $this->expectExceptionObject(new ExpectationFailedException('SpyCallable was not called with 1 argument(s).'));
        $instance->assertWasCalledWithArguments(...$args);
    }

    /**
     * @test
     * @dataProvider instances
     */
    public function itFailsToAssertItWasCalledWithArgumentsWhenTheArgumentAreDifferent(SpyCallable $instance): void
    {
        $instance('foo', 'bar');

        $this->expectExceptionObject(
            new ExpectationFailedException('SpyCallable was called with a different value for argument #1.'),
        );

        $instance->assertWasCalledWithArguments('bar', 'foo');
    }

    /**
     * @test
     * @dataProvider instances
     */
    public function itFailsToAssertItWasCalledWithArgumentsWhenItWasNot(SpyCallable $instance): void
    {
        $args = ['1', 2, [3], null];

        $instance(...$args);

        $instance->assertWasCalledWithArguments(...$args);
    }

    /**
     * @test
     * @dataProvider instances
     */
    public function itSucceedsToAssertItWasNotCalledWhenItWasNot(SpyCallable $instance): void
    {
        // Note that we don't invoke the instance here...

        $instance->assertWasNotCalled();
    }

    /**
     * @test
     * @dataProvider instances
     */
    public function itFailsToAssertItWasNotCalledWhenItWas(SpyCallable $instance): void
    {
        $instance();

        $this->expectExceptionObject(new ExpectationFailedException('SpyCallable was called unexpectedly.'));

        $instance->assertWasNotCalled();
    }
}
