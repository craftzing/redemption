<?php

declare(strict_types=1);

namespace Craftzing\Redemption\Spies;

use Craftzing\Redemption\Spies\Inspections\CalledMethod;
use PHPUnit\Framework\Assert;

use function get_class;
use function sprintf;

final class Spy
{
    private object $subject;

    /**
     * @var array<CalledMethod>
     */
    private array $calledMethods = [];

    public function __construct(object $subject)
    {
        $this->subject = $subject;
    }

    public function capture(string $method, ...$arguments): void
    {
        $this->calledMethods[$method] = new CalledMethod($this->subject, $method, $arguments);
    }

    public function hasCaptured(string $method): bool
    {
        return isset($this->calledMethods[$method]);
    }

    public function assertHasCaptured(string $method, string $message): CalledMethod
    {
        Assert::assertTrue(
            $this->hasCaptured($method),
            sprintf($message, get_class($this->subject)),
        );

        return $this->calledMethods[$method];
    }
}
