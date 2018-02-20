<?php
/**
 * This file is part of the ZenoAuth - PHP SDK package.
 *
 * (c) 2018 Borobudur <http://borobudur.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ZenoAuth\SDK\Storage;

use InvalidArgumentException;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class NativeSessionStorage implements StorageInterface
{
    const PARAM_NAME = '_zeno_auth';

    /**
     * @var array
     */
    private $data = [];

    public function __construct()
    {
        $this->run(
            function () {
                if (isset($_SESSION[self::PARAM_NAME])) {
                    $this->data = (array) $_SESSION['_zeno_auth'];
                }
            }
        );
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
        $this->refresh();
    }

    public function get(string $key, $default = null)
    {
        if ($this->has($key)) {
            return $this->data[$key];
        }

        return $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function remove(string $key): void
    {
        if (!$this->has($key)) {
            throw new InvalidArgumentException(sprintf('Data with key "%s" is not defined.', $key));
        }

        unset($this->data[$key]);
        $this->refresh();
    }

    public function clear(): void
    {
        $this->data = [];
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->run(
            function () {
                $_SESSION[self::PARAM_NAME] = $this->data;
            }
        );
    }

    private function run(callable $scope): void
    {
        session_start();

        $scope();

        session_write_close();
    }
}
