<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model;

use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequestFactory;

/**
 * Class BackendUserInfo
 */
final class BackendUserInfo
{
    /**
     * @var string
     */
    protected string $username;
    /**
     * @var int
     */
    protected int $admin;
    /**
     * @var string
     */
    protected string $email;
    /**
     * @var string
     */
    protected string $realName;
    /**
     * @var string|mixed
     */
    protected string $remoteAddress;
    /**
     * @var string|mixed
     */
    protected string $siteName;
    /**
     * @var string
     */
    protected string $httpUserAgent;
    /**
     * @var string
     */
    protected string $httpAcceptLanguage;

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @var string
     */
    protected string $host;

    /**
     * @var bool
     */
    protected bool $isAdmin = false;

    /**
     * @var bool
     */
    protected bool $isSystemMaintainer = false;

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @return bool
     */
    public function isSystemMaintainer(): bool
    {
        return $this->isSystemMaintainer;
    }

    /**
     * BackendUserInfo constructor.
     *
     * @param array $backendUser
     */
    public function __construct(array $backendUser)
    {
        $this->username = $backendUser['username'];
        $this->admin = $backendUser['admin'];
        $this->email = $backendUser['email'];
        $this->realName = $backendUser['realName'];

        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
        $normalizedParams = $request->getAttribute('normalizedParams');

        if (!$normalizedParams instanceof NormalizedParams) {
            $normalizedParams = NormalizedParams::createFromServerParams($_SERVER);
        }
        $this->remoteAddress = $normalizedParams->getRemoteAddress();
        $this->httpAcceptLanguage = $normalizedParams->getHttpAcceptLanguage();
        $this->httpUserAgent = $normalizedParams->getHttpUserAgent();
        $this->host = $normalizedParams->getRequestHost();
        $this->siteName = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
        $this->isSystemMaintainer = in_array((int)$backendUser['uid'], array_map('intval', $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemMaintainers'] ?? []));
    }

    /**
     * @return string
     */
    public function getHttpUserAgent(): string
    {
        return $this->httpUserAgent;
    }

    /**
     * @return string
     */
    public function getHttpAcceptLanguage(): string
    {
        return $this->httpAcceptLanguage;
    }

    /**
     * @return mixed|string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * @return mixed|string
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return int
     */
    public function getAdmin(): int
    {
        return $this->admin;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getRealName(): string
    {
        return $this->realName;
    }

}