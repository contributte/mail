<?php declare(strict_types = 1);

namespace Contributte\Mail\Imap;

use Nette\InvalidArgumentException;
use stdClass;

class ImapMessage
{

	public const FLAG_SEEN = '\\Seen';
	public const FLAG_ANSWERED = '\\Answered';
	public const FLAG_FLAGGED = '\\Flagged';
	public const FLAG_DELETED = '\\Deleted';
	public const FLAG_DRAFT = '\\Draft';

	private int $number;

	private stdClass $headers;

	private stdClass $structure;

	/** @var array<mixed> */
	private array $body = [];

	/** @param array<mixed> $body */
	public function __construct(int $number, stdClass $headers, stdClass $structure, array $body)
	{
		$this->number = $number;
		$this->headers = $this->utf8($headers);
		$this->structure = $structure;
		$this->body = $body;
	}

	public function getNumber(): int
	{
		return $this->number;
	}

	public function getHeaders(): stdClass
	{
		return $this->headers;
	}

	public function getStructure(): stdClass
	{
		return $this->structure;
	}

	/** @return array<mixed> */
	public function getBody(): array
	{
		return $this->body;
	}

	public function getBodySection(int $section): mixed
	{
		if ($section > count($this->body) || !isset($this->body[$section])) {
			throw new InvalidArgumentException('Section #' . $section . ' not found.');
		}

		return $this->body[$section];
	}

	public function getBodySectionText(int $section, ?int $encoding = null): string
	{
		$bodySection = $this->getBodySection($section);
		$text = is_string($bodySection) ? $bodySection : '';
		$encoding ??= (isset($this->structure->parts[$section]) ? $this->structure->parts[$section]->encoding : $this->structure->encoding);

		switch ($encoding) {
			case 0: // 7BIT
				$etext = $text;
				break;
			case 1: // 8BIT
				$encoded = imap_8bit($text);
				$etext = $encoded !== false ? quoted_printable_decode($encoded) : $text;
				break;
			case 2: // BINARY
				$decoded = imap_binary($text);
				$etext = $decoded !== false ? $decoded : $text;
				break;
			case 3: // BASE64
				$decoded = imap_base64($text);
				$etext = $decoded !== false ? $decoded : $text;
				break;
			case 4: // QUOTED-PRINTABLE
				$etext = quoted_printable_decode($text);
				break;
			case 5: // OTHER
			default: // UNKNOWN
				$etext = $text;
		}

		$charset = $this->getBodyCharset();
		$detectedCharset = mb_detect_encoding($etext, mb_list_encodings(), true);
		$charset ??= $detectedCharset !== false ? $detectedCharset : null;

		if ($charset === null) {
			return $etext;
		}

		$converted = iconv($charset, 'UTF-8//TRANSLIT', $etext);

		return $converted !== false ? $converted : $etext;
	}

	public function getBodyCharset(): ?string
	{
		foreach ($this->structure->parameters as $pair) {
			if (isset($pair->attribute, $pair->value) && $pair->attribute === 'charset') {
				return (string) $pair->value;
			}
		}

		return null;
	}

	public function countBodies(): int
	{
		return count($this->body);
	}

	private function utf8(mixed $data): stdClass
	{
		$json = json_encode($data);
		if ($json === false) {
			return new stdClass();
		}

		$array = json_decode($json, true);
		if (!is_array($array)) {
			return new stdClass();
		}

		$array = $this->utf8Array($array);
		$result = json_decode((string) json_encode($array));

		return $result instanceof stdClass ? $result : new stdClass();
	}

	/**
	 * @param array<mixed> $array
	 * @return array<mixed>
	 */
	private function utf8Array(array $array): array
	{
		$result = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$result[$key] = $this->utf8Array($value);
			} elseif (is_string($value)) {
				$result[$key] = imap_utf8($value);
			} else {
				$result[$key] = $value;
			}
		}

		return $result;
	}

}
