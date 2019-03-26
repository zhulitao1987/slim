<?php
/**
 * User: Administrator
 * Date: 2016/12/8
 */

class Recorder
{
    private static $data;

    private $dataFile;

    public function __construct()
    {
        $this->dataFile =  ESIGN_ROOT . "/comm/inc.dat";
        //-------读取配置文件--------------
        if (empty(self::$data)) {
            if (is_file($this->dataFile)) {
                $datFileContents = file_get_contents($this->dataFile);
            } else {
                $datFileContents = '';
            }
            self::$data = json_decode($datFileContents, true);
        }
    }

    public function write($name, $value)
    {
        self::$data[$name] = $value;
    }

    public function read($name)
    {
        if (empty(self::$data[$name])) {
            return null;
        } else {
            return self::$data[$name];
        }
    }

    public function delete($name)
    {
        unset(self::$data[$name]);
    }

    function __destruct()
    {
        //$_SESSION['esign_userData'] = self::$data;
        $this->write2file();
    }

    public function write2file()
    {
        $datFile = $this->dataFile;
        $s = file_put_contents($datFile, Util::jsonEncode(self::$data));
        if ($s === false) {
            throw new Exception('save data to inc.dat faiture - ' . $datFile);
        }
    }
}