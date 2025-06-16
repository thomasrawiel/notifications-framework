<?php

namespace TRAW\NotificationsFramework\Events\Configuration;

/**
 * Class RepositoryClassesEvent
 */
class RepositoryClassesEvent
{
    /**
     * @param string $repositoryClass
     * @param string $objectClass
     */
    public function __construct(private string $repositoryClass, private string $objectClass)
    {
    }

    /**
     * @return string
     */
    public function getRepositoryClass(): string
    {
        return $this->repositoryClass;
    }

    /**
     * @param string $repositoryClass
     *
     * @return void
     */
    public function setRepositoryClass(string $repositoryClass): void
    {
        $this->repositoryClass = $repositoryClass;
    }

    /**
     * @return string
     */
    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    /**
     * @param string $objectClass
     *
     * @return void
     */
    public function setObjectClass(string $objectClass): void
    {
        $this->objectClass = $objectClass;
    }
}