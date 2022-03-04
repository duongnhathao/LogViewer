<?php

namespace duongnhathao\LogViewer\Utilities;


use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use duongnhathao\LogViewer\Contracts\Utilities\LogStyler as LogInterface;

/**
 *Class     LogStyler
 * @author duongnhathao <duongnhathao001@gmail.com>
 */
class LogStyler implements LogInterface {
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The config repository instance.
     *
     */
    protected $config;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Create a new instance.
     *
     */
    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get config.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    private function get($key, $default = NULL)
    {
        return $this->config->get("log-viewer.$key", $default);
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Make level icon.
     *
     * @param string $level
     * @param string|null $default
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function icon($level, $default = NULL)
    {
        return new HtmlString(
            '<i class="' . $this->get("icons.$level", $default) . '"></i>'
        );
    }

    /**
     * Get level color.
     *
     * @param string $level
     * @param string|null $default
     *
     * @return string
     */
    public function color($level, $default = NULL)
    {
        return $this->get("colors.levels.$level", $default);
    }

    /**
     * Get strings to highlight.
     *
     * @param array $default
     *
     * @return array
     */
    public function toHighlight(array $default = [])
    {
        return $this->get('highlight', $default);
    }
}