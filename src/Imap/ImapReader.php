<?php declare(strict_types = 1);

namespace Contributte\Mail\Imap;

use IMAP\Connection;

final class ImapReader
{

	public const CRITERIA_ALL = 'ALL';
	public const CRITERIA_ANSWERED = 'ANSWERED';
	public const CRITERIA_BCC = 'BCC';
	public const CRITERIA_BEFORE = 'BEFORE';
	public const CRITERIA_BODY = 'BODY';
	public const CRITERIA_CC = 'CC';
	public const CRITERIA_DELETED = 'DELETED';
	public const CRITERIA_FLAGGED = 'FLAGGED';
	public const CRITERIA_FROM = 'FROM';
	public const CRITERIA_KEYWORD = 'KEYWORD';
	public const CRITERIA_NEW = 'NEW';
	public const CRITERIA_OLD = 'OLD';
	public const CRITERIA_ON = 'ON';
	public const CRITERIA_RECENT = 'RECENT';
	public const CRITERIA_SEEN = 'SEEN';
	public const CRITERIA_SINCE = 'SINCE';
	public const CRITERIA_SUBJECT = 'SUBJECT';
	public const CRITERIA_TEXT = 'TEXT';
	public const CRITERIA_TO = 'TO';
	public const CRITERIA_UNANSWERED = 'UNANSWERED';
	public const CRITERIA_UNDELETED = 'UNDELETED';
	public const CRITERIA_UNFLAGGED = 'UNFLAGGED';
	public const CRITERIA_UNKEYWORD = 'UNKEYWORD';
	public const CRITERIA_UNSEEN = 'UNSEEN';

	private Connection $imap;

	private string $mailbox;

	private string $username;

	private string $password;

	public function __construct(string $mailbox, string $username, string $password)
	{
		$this->mailbox = $mailbox;
		$this->username = $username;
		$this->password = $password;

		$this->connect();
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	public function isAlive(): bool
	{
		return @imap_ping($this->imap);
	}

	/** @return ImapMessage[] */
	public function read(string $criteria = 'ALL', int $options = SE_FREE): array
	{
		$mails = imap_search($this->imap, $criteria, $options);
		if ($mails === false) {
			return [];
		}

		$emails = [];
		foreach ($mails as $mailnum) {
			$structure = imap_fetchstructure($this->imap, $mailnum);
			$headers = imap_headerinfo($this->imap, $mailnum);
			if ($structure === false || $headers === false) {
				continue;
			}

			$sections = [];
			if (isset($structure->parts)) {
				foreach ($structure->parts as $partnum => $part) {
					$sections[] = imap_fetchbody($this->imap, $mailnum, (string) $partnum);
				}
			} else {
				$sections[] = imap_body($this->imap, $mailnum);
			}

			$emails[] = new ImapMessage($mailnum, $headers, $structure, $sections);
		}

		return $emails;
	}

	public function folder(string $folder): void
	{
		// @todo
	}

	/**
	 * @param string|array<string> $sequence
	 * @param string|array<string> $flag
	 */
	public function flag(string|array $sequence, string|array $flag): bool
	{
		$sequence = is_array($sequence) ? implode(',', $sequence) : $sequence;
		$flag = is_array($flag) ? implode(' ', $flag) : $flag;

		return imap_setflag_full($this->imap, $sequence, $flag);
	}

	/**
	 * @param string|array<string> $sequence
	 * @param string|array<string> $flag
	 */
	public function unflag(string|array $sequence, string|array $flag): bool
	{
		$sequence = is_array($sequence) ? implode(',', $sequence) : $sequence;
		$flag = is_array($flag) ? implode(' ', $flag) : $flag;

		return imap_clearflag_full($this->imap, $sequence, $flag);
	}

	protected function connect(): void
	{
		$connection = imap_open($this->mailbox, $this->username, $this->password);
		if ($connection === false) {
			throw new \RuntimeException('Failed to connect to IMAP server');
		}

		$this->imap = $connection;
	}

	protected function disconnect(): void
	{
		imap_errors();
		imap_alerts();
		imap_close($this->imap);
	}

	/** @param array<mixed> $arguments */
	public function __call(string $name, array $arguments): mixed
	{
		$args = $arguments;
		array_unshift($args, $this->imap);

		/** @var callable $callback */
		$callback = 'imap_' . $name;

		return call_user_func_array($callback, $args);
	}

}
