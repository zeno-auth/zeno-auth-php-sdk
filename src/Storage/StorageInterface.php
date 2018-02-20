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

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
interface StorageInterface
{
    public function set(string $key, $value): void;

    public function get(string $key, $default = null);

    public function has(string $key): bool;

    public function remove(string $key): void;

    public function clear(): void;
}
