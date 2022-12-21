<?php

use Ramsey\Uuid\Uuid;

class PluginDevProfilerSection
{

    /** @var string */
    private $category;

    /** @var string */
    private $name;

    private $session_id;

    /** @var int */
    private $start;

    /** @var int */
    private $end;

    private $level = PluginDevProfiler::LEVEL_INFO;

    private $is_recursive;

    public function __construct(string $category, string $name, $start, $is_recursive = false)
    {
        $this->category = $category;
        $this->name = $name;
        $this->session_id = session_id() ?? (string)Uuid::uuid4();
        $this->start = (int)$start;
        $this->is_recursive = $is_recursive;
    }

    public function end($time): void
    {
        $this->end = (int)$time;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDuration(): int
    {
        $end = $this->end ?? (int)(microtime(true) * 1000);
        return $end - $this->start;
    }

    public function isFinished(): bool
    {
        return $this->end !== null;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getSessionID(): string
    {
        return $this->session_id;
    }

    public function setSessionID(string $session_id): void
    {
        $this->session_id = $session_id;
    }

    /**
     * Returns true if this section was started in a recursive call.
     * This would not return true for the top-level section.
     * @return bool
     */
    public function isRecursive(): bool
    {
        return $this->is_recursive;
    }

    public function toArray(): array
    {
        return [
            'session_id' => $this->session_id,
            'category' => $this->category,
            'name' => $this->name,
            'start' => $this->start,
            'end' => $this->end,
            'level' => $this->level,
        ];
    }

    public static function fromArray(array $array): self
    {
        $section = new self($array['category'], $array['name'], $array['start']);
        $section->end($array['end']);
        $section->setLevel($array['level']);
        $section->setSessionID($array['session_id']);
        return $section;
    }
}
