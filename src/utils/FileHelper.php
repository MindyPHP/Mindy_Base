<?php

/**
 *
 * CoreFileHelper class file.
 *
 * @author Falaleev Maxim <max@studio107.com>
 * @link http://studio107.ru/
 * @copyright Copyright &copy; 2010-2012 Studio107
 * @license http://www.cms107.com/license/
 * @package modules.core.utils
 * @since 1.1.1
 * @version 1.0
 *
 */
class FileHelper extends CFileHelper
{
    public static function remove($dir, $self = false)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $undeleted = [];

        $dirs = self::findDirs($dir, $self);
        $files = self::findFiles($dir);

        //Устанавливаем handler для отлова Warning
        set_error_handler(array(__CLASS__, "warningHandler"), E_ALL);

        if (!empty($files)) {

            for ($i = 0; $i != count($files); $i++) {
                try {
                    unlink($files[$i]);
                } catch (ErrorException $e) {
                    $message = explode(': ', $e->getMessage());
                    $undeleted[] = array(
                        'name' => $files[$i],
                        'message' => end($message),
                        'type' => 'file'
                    );
                }
            }

        }

        if (!empty($dirs)) {

            for ($i = 0; $i != count($dirs); $i++) {
                try {
                    rmdir($dirs[$i]);
                } catch (ErrorException $e) {
                    $message = explode(': ', $e->getMessage());
                    $undeleted[] = array(
                        'name' => $dirs[$i],
                        'message' => end($message),
                        'type' => 'dir'
                    );
                }
            }

        }

        //Восстанавливаем родной handler
        restore_error_handler();

        return $undeleted;
    }

    //handler для отлова Warning
    public static function warningHandler($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function findDirs($dir, $self = false, $options = [])
    {
        $fileTypes = [];
        $exclude = [];
        $level = -1;
        extract($options);
        $list = self::findDirsRecursive($dir, '', $fileTypes, $exclude, $level);
        ksort($list);
        if (!$self) {
            unset($list[0]);
            $list = array_values($list);
        }

        return $list;
    }

    protected static function findDirsRecursive($dir, $base, $fileTypes, $exclude, $level)
    {
        $list = [];
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            $isDir = is_dir($path);
            if (self::validatePath($base, $file, $isDir, $fileTypes, $exclude)) {
                if ($isDir && $level) {
                    $list = array_merge($list, self::findDirsRecursive($path, $base . '/' . $dir, $fileTypes, $exclude, $level - 1));
                }
            }
        }

        $list[] = $dir;

        closedir($handle);
        return $list;
    }

    //Отправка файла пользователю для скачивания
    public function download($file, $fakeName = false, $serverHandled = false)
    {
        if (is_file($file) && is_readable($file) && !headers_sent()) {

            $baseName = basename($file);
            if ($fakeName) {
                $filename = md5($baseName);
            } else {
                $filename = $baseName;
            }

            // Отключаем кэш браузера
            header('Cache-control: private');
            header('Pragma: private');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . self::getSize($file));
            header('Content-Disposition: attachment;filename="' . $filename . '"');

            if ($serverHandled)
                header('X-Sendfile: ' . $file);

            die();
        }

        return false;
    }

    //Alias к getSize()
    public function size($dir, $human = false, $format = '0.00')
    {
        return self::getSize($dir, $human, $format);
    }

    //Возвращает размер директории или размер файла
    public function getSize($dir, $human = false, $format = '0.00')
    {
        $size = 0;
        if (is_dir($dir)) {
            foreach (self::findFiles($dir) as $item)
                if (is_file($item)) {
                    $size += (int)sprintf("%u", filesize($item));
                }
        } else {
            $size = filesize($dir);
        }

        return $human ? self::formatFileSize($size, $format) : $size;
    }

    //Возвращает размер файла в человеко понятной форме
    private function formatFileSize($bytes, $format)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

        $bytes = max($bytes, 0);
        $expo = floor(($bytes ? log($bytes) : 0) / log(1024));
        $expo = min($expo, count($units) - 1);

        $bytes /= pow(1024, $expo);

        return Yii::app()->numberFormatter->format($format, $bytes) . ' ' . $units[$expo];
    }

    public function setPermissions($dir, $permissions)
    {
        $permissions = octdec(str_pad($permissions, 4, "0", STR_PAD_LEFT));

        if (@chmod($dir, $permissions)) {
            return true;
        } else {
            return false;
        }
    }
}
