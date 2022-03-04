<?php
/**
 * Author: Seun Matt - Custom By Duong Nhat Hao
 * Date: 1-Mar-22
 * Time: 12:30 AM
 */


use duongnhathao\LogViewer\Utilities\LogStyler;

defined('BASEPATH') or exit('No direct script access allowed');
defined('APPPATH') or exit('Not a Code Igniter Environment');


class CILogViewer {

    private $CI;

    private static $levelsIcon = [
        'INFO'  => 'fa-solid fa-circle-info',
        'ERROR' => 'fa-solid fa-bug',
        'DEBUG' => 'fa-solid fa-bug-slash',
        'ALL'   => 'fa-solid fa-earth-africa',
    ];

    private static $levelClasses = [
        'INFO'  => 'info',
        'ERROR' => 'danger',
        'DEBUG' => 'warning',
        'ALL'   => 'muted',
    ];


    const LOG_LINE_START_PATTERN = "/((INFO)|(ERROR)|(DEBUG)|(ALL))[\s\-\d:\.\/]+(-->)/";
    const LOG_TITLE_PATTERN  = "/(.*)(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})([\s]-->[\s])(.*)([\s]-->[\s].*)/";
    const LOG_CONTENT_PATTERN  = "/(.)*(-->[\s])(.*)/";
    const LOG_DATE_PATTERN = ["/^((ERROR)|(INFO)|(DEBUG)|(ALL))\s\-\s/", "/\s(-->)/"];
    const LOG_LEVEL_PATTERN = "/^((ERROR)|(INFO)|(DEBUG)|(ALL))/";

    //this is the path (folder) on the system where the log files are stored
    private $logFolderPath;

    //this is the pattern to pick all log files in the $logFilePath
    private $logFilePattern;

    //this is the pattern to pick all log files not simple file logs
    private $logFilePatternNotSimple;

    //this is a combination of the LOG_FOLDER_PATH and LOG_FILE_PATTERN
    private $fullLogFilePath = "";
    private $fullLogFilePathNotSimple = "";

    //these are the config keys expected in the config.php
    const LOG_FILE_PATTERN_CONFIG_KEY = "clv_log_file_pattern";
    const LOG_FILE_PATTERN_NOT_SIMPLE_CONFIG_KEY = "clv_log_file_pattern_not_simple";
    const LOG_FOLDER_PATH_CONFIG_KEY = "clv_log_folder_path";


    /**
     * Here we define the paths for the view file
     * that's used by the library to present logs on the UI
     */
    private $LOG_VIEW_FILE_FOLDER = "";
    private $LOG_VIEW_FILE_NAME = "logs.php";
    private $LOG_VIEW_FILE_PATH = "";

    //this is the name of the view file passed to CI load->view()
    const CI_LOG_VIEW_FILE_PATH = "cilogviewer/logs";
    const CI_LOG_VIEW_FILE_PATH_LIST_FILE = "cilogviewer/list_file";

    const MAX_LOG_SIZE = 52428800; //50MB
    const MAX_STRING_LENGTH = 300; //300 chars

    /**
     * These are the constants representing the
     * various API commands there are
     */
    private const API_QUERY_PARAM = "api";
    private const API_FILE_QUERY_PARAM = "f";
    private const API_LOG_STYLE_QUERY_PARAM = "sline";
    private const API_CMD_LIST = "list";
    private const API_CMD_VIEW = "view";
    private const API_CMD_DELETE = "delete";
    private const FILE_TEMPLATE_LIST = ['footer.php', 'header.php', 'nav.php'];
    private const FILE_LIST = ['list_file.php', 'base_file.php',];
    protected string $url = '';
    protected string $another_log = '';
    protected string $delimiter = "\n\n";
    protected string $_end_line = '/#/';


    public function __construct()
    {
        $this->init();
    }

    public function get_url()
    {
        return $this->url;
    }

    public function set_url($url)
    {
        return $this->url = $url;
    }

    public function set_delimiter($delimiter)
    {
        return $this->delimiter = $delimiter;
    }

    public function get_delimiter()
    {
        return $this->delimiter;
    }


    /**
     * Bootstrap the library
     * sets the configuration variables
     * @throws \Exception
     */
    private function init()
    {

        if ( ! function_exists("get_instance"))
        {
            throw new \Exception("This library works in a Code Igniter Project/Environment");
        }

        //initiate Code Igniter Instance
        $this->CI = &get_instance();

        //configure the log folder path and the file pattern for all the logs in the folder
        $this->logFolderPath = ! is_null($this->CI->config->item(self::LOG_FOLDER_PATH_CONFIG_KEY)) ? rtrim($this->CI->config->item(self::LOG_FOLDER_PATH_CONFIG_KEY), "/") : rtrim(APPPATH, "/") . "/logs";
        $this->logFilePattern = ! is_null($this->CI->config->item(self::LOG_FILE_PATTERN_CONFIG_KEY)) ? $this->CI->config->item(self::LOG_FILE_PATTERN_CONFIG_KEY) : "log-*.php";
        $this->logFilePatternNotSimple = $this->another_log ?: "query-*.log";

        //concatenate to form Full Log Path
        $this->fullLogFilePath = $this->logFolderPath . "/" . $this->logFilePattern;
        $this->fullLogFilePathNotSimple = $this->logFolderPath . "/" . $this->logFilePatternNotSimple;

        //create the view file so that CI can find it
        //use VIEWPATH constant so the CI can find views location and this constant will be defined in index.php file.
        $this->LOG_VIEW_FILE_FOLDER = VIEWPATH . "cilogviewer";
        $this->LOG_VIEW_FILE_PATH = rtrim($this->LOG_VIEW_FILE_FOLDER) . "/" . $this->LOG_VIEW_FILE_NAME;
        if ( ! file_exists($this->LOG_VIEW_FILE_PATH))
        {
            if ( ! is_dir($this->LOG_VIEW_FILE_FOLDER))
                mkdir($this->LOG_VIEW_FILE_FOLDER);

            file_put_contents($this->LOG_VIEW_FILE_PATH, file_get_contents($this->LOG_VIEW_FILE_NAME, FILE_USE_INCLUDE_PATH));
        }
        $template_folder_copy = $this->LOG_VIEW_FILE_FOLDER . '/template/';
        foreach (self::FILE_TEMPLATE_LIST as $filename)
        {
            if ( ! is_dir($template_folder_copy))
                mkdir($template_folder_copy);
            if ( ! file_exists($template_folder_copy . $filename))
            {
                file_put_contents($template_folder_copy . $filename, file_get_contents($filename, FILE_USE_INCLUDE_PATH));
            }
        }
        foreach (self::FILE_LIST as $filename)
        {
            if ( ! file_exists($this->LOG_VIEW_FILE_FOLDER . "/" . $filename))
            {
                file_put_contents($this->LOG_VIEW_FILE_FOLDER . "/" . $filename, file_get_contents($filename, FILE_USE_INCLUDE_PATH));
            }
        }

    }

    /*
     * This function will return the processed HTML page
     * and return it's content that can then be echoed
     *
     * @param $fileName optional base64_encoded filename of the log file to process.
     * @returns the parse view file content as a string that can be echoed
     * */
    public function showLogs($search = NULL, $not_include = NULL, $type_logs = TRUE)
    {
        $config = new LogStyler();

        if ( ! is_null($this->CI->input->get("del")))
        {
            $this->deleteFiles(base64_decode($this->CI->input->get("del")));
            redirect($this->CI->uri->uri_string());
            return;
        }

        //process download of log file command
        //if the supplied file exists, then perform download
        //otherwise, just ignore which will resolve to page reloading
        $dlFile = $this->CI->input->get("dl");
        if ( ! is_null($dlFile) && file_exists($this->logFolderPath . "/" . basename(base64_decode($dlFile))))
        {
            $file = $this->logFolderPath . "/" . basename(base64_decode($dlFile));
            $this->downloadFile($file);
        }

        if ( ! is_null($this->CI->input->get(self::API_QUERY_PARAM)))
        {
            return $this->processAPIRequests($this->CI->input->get(self::API_QUERY_PARAM));
        }

        //it will either get the value of f or return null
        $fileName = $this->CI->input->get("f");

        //get the log files from the log directory
        $files = $this->getFiles(TRUE, $type_logs);

        //let's determine what the current log file is
        if ( ! is_null($fileName))
        {
            $currentFile = $this->logFolderPath . "/" . basename(base64_decode($fileName));
        } else if (is_null($fileName) && ! empty($files))
        {
            $currentFile = $this->logFolderPath . "/" . $files[0];
        } else
        {
            $currentFile = NULL;
        }

        //if the resolved current file is too big
        //just trigger a download of the file
        //otherwise process its content as log

        if ( ! is_null($currentFile) && file_exists($currentFile))
        {

            $fileSize = filesize($currentFile);

            if (is_int($fileSize) && $fileSize > self::MAX_LOG_SIZE)
            {
                //trigger a download of the current file instead
                $logs = NULL;
            } else
            {
                if (get_file_extension($currentFile) !== 'php')
                {
                    $is_content = TRUE;
                    $logs = file_get_contents($currentFile);
                } else
                {
                    $logs = $this->processLogs($this->getLogs($currentFile));
                }
            }
        } else
        {
            $logs = [];
        }
        $data['search'] = $search;
        $data['is_content'] = $is_content;
        $data['not_include'] = $not_include ?? NULL;
        $data['logs'] = $logs;
        $data['is_simple'] = (boolean)$type_logs;
        $data['CILogViewer'] = $this;
        $data['files'] = ! empty($files) ? $files : [];
        $data['currentFile'] = ! is_null($currentFile) ? basename($currentFile) : "";
        return $this->CI->load->view(self::CI_LOG_VIEW_FILE_PATH, $data, TRUE);
    }

    public function index()
    {
        $files = $this->getFiles();
        $data['files'] = ! empty($files) ? $files : [];
        $data['CILogViewer'] = $this;
        return $this->CI->load->view(self::CI_LOG_VIEW_FILE_PATH_LIST_FILE, $data, TRUE);
    }

    public function showDashboard($search = NULL, $not_include = NULL, $type_logs = TRUE)
    {
        if ($type_logs)
        {
            $files = $this->getFilesInfo();
        } else
        {
            $files = $this->getFilesInfo(TRUE, FALSE);
        }
        $data['files'] = ! empty($files) ? $files : [];
        $data['is_simple'] = (boolean)$type_logs;
        $data['files_not_simple'] = ! empty($files_not_simple) ? $files_not_simple : [];
        $data['CILogViewer'] = $this;
        return $this->CI->load->view(self::CI_LOG_VIEW_FILE_PATH_LIST_FILE, $data, TRUE);
    }


    private function processAPIRequests($command)
    {

        if ($command === self::API_CMD_LIST)
        {
            //respond with a list of all the files
            $response["status"] = TRUE;
            $response["log_files"] = self::getFilesBase64Encoded();
        } else if ($command === self::API_CMD_VIEW)
        {
            //respond to view the logs of a particular file
            $file = $this->CI->input->get(self::API_FILE_QUERY_PARAM);
            $response["log_files"] = self::getFilesBase64Encoded();

            if (is_null($file) || empty($file))
            {
                $response["status"] = FALSE;
                $response["error"]["message"] = "Invalid File Name Supplied: [" . json_encode($file) . "]";
                $response["error"]["code"] = 400;
            } else
            {
                $singleLine = $this->CI->input->get(self::API_LOG_STYLE_QUERY_PARAM);
                $singleLine = ! is_null($singleLine) && ($singleLine === TRUE || $singleLine === "true" || $singleLine === "1") ? TRUE : FALSE;
                $logs = $this->processLogsForAPI($file, $singleLine);
                $response["status"] = TRUE;
                $response["logs"] = $logs;
            }
        } else if ($command === self::API_CMD_DELETE)
        {

            $file = $this->CI->input->get(self::API_FILE_QUERY_PARAM);

            if (is_null($file))
            {
                $response["status"] = FALSE;
                $response["error"]["message"] = "NULL value is not allowed for file param";
                $response["error"]["code"] = 400;
            } else
            {

                //decode file if necessary
                $fileExists = FALSE;

                if ($file !== "all")
                {
                    $file = basename(base64_decode($file));
                    $fileExists = file_exists($this->logFolderPath . "/" . $file);
                } else
                {
                    //check if the directory exists
                    $fileExists = file_exists($this->logFolderPath);
                }


                if ($fileExists)
                {
                    $this->deleteFiles($file);
                    $response["status"] = TRUE;
                    $response["message"] = "File [" . $file . "] deleted";
                } else
                {
                    $response["status"] = FALSE;
                    $response["error"]["message"] = "File does not exist";
                    $response["error"]["code"] = 404;
                }


            }
        } else
        {
            $response["status"] = FALSE;
            $response["error"]["message"] = "Unsupported Query Command [" . $command . "]";
            $response["error"]["code"] = 400;
        }

        //convert response to json and respond
        header("Content-Type: application/json");
        if ( ! $response["status"])
        {
            //set a generic bad request code
            http_response_code(400);
        } else
        {
            http_response_code(200);
        }
        return json_encode($response);
    }


    /*
     * This function will process the logs. Extract the log level, icon class and other information
     * from each line of log and then arrange them in another array that is returned to the view for processing
     *
     * @params logs. The raw logs as read from the log file
     * @return array. An [[], [], [] ...] where each element is a processed log line
     * */
    private function processLogs($logs)
    {

        if (is_null($logs))
        {
            return NULL;
        }

        $superLog = [];

        foreach ($logs as $log)
        {

            //get the logLine Start
            $logLineStart = $this->getLogLineStart($log);

            if ( ! empty($logLineStart))
            {
                //this is actually the start of a new log and not just another line from previous log
                $level = $this->getLogLevel($logLineStart);
                $data = [
                    "level" => $level,
                    "date"  => $this->getLogDate($logLineStart),
                    "icon"  => self::$levelsIcon[$level],
                    "class" => self::$levelClasses[$level],
                ];

                $logMessage = $this->getLogContent($log);
                $logTitle = $this->getLogTitle($log);
                $logMessage = preg_replace($this->_end_line, "\n", $logMessage);
                $logTitle = preg_replace($this->_end_line, "\n", $logTitle);

                if (strlen($logMessage) > self::MAX_STRING_LENGTH)
                {
                    $data['content'] = substr($logMessage, 0, self::MAX_STRING_LENGTH);
                    $data["extra"] = substr($logMessage, (self::MAX_STRING_LENGTH + 1));
                } else
                {
                    $data["content"] = $logMessage;
                }
                if (strlen($logTitle) > self::MAX_STRING_LENGTH)
                {
                    $data['title'] = substr($logTitle, 0, self::MAX_STRING_LENGTH);
                    $data["extra_title"] = substr($logTitle, (self::MAX_STRING_LENGTH + 1));
                } else
                {
                    $data["title"] = $logTitle;
                }

                array_push($superLog, $data);

            } else if ( ! empty($superLog))
            {
                //this log line is a continuation of previous logline
                //so let's add them as extra
                $prevLog = $superLog[count($superLog) - 1];
                $extra = (array_key_exists("extra", $prevLog)) ? $prevLog["extra"] : "";
                $extra_title = (array_key_exists("extra_title", $prevLog)) ? $prevLog["extra_title"] : "";
                $prevLog["extra"] = $extra . "<br>" . $log;
                $prevLog["extra_title"] = $extra_title . "<br>" . $log;
                $superLog[count($superLog) - 1] = $prevLog;
            } else
            {
                //this means the file has content that are not logged
                //using log_message()
                //they may be sensitive! so we are just skipping this
                //other we could have just insert them like this
//               array_push($superLog, [
//                   "level" => "INFO",
//                   "date" => "",
//                   "icon" => self::$levelsIcon["INFO"],
//                   "class" => self::$levelClasses["INFO"],
//                   "content" => $log
//               ]);
            }
        }

        return array_reverse($superLog);
    }


    /**
     * This function will extract the logs in the supplied
     * fileName
     * @param      $fileNameInBase64
     * @param bool $singleLine
     * @return array|null
     * @internal param $logs
     */
    private function processLogsForAPI($fileNameInBase64, $singleLine = FALSE)
    {

        $logs = NULL;

        //let's prepare the log file name sent from the client
        $currentFile = $this->prepareRawFileName($fileNameInBase64);

        //if the resolved current file is too big
        //just return null
        //otherwise process its content as log
        if ( ! is_null($currentFile))
        {

            $fileSize = filesize($currentFile);

            if (is_int($fileSize) && $fileSize > self::MAX_LOG_SIZE)
            {
                //trigger a download of the current file instead
                $logs = NULL;
            } else
            {
                $logs = $this->getLogsForAPI($currentFile, $singleLine);
            }
        }

        return $logs;
    }


    /*
     * extract the log level from the logLine
     * @param $logLineStart - The single line that is the start of log line.
     * extracted by getLogLineStart()
     *
     * @return log level e.g. ERROR, DEBUG, INFO
     * */
    private function getLogLevel($logLineStart)
    {
        preg_match(self::LOG_LEVEL_PATTERN, $logLineStart, $matches);
        return $matches[0];
    }


    /*
     * extract the log level from the logLine
     * @param $logLineStart - The single line that is the start of log line.
     * extracted by getLogLineStart()
     *
     * @return log content
     * */
    private function getLogContent($logLineStart)
    {
        return preg_replace(self::LOG_CONTENT_PATTERN, '${3}', $logLineStart);
    }
    /*
     * extract the log level from the logLine
     * @param $logLineStart - The single line that is the start of log line.
     * extracted by getLogLineStart()
     *
     * @return log content
     * */
    private function getLogTitle($logLineStart)
    {
        return preg_replace(self::LOG_TITLE_PATTERN, '${4}', $logLineStart);
    }


    private function getLogDate($logLineStart)
    {
        return preg_replace(self::LOG_DATE_PATTERN, '', $logLineStart);
    }

    private function getLogLineStart($logLine)
    {
        preg_match(self::LOG_LINE_START_PATTERN, $logLine, $matches);
        if ( ! empty($matches))
        {
            return $matches[0];
        }
        return "";
    }

    /*
     * returns an array of the file contents
     * each element in the array is a line
     * in the underlying log file
     * @returns array | each line of file contents is an entry in the returned array.
     * @params complete fileName
     * */
    private function getLogs($fileName)
    {
        $size = filesize($fileName);
        if ( ! $size || $size > self::MAX_LOG_SIZE)
            return NULL;
        return file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    /**
     * This function will get the contents of the log
     * file as a string. It will first check for the
     * size of the file before attempting to get the contents.
     *
     * By default it will return all the log contents as an array where the
     * elements of the array is the individual lines of the files
     * otherwise, it will return all file content as a single string with each line ending
     * in line break character "\n"
     * @param      $fileName
     * @param bool $singleLine
     * @return bool|string
     */
    private function getLogsForAPI($fileName, $singleLine = FALSE)
    {
        $size = filesize($fileName);
        if ( ! $size || $size > self::MAX_LOG_SIZE)
            return "File Size too Large. Please donwload it locally";

        return ( ! $singleLine) ? file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : file_get_contents($fileName);
    }


    /*
     * This will get all the files in the logs folder
     * It will reverse the files fetched and
     * make sure the latest log file is in the first index
     *
     * @param boolean. If true returns the basename of the files otherwise full path
     * @returns array of file
     * */
    private function getFiles($basename = TRUE, $is_simple = TRUE)
    {

        if ($is_simple)
        {
            $files = glob($this->fullLogFilePath);
        } else
        {
            $files = glob($this->fullLogFilePathNotSimple);
        }
        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');
        if ($basename && is_array($files))
        {
            foreach ($files as $k => $file)
            {
                $files[$k] = basename($file);
            }
        }
        return array_values($files);
    }


    private function getFilesInfo($basename = TRUE, $is_simple = TRUE)
    {

        if ($is_simple)
        {
            $files = glob($this->fullLogFilePath);
        } else
        {
            $files = glob($this->fullLogFilePathNotSimple);
        }
        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');
        $file_info = [];
        if ($basename && is_array($files))
        {
            foreach ($files as $k => $file)
            {
                $file_info[$k]['name'] = basename($file);
            }
        }
        return $file_info;
    }


    /**
     * This function will return an array of available log
     * files
     * The array will containt the base64encoded name
     * as well as the real name of the fiile
     * @return array
     * @internal param bool $appendURL
     * @internal param bool $basename
     */
    private function getFilesBase64Encoded()
    {

        $files = glob($this->fullLogFilePath);

        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');

        $finalFiles = [];

        //if we're to return the base name of the files
        //let's do that here
        foreach ($files as $file)
        {
            array_push($finalFiles, ["file_b64" => base64_encode(basename($file)), "file_name" => basename($file)]);
        }

        return $finalFiles;

    }

    /*
     * Delete one or more log file in the logs directory
     * @param filename. It can be all - to delete all log files - or specific for a file
     * */
    private function deleteFiles($fileName)
    {

        if ($fileName == "all")
        {
            array_map("unlink", glob($this->fullLogFilePath));
        } else
        {
            unlink($this->logFolderPath . "/" . basename($fileName));
        }
        return;
    }

    /*
     * Download a particular file to local disk
     * This should only be called if the file exists
     * hence, the file exist check has ot be done by the caller
     * @param $fileName the complete file path
     * */
    private function downloadFile($file)
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }


    /**
     * This function will take in the raw file
     * name as sent from the browser/client
     * and append the LOG_FOLDER_PREFIX and decode it from base64
     * @param $fileNameInBase64
     * @return null|string
     * @internal param $fileName
     */
    private function prepareRawFileName($fileNameInBase64)
    {

        //let's determine what the current log file is
        if ( ! is_null($fileNameInBase64) && ! empty($fileNameInBase64))
        {
            $currentFile = $this->logFolderPath . "/" . basename(base64_decode($fileNameInBase64));
        } else
        {
            $currentFile = NULL;
        }

        return $currentFile;
    }


}



