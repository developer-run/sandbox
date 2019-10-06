<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    FileTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Utils;

use Devrun\InvalidArgumentException;
use Nette\Utils\Finder;

trait FileTrait
{


    /**
     * Copy directory of file.
     *
     * @param $source
     * @param $dest
     *
     * @return bool
     */
    public static function copy($source, $dest, $mode = 0777)
    {
        if (is_file($source)) {
            return copy($source, $dest);
        }

        $status = TRUE;

        if (!is_dir($dest)) {
            umask(0000);
            mkdir($dest, $mode, TRUE);
        }

        $dir = dir($source);
        while (FALSE !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            if ($dest !== "$source/$entry") {
                if (self::copy("$source/$entry", "$dest/$entry", $mode) === FALSE) {
                    $status = FALSE;
                }
            }
        }
        $dir->close();

        return $status;
    }


    /**
     * Removes directory.
     *
     * @static
     *
     * @param string $dirname
     * @param bool   $recursive
     *
     * @return bool
     */
    public static function rmdir($dirname, $recursive = FALSE)
    {
        if (!$recursive) {
            return rmdir($dirname);
        }

        /** @var \SplFileInfo[] $dirContent */
        $dirContent = Finder::find('*')->from($dirname)->childFirst();

        foreach ($dirContent as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }

        @rmdir($dirname);
        return TRUE;
    }


    /**
     * Purges directory.
     *
     * @param string
     * @return void
     */
    public static function purge($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $entry) {
            if ($entry->isDir()) {
                rmdir($entry);
            } else {
                unlink($entry);
            }
        }
    }


    /**
     * remove dir from masks files, only first level, not to deep
     *
     * @param $dir
     * @return void
     */
    public static function eraseDirFromFiles($dir, $masks)
    {
        /** @var \SplFileInfo[] $dirContent */
        $dirContent = Finder::findFiles($masks)->in($dir);

        foreach ($dirContent as $file) {
            @unlink($file->getPathname());
        }
    }


    /**
     * @param $dirName
     *
     * @see https://stackoverflow.com/questions/7497733/how-can-use-php-to-check-if-a-directory-is-empty
     * @return bool
     */
    public static function isDirEmpty($dirName)
    {
        return !(new \FilesystemIterator($dirName))->valid();
    }



    public static function removeEmptySubFolders($path)
    {
        $empty = true;
        foreach (glob($path . DIRECTORY_SEPARATOR . "*") as $file) {
            if (is_dir($file)) {
                if (!self::removeEmptySubFolders($file)) $empty = false;
            } else {
                $empty = false;
            }
        }
        if ($empty) {
            rmdir($path);
        }
        return $empty;
    }



    /**
     * Get relative path.
     *
     * @static
     *
     * @param $from
     * @param $to
     *
     * @return string
     */
    public static function getRelativePath($from, $to, $directorySeparator = NULL)
    {
        $directorySeparator = $directorySeparator ?: ((substr(PHP_OS, 0, 3) === 'WIN') ? '\\' : '/');

        if ($directorySeparator !== '/') {
            $from = str_replace($directorySeparator, '/', $from);
            $to   = str_replace($directorySeparator, '/', $to);
        }

        $from    = substr($from, -1) !== '/' ? $from . '/' : $from;
        $to      = substr($to, -1) !== '/' ? $to . '/' : $to;
        $from    = explode('/', $from);
        $to      = explode('/', $to);
        $relPath = $to;

        foreach ($from as $depth => $dir) {
            if ($dir === $to[$depth]) {
                array_shift($relPath);
            } else {
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath   = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        $relPath = implode('/', $relPath);
        $relPath = substr($relPath, -1) === '/' ? substr($relPath, 0, -1) : $relPath;

        if ($directorySeparator !== '/') {
            $relPath = str_replace('/', $directorySeparator, $relPath);
        }

        return $relPath;
    }


    /**
     * get full class namespace name [FrontModule\Presenters\TestPresenter]
     *
     * @param $file
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getClassByFile($file)
    {
        $classes = $this->getClassesFromFile($file);

        if (count($classes) !== 1) {
            throw new InvalidArgumentException("File '{$file}' must contain only one class.");
        }

        return $classes[0];
    }


    /**
     * get array of full class namespace name
     *
     * @param $file
     *
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getClassesFromFile($file)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException("File '{$file}' does not exist.");
        }

        $namespace = 0;
        $classes   = array();
        $tokens    = token_get_all(file_get_contents($file));
        $count     = count($tokens);

        $dlm = FALSE;
        for ($i = 2; $i < $count; $i++) {
            if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] == "phpnamespace" || $tokens[$i - 2][1] == "namespace")) ||
                ($dlm && $tokens[$i - 1][0] == T_NS_SEPARATOR && $tokens[$i][0] == T_STRING)
            ) {
                if (!$dlm) $namespace = 0;
                if (isset($tokens[$i][1])) {
                    $namespace = $namespace ? $namespace . "\\" . $tokens[$i][1] : $tokens[$i][1];
                    $dlm       = TRUE;
                }
            } elseif ($dlm && ($tokens[$i][0] != T_NS_SEPARATOR) && ($tokens[$i][0] != T_STRING)) {
                $dlm = FALSE;
            }
            if (($tokens[$i - 2][0] == T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] == "phpclass"))
                && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING
            ) {
                $class_name = $tokens[$i][1];
                $classes[]  = $namespace . '\\' . $class_name;
            }
        }
        return $classes;
    }


}