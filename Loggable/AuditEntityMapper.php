<?php

namespace Diamante\DeskBundle\Loggable;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Util\ClassUtils;
use Diamante\UserBundle\Entity\DiamanteUser;

class AuditEntityMapper
{
    /**
     * @var ArrayCollection
     */
    protected $entryMap;

    /**
     * @var ArrayCollection
     */
    protected $entryFieldMap;

    public function __construct()
    {
        $this->entryMap = new ArrayCollection();
        $this->entryFieldMap = new ArrayCollection();
    }

    /**
     * @param string $userClass
     * @param string $auditEntryClass
     */
    public function addAuditEntryClass($userClass, $auditEntryClass)
    {
        $this->entryMap->set($userClass, $auditEntryClass);
    }

    /**
     * @param string $userClass
     * @param string $auditEntryFieldClass
     */
    public function addAuditEntryFieldClass($userClass, $auditEntryFieldClass)
    {
        $this->entryFieldMap->set($userClass, $auditEntryFieldClass);
    }

    /**
     * @param DiamanteUser|null $user
     *
     * @return string
     */
    public function getAuditEntryClass(DiamanteUser $user = null)
    {
        if ($user === null) {
            return $this->entryMap->first();
        }

        $userClass = ClassUtils::getRealClass($user);

        if (!$this->entryMap->containsKey($userClass)) {
            throw new \InvalidArgumentException(sprintf('Audit entry not found for "%s"', $userClass));
        }

        return $this->entryMap->get($userClass);
    }

    /**
     * @param DiamanteUser|null $user
     *
     * @return string
     */
    public function getAuditEntryFieldClass(DiamanteUser $user = null)
    {
        if ($user === null) {
            return $this->entryFieldMap->first();
        }

        $userClass = ClassUtils::getRealClass($user);

        if (!$this->entryFieldMap->containsKey($userClass)) {
            throw new \InvalidArgumentException(sprintf('Audit entry field not found for "%s"', $userClass));
        }

        return $this->entryFieldMap->get($userClass);
    }
}
