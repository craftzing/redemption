<?php

declare(strict_types=1);

namespace Craftzing\Redemption\Spies\Inspections;

use InvalidArgumentException;
use PHPUnit\Framework\Assert;

use function count;
use function get_class;
use function sprintf;

final class CalledMethod
{
    private object $subject;
    private string $method;
    private array $arguments;

    public function __construct(object $subject, string $method, ...$arguments)
    {
        $this->subject = $subject;
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function withoutArguments(): void
    {
        Assert::assertEmpty($this->arguments, sprintf(
            '[%s::%s()] should not have been called with any arguments, but it was called with %d.',
            get_class($this->subject),
            $this->method,
            count($this->arguments),
        ));
    }

    public function withArguments(...$arguments): void
    {
        if (empty($arguments)) {
            throw new InvalidArgumentException(
                'Please make sure to provide at least one argument which the method should have been called with.',
            );
        }

        $subjectClass = get_class($this->subject);

        foreach ($arguments as $key => $expectedArgument) {
            $argumentNumber = $key + 1;

            Assert::assertArrayHasKey($key, $this->arguments, sprintf(
                "[%s::%s()] did not receive {$argumentNumber} argument(s) when called.",
                $subjectClass,
                $this->method,
            ));
            Assert::assertSame($this->arguments[$key], $expectedArgument, sprintf(
                "[%s::%s()] did not receive the expected value for argument number {$argumentNumber} when called.",
                $subjectClass,
                $this->method,
            ));
        }
    }
}
