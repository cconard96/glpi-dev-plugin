<?php

use Mexitek\PHPColors\Color;

class PluginDevProfiler extends CommonGLPI
{

    /** @var DateTime */
    private static $session_start;

    /** @var string */
    private static $session_uuid;

    /** @var PluginDevProfilerSection[] */
    private static $prev_sections = [];

    /** @var PluginDevProfilerSection[] */
    private static $current_sections = [];

    public static $disabled = false;

    public static $levels_to_log = self::LEVEL_SLOW | self::LEVEL_CRITICAL;

    /**
     * If true, sections with the same name will have their duration summed up and if they meet a logged level, an aggregate log entry will be created.
     * This does not remove the individual log entries.
     * @var bool
     */
    public static $log_aggregate = true;

    private static $slow_time_ms = 100;

    private static $critical_time_ms = 500;

    private static $initialized = false;

    public const LEVEL_INFO = 1;
    public const LEVEL_SLOW = 2;
    public const LEVEL_CRITICAL = 4;

    public static function getTypeName($nb = 0)
    {
        return _x('tool', 'Profiler', 'dev');
    }

    private static function init()
    {
        register_shutdown_function([__CLASS__, 'dumpSections']);
        self::$initialized = true;
    }

    /**
     * Dump completed log sections to the log file.
     * Dumped sections are removed from the current sections array.
     * @return void
     */
    public static function dumpSections()
    {
        $completed = array_filter(self::$prev_sections, static function (PluginDevProfilerSection $section) {
            return $section->isFinished();
        });
        if (count($completed)) {
            $log_path = self::getProfilerLogPath();
            $log = fopen($log_path, 'ab');
            foreach ($completed as $k => $section) {
                unset(self::$prev_sections[$k]);
                if ($section->getLevel() & self::$levels_to_log === $section->getLevel()) {
                    fwrite($log, json_encode($section->toArray()) . PHP_EOL);
                }
            }
            if (self::$log_aggregate) {
                $aggregated = [];
                foreach ($completed as $section) {
                    $name = $section->getName();
                    if (!isset($aggregated[$name])) {
                        $aggregated[$name] = [
                            'name' => $name,
                            'session_id' => $section->getSessionId(),
                            'category' => $section->getCategory(),
                            'aggregate' => true,
                            'duration' => 0,
                            'count' => 0,
                            'level' => $section->getLevel(),
                        ];
                    }
                    if (!$section->isRecursive()) {
                        // Do not count inner sections (recursive calls except the top-level one) to avoid double-counting
                        $aggregated[$name]['duration'] += $section->getDuration();
                    }
                    $aggregated[$name]['count']++;
                }
                foreach ($aggregated as &$section) {
                    // Recalculate the level based on the aggregated duration
                    $section['level'] = self::getLevelFromDuration($section['duration']);
                    if (($section['level'] & self::$levels_to_log) === $section['level']) {
                        fwrite($log, json_encode($section) . PHP_EOL);
                    }
                }
                unset($section);
            }
            fclose($log);
        }
    }

    public static function start(string $name, string $category = 'core'): void
    {
        if (self::$disabled) {
            return;
        }
        if (!self::$initialized) {
            self::init();
        }
        if (self::$session_start === null) {
            self::$session_start = new \DateTime('now');
            self::$session_uuid = session_id() ?? (string)\Ramsey\Uuid\Uuid::uuid4();
        }
        // Check if a section with the same name is already running
        $match = array_filter(self::$current_sections, static function (PluginDevProfilerSection $section) use ($name) {
            return $section->getName() === $name && !$section->isFinished();
        });
        $is_recursive = count($match) > 0;
        self::$current_sections[] = new PluginDevProfilerSection($category, $name, microtime(true) * 1000, $is_recursive);
    }

    public static function getLevelFromDuration(int $duration): int
    {
        $level = self::LEVEL_INFO;
        if ($duration > self::$critical_time_ms) {
            $level = self::LEVEL_CRITICAL;
        } else if ($duration > self::$slow_time_ms) {
            $level = self::LEVEL_SLOW;
        }
        return $level;
    }

    public static function end(string $name): void
    {
        // get the last section with the given name
        $section = array_filter(self::$current_sections, static function (PluginDevProfilerSection $section) use ($name) {
            return $section->getName() === $name;
        });
        if (count($section)) {
            $k = array_key_last($section);
            $section = array_pop($section);
            $section->end(microtime(true) * 1000);
            unset(self::$current_sections[$k]);
            $duration = $section->getDuration();
            $level = self::getLevelFromDuration($duration);
            $section->setLevel($level);
            self::$prev_sections[] = $section;
        }
    }

    private static function getProfilerLogDir(): string
    {
        return GLPI_ROOT . '/' . \Plugin::getWebDir('dev', false) . '/var/profiler_sessions/';
    }

    private static function getProfilerLogPath(): string
    {
        return self::getProfilerLogDir() . self::$session_start->format('Y-m-d') . '.log';
    }

    private static function getProfilerSessionFiles(): array
    {
        $files = glob(self::getProfilerLogDir() . '*.log');
        $log_files = [];

        foreach ($files as $file) {
            $log_files[] = [
                'name' => explode('.', basename($file))[0],
                'file' => $file
            ];
        }

        return $log_files;
    }

    private static function getSectionsFromFile(string $file): array
    {
        $sections = [];
        $log = fopen($file, 'rb');
        while (($line = fgets($log)) !== false) {
            $sections[] = json_decode($line, true);
        }
        // Group by session ID
        $result = [];
        foreach ($sections as $section) {
            if (!isset($result[$section['session_id']])) {
                $result[$section['session_id']] = [];
            }
            $result[$section['session_id']][] = $section;
        }

        return $result;
    }

    public static function showDebugTab(...$params)
    {
        self::showDashboard(null, session_id());
    }

    public static function showDashboard(string $selected_log = null, string $selected_session = null): void
    {
        $output = '<div id="devprofiler-container">';
        $log_select_label = __('Profiler log', 'dev');
        $output .= "<label id='devprofiler-logselect-label'>{$log_select_label}</label>";
        $output .= '<select class="ms-1" aria-labelledby="devprofiler-logselect-label">';
        $logs = self::getProfilerSessionFiles();
        if (count($logs) > 0) {
            if ($selected_log === null) {
                $selected_log = end($logs)['file'];
            } else {
                $selected_log = self::getProfilerLogDir() . $selected_log . '.log';
            }
            foreach ($logs as $log) {
                if ($log['file'] === $selected_log) {
                    $output .= "<option value='{$log['name']}' selected='selected'>{$log['name']}</option>";
                } else {
                    $output .= "<option value='{$log['name']}'>{$log['name']}</option>";
                }
            }
        }
        $output .= '</select>';

        if ($selected_log === null) {
            echo '<span>There are no profiler logs available.</span>';
            return;
        }
        $sessions = self::getSectionsFromFile($selected_log);

        if ($selected_session === null || !isset($sessions[$selected_session])) {
            $selected_session = array_key_last($sessions);
        }
        $output .= "<select>";
        foreach ($sessions as $session_id => $events) {
            if ($session_id === $selected_session) {
                $output .= "<option value='{$session_id}' selected='selected'>{$session_id}</option>";
            } else {
                $output .= "<option value='{$session_id}'>{$session_id}</option>";
            }
        }
        $output .= "</select>";

        // Add level filters
        $level_info = self::LEVEL_INFO;
        $level_slow = self::LEVEL_SLOW;
        $level_critical = self::LEVEL_CRITICAL;
        $levels = [
            self::LEVEL_INFO => [
                'label' => 'Info',
                'color' => new Color('526dad'),
            ],
            self::LEVEL_SLOW => [
                'label' => 'Slow',
                'color' => new Color('ffaa00'),
            ],
            self::LEVEL_CRITICAL => [
                'label' => 'Critical',
                'color' => new Color('ff0000'),
            ],
        ];
        $output .= <<<HTML
        <div class="form-check form-check-inline form-switch">
             <input class="form-check-input" type="checkbox" id="devprofiler-level-info" data-level="$level_info">
             <label class="form-check-label" for="devprofiler-level-info">{$levels[$level_info]['label']}</label>
        </div>
        <div class="form-check form-check-inline form-switch">
             <input class="form-check-input" type="checkbox" id="devprofiler-level-slow" data-level="$level_slow" checked>
             <label class="form-check-label" for="devprofiler-level-slow">{$levels[$level_slow]['label']}</label>
        </div>
        <div class="form-check form-check-inline form-switch">
             <input class="form-check-input" type="checkbox" id="devprofiler-level-critical" data-level="$level_critical" checked>
             <label class="form-check-label" for="devprofiler-level-critical">{$levels[$level_critical]['label']}</label>
        </div>
HTML;


        $output .= <<<HTML
<table class="table table-striped card-table table-hover">
    <thead>
        <tr>
           <th>Category</th>
           <th>Level</th>
           <th>Name</th>
           <th>Start</th>
           <th>End</th>
           <th>Duration</th>
           <th>Count</th>
       </tr>
    </thead>
    <tbody>
HTML;

        // Store colors to avoid re-calculation during the same request. Predefined some colors.
        $category_colors = [
            'core' => new Color('526dad'),
            'db' => new Color('9252ad'),
            'twig' => new Color('64ad52'),
        ];

        $calc_color = static function ($str) {
            $code = dechex(crc32($str));
            $code = substr($code, 0, 6);
            try {
                return new Color($code);
            } catch (Exception $e) {
                return substr(dechex(mt_rand()), 0, 6);
            }
        };

        foreach ($sessions as $session_id => $sections) {
            if ($session_id !== $selected_session) {
                continue;
            }
            foreach ($sections as $section) {
                $category = $section['category'];
                $level = $section['level'];
                $name = $section['name'];
                $start = $section['start'] ?? '';
                $end = $section['end'] ?? '';
                $duration = (!empty($start) && !empty($end)) ? $end - $start : $section['duration'];

                if ($section['aggregate'] ?? false) {
                    $name = "<strong>{$name} (aggregated)</strong>";
                }
                $count = $section['count'] ?? 1;

                // Calculate color if needed
                if (!isset($category_colors[$category])) {
                    $category_colors[$category] = $calc_color($category);
                }

                $bg_color_cat = '#' . $category_colors[$category]->getHex();
                $fg_color_cat = $category_colors[$category]->isLight() ? 'var(--dark)' : 'var(--light)';
                $bg_color_level = '#' . $levels[$level]['color']->getHex();
                $fg_color_level = $levels[$level]['color']->isLight() ? 'var(--dark)' : 'var(--light)';
                $output .= "<tr>
                <td><span style='padding: 5px; border-radius: 25%; background-color: {$bg_color_cat}; color: {$fg_color_cat}'>{$category}</span></td>
                <td data-level='{$level}'><span style='padding: 5px; border-radius: 25%; background-color: {$bg_color_level}; color: {$fg_color_level}'>{$levels[$level]['label']}</span></td>
                <td>{$name}</td><td>{$start}</td><td>{$end}</td><td>{$duration}ms</td><td>{$count}</td>
            </tr>";
            }
        }
        $output .= '</tbody></table>';
        $output .= '</div>';

        echo $output;
    }
}
