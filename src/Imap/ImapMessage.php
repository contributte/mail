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
		$text = $this->getBodySection($section);
		$encoding = $encoding ?? (isset($this->structure->parts[$section]) ? $this->structure->parts[$section]->encoding : $this->structure->encoding);

		switch ($encoding) {
			case 0: // 7BIT
				$etext = $text;
				break;
			case 1: // 8BIT
				$etext = quoted_printable_decode(imap_8bit($text));
				break;
			case 2: // BINARY
				$etext = imap_binary($text);
				break;
			case 3: // BASE64
				$etext = imap_base64($text);
				break;
			case 4: // QUOTED-PRINTABLE
				$etext = quoted_printable_decode($text);
				break;
			case 5: // OTHER
			default: // UNKNOWN
				$etext = $text;
		}

		$charset = $this->getBodyCharset();
		$charset = $charset ?: mb_detect_encoding($etext, mb_detect_order(), true);
		if ($charset === false) {
			return $etext;
		} else {
			return iconv($charset, 'UTF-8//TRANSLIT', $etext);
		}
	}

	public function getBodyCharset(): ?string
	{
		foreach ($this->structure->parameters as $pair) {
			if (isset($pair->attribute) && $pair->attribute === 'charset') {
				return $pair->value;
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
		$array = json_decode(json_encode($data), true);
		array_walk_recursive($array, function ($v, $k) {
			return is_array($v) ? $v : imap_utf8($v);
		});

		return json_decode(json_encode($array));
	}

}
