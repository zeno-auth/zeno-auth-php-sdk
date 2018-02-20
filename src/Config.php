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

namespace ZenoAuth\SDK;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class Config
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * Constructor.
     *
     * @param string $uri
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(string $uri, string $clientId, string $clientSecret = null)
    {
        $this->uri = $uri;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }
}
