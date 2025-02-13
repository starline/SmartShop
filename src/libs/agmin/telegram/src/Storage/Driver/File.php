<?php
/**
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bot\Storage\Driver;

use Bot\Entity\TempFile;
use Bot\Exception\StorageException;
use DirectoryIterator;
use RuntimeException;

/**
 * Stores data in json formatted text files
 */
class File
{
    /**
     * Lock file object
     *
     * @var TempFile
     */
    private static $lock;

    /**
     * Initialize - define paths
     *
     * @throws StorageException
     */
    public static function initializeStorage(): bool
    {
        if (!defined('STORAGE_GAME_PATH')) {
            if (!defined('DATA_PATH')) {
                throw new StorageException('Data path is not set!');
            }

            define('STORAGE_GAME_PATH', DATA_PATH . '/game');

            if (!is_dir(STORAGE_GAME_PATH) && !mkdir($concurrentDirectory = STORAGE_GAME_PATH, 0755, true) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        return true;
    }


    /**
     * Dummy function
     *
     * @return bool
     */
    public static function createStructure(): bool
    {
        self::initializeStorage();
        return true;
    }


    /**
     * Read data from the file
     *
     * @param string $id
     *
     * @return array|bool
     * @throws StorageException
     */
    public static function selectFromGame(string $id)
    {
        if (empty($id)) {
            throw new StorageException('Id is empty!');
        }

        if (file_exists(STORAGE_GAME_PATH . '/' . $id . '.json')) {
            return json_decode(file_get_contents(STORAGE_GAME_PATH . '/' . $id . '.json'), true) ?? [];
        }

        return [];
    }

    /**
     * Place data to the file
     *
     * @param string $id
     * @param array  $data
     *
     * @return bool
     * @throws StorageException
     */
    public static function insertToGame(string $id, array $data): bool
    {
        if (empty($id)) {
            throw new StorageException('Id is empty!');
        }

        $data['updated_at'] = time();

        if (!isset($data['created_at'])) {
            $data['created_at'] = $data['updated_at'];
        }

        if (file_exists(STORAGE_GAME_PATH . '/' . $id . '.json')) {
            return file_put_contents(STORAGE_GAME_PATH . '/' . $id . '.json', json_encode($data));
        }

        return false;
    }

    /**
     * Remove data file
     *
     * @param string $id
     *
     * @return bool
     * @throws StorageException
     */
    public static function deleteFromGame(string $id): bool
    {
        if (empty($id)) {
            throw new StorageException('Id is empty!');
        }

        if (file_exists(STORAGE_GAME_PATH . '/' . $id . '.json')) {
            return unlink(STORAGE_GAME_PATH . '/' . $id . '.json');
        }

        return false;
    }

    /**
     * Lock the file to prevent another process modifying it
     *
     * @param string $id
     *
     * @return bool
     * @throws StorageException
     */
    public static function lockGame(string $id): bool
    {
        if (empty($id)) {
            throw new StorageException('Id is empty!');
        }

        self::$lock = new TempFile($id);
        if (self::$lock->getFile() === null) {
            return false;
        }

        if (flock(fopen(self::$lock->getFile()->getPathname(), 'ab+'), LOCK_EX)) {
            if (!file_exists(STORAGE_GAME_PATH . '/' . $id . '.json')) {
                file_put_contents(STORAGE_GAME_PATH . '/' . $id . '.json', json_encode([]));
            }

            return true;
        }

        return false;
    }

    /**
     * Unlock the file after
     *
     * @param string $id
     *
     * @return bool
     * @throws StorageException
     */
    public static function unlockGame(string $id): bool
    {
        if (empty($id)) {
            throw new StorageException('Id is empty!');
        }

        if (self::$lock === null) {
            throw new StorageException('No lock file object!');
        }

        if (self::$lock->getFile() === null) {
            return false;
        }

        return flock(fopen(self::$lock->getFile()->getPathname(), 'ab+'), LOCK_UN);
    }

    /**
     * Select inactive data fields from database
     *
     * @param int $time
     *
     * @return array
     * @throws StorageException
     */
    public static function listFromGame(int $time = 0): array
    {
        if (!is_numeric($time)) {
            throw new StorageException('Time must be a number!');
        }

        $ids = [];
        foreach (new DirectoryIterator(STORAGE_GAME_PATH) as $file) {
            if (!$file->isDir() && !$file->isDot() && $file->getMTime() + $time < time()) {
                $data = file_get_contents($file->getPathname());
                $json = json_decode($data, true);
                $data_stripped = json_encode(['game_code' => $json['game_code'] ?? null]);

                $ids[] = ['id' => trim(basename($file->getFilename(), '.json')), 'data' => $data_stripped, 'updated_at' => date('H:i:s d-m-Y', filemtime($file->getPathname()))];
            }
        }

        return $ids;
    }
}
