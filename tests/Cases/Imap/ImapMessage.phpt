<?php declare(strict_types = 1);

namespace Tests\Cases\Imap;

use Contributte\Mail\Imap\ImapMessage;
use Contributte\Tester\Toolkit;
use Nette\InvalidArgumentException;
use stdClass;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../../bootstrap.php';

// Skip if IMAP extension is not loaded
if (!extension_loaded('imap')) {
	Environment::skip('IMAP extension is required for this test');
}

// Helper function to create test headers
function createHeaders(): stdClass
{
	$headers = new stdClass();
	$headers->subject = 'Test Subject';
	$headers->from = 'from@test.com';
	$headers->to = 'to@test.com';
	$headers->date = '2024-01-01 12:00:00';
	return $headers;
}

// Helper function to create test structure
function createStructure(?int $encoding = 0, ?string $charset = 'UTF-8'): stdClass
{
	$structure = new stdClass();
	$structure->encoding = $encoding;
	$structure->type = 0; // TEXT
	$structure->subtype = 'PLAIN';

	$param = new stdClass();
	$param->attribute = 'charset';
	$param->value = $charset;
	$structure->parameters = [$param];

	return $structure;
}

// Test ImapMessage constants
Toolkit::test(function (): void {
	Assert::same('\\Seen', ImapMessage::FLAG_SEEN);
	Assert::same('\\Answered', ImapMessage::FLAG_ANSWERED);
	Assert::same('\\Flagged', ImapMessage::FLAG_FLAGGED);
	Assert::same('\\Deleted', ImapMessage::FLAG_DELETED);
	Assert::same('\\Draft', ImapMessage::FLAG_DRAFT);
});

// Test ImapMessage basic getters
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure();
	$body = ['Test body content'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	Assert::same(1, $message->getNumber());
	Assert::type(stdClass::class, $message->getHeaders());
	Assert::same($structure, $message->getStructure());
	Assert::same($body, $message->getBody());
});

// Test countBodies
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure();
	$body = ['Part 1', 'Part 2', 'Part 3'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	Assert::same(3, $message->countBodies());
});

// Test countBodies with empty body
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure();
	$body = [];

	$message = new ImapMessage(1, $headers, $structure, $body);

	Assert::same(0, $message->countBodies());
});

// Test getBodySection with valid section
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure();
	$body = ['Section 0 content', 'Section 1 content'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	Assert::same('Section 0 content', $message->getBodySection(0));
	Assert::same('Section 1 content', $message->getBodySection(1));
});

// Test getBodySection with invalid section throws exception
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure();
	$body = ['Only one section'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	Assert::exception(function () use ($message): void {
		$message->getBodySection(5);
	}, InvalidArgumentException::class, 'Section #5 not found.');
});

// Test getBodySection with negative index throws exception
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure();
	$body = ['Section content'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	Assert::exception(function () use ($message): void {
		$message->getBodySection(-1);
	}, InvalidArgumentException::class);
});

// Test getBodyCharset returns charset from parameters
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure(0, 'ISO-8859-1');
	$body = ['Test content'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	Assert::same('ISO-8859-1', $message->getBodyCharset());
});

// Test getBodyCharset returns null when no charset parameter
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = new stdClass();
	$structure->encoding = 0;
	$structure->parameters = [];
	$body = ['Test content'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	Assert::null($message->getBodyCharset());
});

// Test getBodyCharset with different attribute name
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = new stdClass();
	$structure->encoding = 0;

	$param = new stdClass();
	$param->attribute = 'boundary'; // Not charset
	$param->value = '----=_Part_0_123456789.1234567890';
	$structure->parameters = [$param];

	$body = ['Test content'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	Assert::null($message->getBodyCharset());
});

// Test getBodySectionText with encoding 0 (7BIT)
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure(0, 'UTF-8');
	$body = ['Plain 7BIT text'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	$result = $message->getBodySectionText(0);
	Assert::same('Plain 7BIT text', $result);
});

// Test getBodySectionText with encoding 3 (BASE64)
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure(3, 'UTF-8');
	$body = [base64_encode('BASE64 encoded text')];

	$message = new ImapMessage(1, $headers, $structure, $body);

	$result = $message->getBodySectionText(0);
	Assert::same('BASE64 encoded text', $result);
});

// Test getBodySectionText with encoding 4 (QUOTED-PRINTABLE)
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure(4, 'UTF-8');
	$body = ['Quoted=20printable=20text'];

	$message = new ImapMessage(1, $headers, $structure, $body);

	$result = $message->getBodySectionText(0);
	Assert::same('Quoted printable text', $result);
});

// Test getBodySectionText with explicit encoding parameter
Toolkit::test(function (): void {
	$headers = createHeaders();
	$structure = createStructure(0, 'UTF-8'); // Structure says 7BIT
	$body = [base64_encode('Override with BASE64')];

	$message = new ImapMessage(1, $headers, $structure, $body);

	// Pass encoding=3 (BASE64) explicitly
	$result = $message->getBodySectionText(0, 3);
	Assert::same('Override with BASE64', $result);
});

// Test getBodySectionText with structure parts
Toolkit::test(function (): void {
	$headers = createHeaders();

	$structure = new stdClass();
	$structure->encoding = 0;

	$param = new stdClass();
	$param->attribute = 'charset';
	$param->value = 'UTF-8';
	$structure->parameters = [$param];

	// Create parts with different encodings
	$part0 = new stdClass();
	$part0->encoding = 3; // BASE64

	$structure->parts = [$part0];

	$body = [base64_encode('Part 0 BASE64')];

	$message = new ImapMessage(1, $headers, $structure, $body);

	$result = $message->getBodySectionText(0);
	Assert::same('Part 0 BASE64', $result);
});
