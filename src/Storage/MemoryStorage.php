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
final class MemoryStorage implements StorageInterface
{
    /**
     * @var array
     */
    private $data = [];

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
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
            throw new InvalidArgumentException(sprintf('Data with key "%s" not defined.', $key));
        }
    }

    public function clear(): void
    {
        $this->data = [];
    }
}
