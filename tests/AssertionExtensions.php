<?php

trait AssertionExtensions
{
    private function assertSameArrayValues(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertSame(array_values($expected), array_values($actual), $message);
    }

    private function assertNotSameArrayValues(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertNotSame(array_values($expected), array_values($actual), $message);
    }
}
