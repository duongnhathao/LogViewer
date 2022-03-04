<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Custom_log extends CI_Custom_Log {

    protected string $_cus_log_path;
    protected string $_end_line = '#';

    /**
     * @return string
     */

    /**
     * @param string $end_line
     */
    public function setEndLine(string $end_line): void
    {
        $this->_end_line = $end_line;
    }

    /**
     * @return string
     */
    public function getEndLine(): string
    {
        return $this->_end_line;
    }

    public function getFileExt(): string
    {
        return $this->_file_ext;
    }

    /**
     * @return string
     */
    public function getDateFmt()
    {
        return $this->_date_fmt;
    }

    /**
     * @return string
     */
    public function getLogPath()
    {
        return $this->_log_path;
    }

    /**
     * @return int
     */
    public function getFilePermissions(): int
    {
        return $this->_file_permissions;
    }

    /**
     * @return array
     */
    public function getLevels(): array
    {
        return $this->_levels;
    }

    /**
     * @return int
     */
    public function getThreshold(): int
    {
        return $this->_threshold;
    }

    /**
     * @return array
     */
    public function getThresholdArray(): ?array
    {
        return $this->_threshold_array;
    }

    /**
     * @param mixed $cus_log_path
     */
    public function setCusLogPath($cus_log_path): void
    {
        $this->_cus_log_path = $cus_log_path;
    }


    // --------------------------------------------------------------------

    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @param string $level The error level: 'error', 'debug' or 'info'
     * @param array $msg The error message
     * @return    bool
     */
    public function write_log_list($level, $msg)
    {
        if ($this->_enabled === FALSE)
        {
            return FALSE;
        }

        $level = strtoupper($level);

        if ( ! isset($this->_levels[$level]))
        {
            return FALSE;
        }

        $filepath = ! empty($this->_cus_log_path) ? $this->_cus_log_path : $this->_log_path . 'log-' . date('Y-m-d') . '.' . $this->_file_ext;
        $message = '';

        if ( ! file_exists($filepath))
        {
            $newfile = TRUE;
            // Only add protection to php files
            if ($this->_file_ext === 'php')
            {
                $message .= "<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>\n\n";
            }
        }

        if ( ! $fp = @fopen($filepath, 'ab'))
        {
            return FALSE;
        }

        flock($fp, LOCK_EX);

        // Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
        if (strpos($this->_date_fmt, 'u') !== FALSE)
        {
            $microtime_full = microtime(TRUE);
            $microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
            $date = new DateTime(date('Y-m-d H:i:s.' . $microtime_short, $microtime_full));
            $date = $date->format($this->_date_fmt);
        } else
        {
            $date = date($this->_date_fmt);
        }

        $msg_to_string = implode($this->_end_line, $msg);

        $message .= $this->_format_line($level, $date, $msg['title'] ?? $msg_to_string, ! $msg['title'] ? '' : $msg_to_string);

        for ($written = 0, $length = self::strlen($message); $written < $length; $written += $result)
        {
            if (($result = fwrite($fp, self::substr($message, $written))) === FALSE)
            {
                break;
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        if (isset($newfile) && $newfile === TRUE)
        {
            chmod($filepath, $this->_file_permissions);
        }

        return is_int($result);
    }

}

