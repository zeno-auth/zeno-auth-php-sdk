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

use GuzzleHttp\Client;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use ZenoAuth\SDK\Storage\NativeSessionStorage;
use ZenoAuth\SDK\Storage\StorageInterface;
use function GuzzleHttp\Psr7\build_query;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class ZenoAuth
{
    const API_VERSION = 'v1';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Request
     */
    private $request;

    /**
     * Constructor.
     *
     * @param Config           $config
     * @param StorageInterface $storage
     */
    public function __construct(Config $config, StorageInterface $storage = null)
    {
        $this->config = $config;

        if (null === $storage) {
            $storage = new NativeSessionStorage();
        }

        $this->storage = $storage;
        $this->client = new Client(['base_uri' => sprintf('%s/api/%s/', $this->config->getUri(), self::API_VERSION)]);
        $this->request = Request::createFromGlobals();
    }

    public function issueToken(string $grantType, string $scopes, array $params = []): void
    {
        $response = $this->client->request(
            'post',
            'tokens',
            [
                'json'    => array_merge(
                    $params,
                    [
                        'grant_type'    => $grantType,
                        'scopes'        => $scopes,
                        'client_id'     => $this->config->getClientId(),
                        'client_secret' => $this->config->getClientSecret(),
                    ]
                ),
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        $tokens = json_decode($response->getBody()->getContents(), true);

        if (isset($tokens['data'])) {
            $tokens = $tokens['data'];

            $this->setAccessToken($tokens['access_token'], $tokens['expires_in']);

            if (array_key_exists('refresh_token', $tokens)) {
                $this->setRefreshToken($tokens['refresh_token']);
            }
        }
    }

    public function setAccessToken(string $accessToken, int $ttl = null): void
    {
        $this->storage->set('access_token', $accessToken);

        if (null !== $ttl) {
            $this->storage->set(
                'access_token_expires_at',
                date('Y-m-d H:i:s', strtotime(sprintf('+%d seconds', $ttl)))
            );
        }
    }

    public function login(string $responseType, string $scopes, string $redirectUri = null): string
    {
        $this->storage->clear();

        if (null === $redirectUri) {
            $scheme = isset($_SERVER['HTTPS']) ? "https" : "http";
            $redirectUri = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        $this->storage->set('state', $state = uniqid('za'));

        $queryStr = build_query(
            [
                'response_type' => $responseType,
                'scopes'        => $scopes,
                'client_id'     => $this->config->getClientId(),
                'state'         => $state,
                'continue'      => $redirectUri,
            ]
        );

        return rtrim($this->config->getUri(), '/') . '/oauth2/authorize?' . $queryStr;
    }

    public function getUser(): ?User
    {
        if (null !== $this->user) {
            return $this->user;
        }

        if (null !== $this->getAccessToken()) {
            return $this->getUserFromAccessToken();
        }

        if (null !== $this->getAccessTokenFromAuthorization()) {
            return $this->getUserFromAccessToken();
        }

        return null;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->storage->set('refresh_token', $refreshToken);
    }

    public function getAccessToken(): ?string
    {
        return $this->storage->get('access_token');
    }

    public function getRefreshToken(): ?string
    {
        return $this->storage->get('refresh_token');
    }

    public function getAuthorizationCode(): ?string
    {
        if (null !== $code = $this->request->get('code')) {
            $this->assertCSRF();

            return $code;
        }

        return null;
    }

    public function getState(): ?string
    {
        return $this->storage->get('state');
    }

    private function getUserFromAccessToken(): ?User
    {
        try {
            $response = $this->client->get(
                'users/me',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getAccessToken(),
                        'Accept'        => 'application/json',
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->user = new User($data['data']);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getAccessTokenFromAuthorization(): ?string
    {
        if (null !== $accessToken = $this->request->get('access_token')) {
            $this->assertCSRF();

            $this->setAccessToken($accessToken, (int) $this->request->get('expires_in'));

            return $accessToken;
        }

        return null;
    }

    private function assertCSRF(): void
    {
        if (null !== $state = $this->getState()) {
            if ($state !== $this->request->get('state')) {
                throw new RuntimeException('Invalid CSRF token.');
            }
        }
    }
}
