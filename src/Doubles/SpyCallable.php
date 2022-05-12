<?php

declare(strict_types=1);

namespace Craftzing\Redemption\Doubles;

use PHPUnit\Framework\Assert;

final class SpyCallable
{
    private mixed $argumentsWhenCalled = null;

    private function __construct(
        public readonly mixed $returnValueWhenCalled = null,
    ) {}

    public static function withoutReturnValue(): self
    {
        return new self();
    }

    public static function withReturnValue(mixed $returnValueWhenCalled): self
    {
        return new self($returnValueWhenCalled);
    }

    public function __invoke(...$arguments): mixed
    {
        $this->argumentsWhenCalled = $arguments;

        return $this->returnValueWhenCalled;
    }

    public function assertWasCalled(): void
    {
        Assert::assertNotNull($this->argumentsWhenCalled, 'SpyCallable was not called.');
    }

    public function assertWasCalledWithArguments(...$expectedArguments): void
    {
        if ($expectedArguments === []) {
            Assert::assertSame($expectedArguments, $this->argumentsWhenCalled);
        }

        foreach ($expectedArguments as $key => $expectedArgument) {
            $nthArgument = $key + 1;
            Assert::assertArrayHasKey(
                $key,
                $this->argumentsWhenCalled,
                "SpyCallable was not called with $nthArgument argument(s).",
            );
            Assert::assertSame(
                $this->argumentsWhenCalled[$key],
                $expectedArgument,
                "SpyCallable was called with a different value for argument #$nthArgument.",
            );
        }
    }

    public function assertWasNotCalled(): void
    {
        Assert::assertNull($this->argumentsWhenCalled, 'SpyCallable was called unexpectedly.');
    }
}
