<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
namespace Diamante\DeskBundle\Infrastructure\Persistence;

use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfigurationRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfiguration;

class DoctrineBranchEmailConfigurationRepository extends DoctrineGenericRepository
    implements BranchEmailConfigurationRepository
{
    /**
     * Retrieves BranchEmailConfiguration by Branch Id
     *
     * @param $branchId
     * @return BranchEmailConfiguration
     */
    public function getByBranchId($branchId)
    {
        return $this->findOneBy(array('branch' => $branchId));
    }

    /**
     * Retrieves BranchEmailConfiguration using $supportAddress and $customerDomain as Criteria
     *
     * @param $supportAddress
     * @param $customerDomain
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getBySupportAddressAndCustomerDomainCriteria($supportAddress, $customerDomain)
    {
        $customerDomainRegExp = "[[:<:]]" . $customerDomain . "[[:>:]]";
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('DiamanteDeskBundle:BranchEmailConfiguration', 'j');
        $rsm->addScalarResult('branch_id', 'branch_id');
        $rsm->addScalarResult('criteria', 'criteria');

        $result = $this->getEntityManager()->createNativeQuery("
            SELECT j.branch_id as branch_id,
               (j.support_address = :supportAddress AND
                 j.customer_domains REGEXP :customerDomainRegExp) * 3 +
               (j.support_address = :supportAddress AND
                 j.customer_domains = '') * 2 +
               (j.support_address = '' AND
                 j.customer_domains REGEXP :customerDomainRegExp) * 1
            AS criteria
            FROM diamante_branch_email_configuration j ORDER BY criteria DESC LIMIT 1
        ", $rsm)
            ->setParameter('supportAddress', $supportAddress)
            ->setParameter('customerDomainRegExp', $customerDomainRegExp)
            ->getOneOrNullResult();

        if ($result['criteria']) {
            return $result['branch_id'];
        } else {
            return 0;
        }
    }
}
