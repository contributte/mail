<?php declare(strict_types = 1);

namespace Tests\Cases\Exception;

use Contributte\Mail\Exception\Logic\InvalidArgumentException;
use Contributte\Mail\Exception\LogicalException;
use Contributte\Tester\Toolkit;
use LogicException;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test LogicalException extends LogicException
Toolkit::test(function (): void {
	$exception = new LogicalException('Test message');

	Assert::type(LogicException::class, $exception);
	Assert::type(LogicalException::class, $exception);
	Assert::same('Test message', $exception->getMessage());
});

// Test LogicalException with code
Toolkit::test(function (): void {
	$exception = new LogicalException('Test message', 100);

	Assert::same('Test message', $exception->getMessage());
	Assert::same(100, $exception->getCode());
});

// Test LogicalException with previous exception
Toolkit::test(function (): void {
	$previous = new \Exception('Previous exception');
	$exception = new LogicalException('Test message', 0, $previous);

	Assert::same($previous, $exception->getPrevious());
});

// Test InvalidArgumentException extends LogicalException
Toolkit::test(function (): void {
	$exception = new InvalidArgumentException('Invalid argument');

	Assert::type(LogicException::class, $exception);
	Assert::type(LogicalException::class, $exception);
	Assert::type(InvalidArgumentException::class, $exception);
	Assert::same('Invalid argument', $exception->getMessage());
});

// Test InvalidArgumentException with code
Toolkit::test(function (): void {
	$exception = new InvalidArgumentException('Invalid argument', 200);

	Assert::same('Invalid argument', $exception->getMessage());
	Assert::same(200, $exception->getCode());
});

// Test InvalidArgumentException can be caught as LogicalException
Toolkit::test(function (): void {
	$caught = false;

	try {
		throw new InvalidArgumentException('Test');
	} catch (LogicalException $e) {
		$caught = true;
	}

	Assert::true($caught);
});

// Test InvalidArgumentException can be caught as LogicException
Toolkit::test(function (): void {
	$caught = false;

	try {
		throw new InvalidArgumentException('Test');
	} catch (LogicException $e) {
		$caught = true;
	}

	Assert::true($caught);
});
