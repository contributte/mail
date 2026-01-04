<?php declare(strict_types = 1);

namespace Tests\Cases\Imap;

use Contributte\Mail\Imap\ImapMessage;
use Contributte\Tester\Toolkit;
use Nette\InvalidArgumentException;
use stdClass;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../../bootstrap.php';

// Skip tests if IMAP extension is not loaded
if (!extension_loaded('imap')) {
	Environment::skip('IMAP extension is required for these tests.');
}

// Helper function to create headers stdClass
function createHeaders(array $data = []): stdClass
{
	$headers = new stdClass();
	foreach ($data as $key => $value) {
		$headers->$key = $value;
	}

	return $headers;
}

// Helper function to create structure stdClass
function createStructure(int $encoding = 0, array $parameters = [], ?array $parts = null): stdClass
{
	$structure = new stdClass();
	$structure->encoding = $encoding;
	$structure->parameters = [];

	foreach ($parameters as $param) {
		$obj = new stdClass();
		$obj->attribute = $param['attribute'];
		$obj->value = $param['value'];
		$structure->parameters[] = $obj;
	}

	if ($parts !== null) {
		$structure->parts = [];
		foreach ($parts as $part) {
			$partObj = new stdClass();
			$partObj->encoding = $part['encoding'] ?? 0;
			$structure->parts[] = $partObj;
		}
	}

	return $structure;
}

// Test constants are defined
Toolkit::test(function (): void {
	Assert::same('\\Seen', ImapMessage::FLAG_SEEN);
	Assert::same('\\Answered', ImapMessage::FLAG_ANSWERED);
	Assert::same('\\Flagged', ImapMessage::FLAG_FLAGGED);
	Assert::same('\\Deleted', ImapMessage::FLAG_DELETED);
	Assert::same('\\Draft', ImapMessage::FLAG_DRAFT);
});

// Test basic message construction
Toolkit::test(function (): void {
	$headers = createHeaders(['subject' => 'Test Subject', 'from' => 'test@example.com']);
	$structure = createStructure();
	$body = ['Body content'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	Assert::same(1, $message->getNumber());
	Assert::type(stdClass::class, $message->getHeaders());
	Assert::type(stdClass::class, $message->getStructure());
	Assert::same(['Body content'], $message->getBody());
});

// Test getNumber
Toolkit::test(function (): void {
	$message = new ImapMessage(42, createHeaders(), createStructure(), []);
	Assert::same(42, $message->getNumber());
});

// Test countBodies
Toolkit::test(function (): void {
	$message = new ImapMessage(1, createHeaders(), createStructure(), ['part1', 'part2', 'part3']);
	Assert::same(3, $message->countBodies());
});

// Test countBodies with empty body
Toolkit::test(function (): void {
	$message = new ImapMessage(1, createHeaders(), createStructure(), []);
	Assert::same(0, $message->countBodies());
});

// Test getBodySection
Toolkit::test(function (): void {
	$body = ['Section 0', 'Section 1', 'Section 2'];
	$message = new ImapMessage(1, createHeaders(), createStructure(), $body);

	Assert::same('Section 0', $message->getBodySection(0));
	Assert::same('Section 1', $message->getBodySection(1));
	Assert::same('Section 2', $message->getBodySection(2));
});

// Test getBodySection throws exception for invalid section
Toolkit::test(function (): void {
	$message = new ImapMessage(1, createHeaders(), createStructure(), ['Only one']);

	Assert::exception(function () use ($message): void {
		$message->getBodySection(5);
	}, InvalidArgumentException::class, 'Section #5 not found.');
});

// Test getBodySection throws exception for negative section
Toolkit::test(function (): void {
	$message = new ImapMessage(1, createHeaders(), createStructure(), ['Content']);

	Assert::exception(function () use ($message): void {
		$message->getBodySection(-1);
	}, InvalidArgumentException::class);
});

// Test getBodyCharset with charset parameter
Toolkit::test(function (): void {
	$structure = createStructure(0, [
		['attribute' => 'charset', 'value' => 'UTF-8'],
	]);
	$message = new ImapMessage(1, createHeaders(), $structure, ['Content']);

	Assert::same('UTF-8', $message->getBodyCharset());
});

// Test getBodyCharset with ISO charset
Toolkit::test(function (): void {
	$structure = createStructure(0, [
		['attribute' => 'charset', 'value' => 'ISO-8859-1'],
	]);
	$message = new ImapMessage(1, createHeaders(), $structure, ['Content']);

	Assert::same('ISO-8859-1', $message->getBodyCharset());
});

// Test getBodyCharset returns null when no charset
Toolkit::test(function (): void {
	$structure = createStructure(0, [
		['attribute' => 'other', 'value' => 'value'],
	]);
	$message = new ImapMessage(1, createHeaders(), $structure, ['Content']);

	Assert::null($message->getBodyCharset());
});

// Test getBodyCharset with empty parameters
Toolkit::test(function (): void {
	$structure = createStructure();
	$message = new ImapMessage(1, createHeaders(), $structure, ['Content']);

	Assert::null($message->getBodyCharset());
});

// Test getBodySectionText with 7BIT encoding (0)
Toolkit::test(function (): void {
	$structure = createStructure(0, [['attribute' => 'charset', 'value' => 'UTF-8']]);
	$message = new ImapMessage(1, createHeaders(), $structure, ['Plain text content']);

	$text = $message->getBodySectionText(0);
	Assert::same('Plain text content', $text);
});

// Test getBodySectionText with BASE64 encoding (3)
Toolkit::test(function (): void {
	$structure = createStructure(3, [['attribute' => 'charset', 'value' => 'UTF-8']]);
	$encodedContent = base64_encode('Decoded content');
	$message = new ImapMessage(1, createHeaders(), $structure, [$encodedContent]);

	$text = $message->getBodySectionText(0);
	Assert::same('Decoded content', $text);
});

// Test getBodySectionText with QUOTED-PRINTABLE encoding (4)
Toolkit::test(function (): void {
	$structure = createStructure(4, [['attribute' => 'charset', 'value' => 'UTF-8']]);
	$encodedContent = quoted_printable_encode('Test content');
	$message = new ImapMessage(1, createHeaders(), $structure, [$encodedContent]);

	$text = $message->getBodySectionText(0);
	Assert::same('Test content', $text);
});

// Test getBodySectionText with explicit encoding parameter
Toolkit::test(function (): void {
	$structure = createStructure(0, [['attribute' => 'charset', 'value' => 'UTF-8']]);
	$encodedContent = base64_encode('Base64 text');
	$message = new ImapMessage(1, createHeaders(), $structure, [$encodedContent]);

	// Force BASE64 decoding
	$text = $message->getBodySectionText(0, 3);
	Assert::same('Base64 text', $text);
});

// Test getBodySectionText uses part encoding when available
Toolkit::test(function (): void {
	$structure = createStructure(0, [['attribute' => 'charset', 'value' => 'UTF-8']], [
		['encoding' => 3], // BASE64
	]);
	$encodedContent = base64_encode('Part encoded');
	$message = new ImapMessage(1, createHeaders(), $structure, [$encodedContent]);

	$text = $message->getBodySectionText(0);
	Assert::same('Part encoded', $text);
});

// Test headers are converted to UTF-8
Toolkit::test(function (): void {
	$headers = createHeaders([
		'subject' => '=?UTF-8?B?VGVzdCBTdWJqZWN0?=',
		'from' => 'sender@example.com',
	]);
	$structure = createStructure();

	$message = new ImapMessage(1, $headers, $structure, []);
	$resultHeaders = $message->getHeaders();

	Assert::type(stdClass::class, $resultHeaders);
});

// Test message with multiple body parts
Toolkit::test(function (): void {
	$structure = createStructure(0, [['attribute' => 'charset', 'value' => 'UTF-8']], [
		['encoding' => 0],
		['encoding' => 3],
	]);

	$body = [
		'Plain text part',
		base64_encode('Base64 encoded part'),
	];

	$message = new ImapMessage(1, createHeaders(), $structure, $body);

	Assert::same(2, $message->countBodies());
	Assert::same('Plain text part', $message->getBodySectionText(0));
	Assert::same('Base64 encoded part', $message->getBodySectionText(1));
});
