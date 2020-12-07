<?php

declare(strict_types=1);

namespace Craftzing\Redemption\Spies;

use Craftzing\Redemption\Spies\Inspections\CalledMethod;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;

use function count;
use function sprintf;

final class SpyCallable
{
    use SpiesOnMethodCalls;

    /**
     * @var mixed|null
     */
    private $returnValueWhenCalled = null;
    private bool $wasCalled = false;
    private array $argumentsWhenCalled = [];

    public function __construct()
    {
        $this->spy = new Spy($this);
    }

    /**
     * @param mixed $returnValueWhenCalled
     * @return self
     */
    public static function withReturnValue($returnValueWhenCalled): self
    {
        $instance = new self();
        $instance->returnValueWhenCalled = $returnValueWhenCalled;

        return $instance;
    }

    /**
     * @param mixed ...$arguments
     * @return mixed|null
     */
    public function __invoke(...$arguments)
    {
        $this->spy->capture(__FUNCTION__, ...$arguments);

        return $this->returnValueWhenCalled;
    }

    public function assertWasCalled(): CalledMethod
    {
        return $this->spy->assertHasCaptured('__invoke', '[%s] was not called.');
    }

    public function assertWasCalledWithoutArguments(): void
    {
        $this->assertWasCalled();
        $this->spy->assertHasCapturedWithoutArguments(
            '__invoke',
            '[%s] should not have been called with any arguments, but it was called with %d.',
        );
        Assert::assertEmpty($this->argumentsWhenCalled, sprintf(
            '[%s] should not have been called with any arguments, but it was called with %d.',
            self::class,
            count($this->argumentsWhenCalled),
        ));
    }

    public function assertWasCalledWithArguments(...$expectedArguments): void
    {
        if (empty($expectedArguments)) {
            throw new InvalidArgumentException(
                'Please make sure to provide at least one argument which the callable should have been called with.',
            );
        }

        $this->assertWasCalled();

        foreach ($expectedArguments as $key => $expectedArgument) {
            $argumentNumber = $key + 1;

            Assert::assertArrayHasKey($key, $this->argumentsWhenCalled, sprintf(
                "[%s] did not receive {$argumentNumber} argument(s) when called.",
                self::class,
            ));
            Assert::assertSame($this->argumentsWhenCalled[$key], $expectedArgument, sprintf(
                "[%s] did not receive the expected value for argument number {$argumentNumber} when called.",
                self::class,
            ));
        }
    }
}
