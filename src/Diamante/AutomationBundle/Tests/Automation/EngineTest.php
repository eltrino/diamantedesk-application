<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Automation;

use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\WorkflowAction;
use Diamante\AutomationBundle\Entity\WorkflowRule;
use Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\DeskBundle\Entity\Branch;
use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    const SUBJECT = 'Subject';
    const DESCRIPTION = 'Description';

    /**
     * @var \Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider
     * @Mock Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider
     */
    private $configurationProvider;

    /**
     * @var \Diamante\AutomationBundle\Automation\ActionProvider
     * @Mock Diamante\AutomationBundle\Automation\ActionProvider
     */
    private $actionProvider;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     * @Mock Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrineRegistry;

    /**
     * @var \Diamante\AutomationBundle\Rule\Condition\ConditionFactory
     * @Mock Diamante\AutomationBundle\Rule\Condition\ConditionFactory
     */
    private $conditionFactory;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     * @Mock Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \Diamante\AutomationBundle\Infrastructure\GenericTargetEntityProvider
     * @Mock Diamante\AutomationBundle\Infrastructure\GenericTargetEntityProvider
     */
    private $targetProvider;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     * @Mock Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    private $ruleRepository;

    /**
     * @var \Diamante\AutomationBundle\Automation\Action\UpdatePropertyAction
     * @Mock Diamante\AutomationBundle\Automation\Action\UpdatePropertyAction
     */
    private $updatePropertyAction;

    /**
     * @var \Diamante\AutomationBundle\Automation\Action\Email\NotifyByEmailAction
     * @Mock Diamante\AutomationBundle\Automation\Action\Email\NotifyByEmailAction
     */
    private $notifyByEmailAction;

    /**
     * @var Engine
     */
    private $service;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->doctrineRegistry
            ->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->em));

        $this->service = new Engine(
            $this->configurationProvider,
            $this->actionProvider,
            $this->doctrineRegistry,
            $this->conditionFactory,
            $this->logger,
            $this->targetProvider
        );
    }

    /**
     * @test
     */
    public function testCreateFact()
    {
        $action = 'created';
        $changeset = $this->getChangeset();

        $fact = $this->service->createFact('ticket', $action, $changeset);

        $this->assertInternalType('array', $fact->getTarget());
        $this->assertEquals('ticket', $fact->getTargetType());
        $this->assertInternalType('array', $fact->getTargetChangeset());
    }

    /**
     * @test
     */
    public function testProcess()
    {
        $action = 'created';
        $changeset = $this->getChangeset();
        $rule = $this->getRule();
        $fact = $this->service->createFact('ticket', $action, $changeset);
        $context = new ExecutionContext(['status' => Status::CLOSED]);

        $this->em
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->ruleRepository));

        $this->ruleRepository
            ->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue([$rule]));

        $this->conditionFactory
            ->expects($this->any())
            ->method('getCondition')
            ->will(
                $this->returnCallback(
                    function ($type, $parameters) {
                        $class = sprintf('Diamante\AutomationBundle\Rule\Condition\Expression\%s', $type);
                        $property = key($parameters);
                        $expectedValue = $parameters[$property];
                        $conditions = new $class($property, $expectedValue);

                        return $conditions;
                    }
                )
            );

        $this->actionProvider
            ->expects($this->once())
            ->method('getActions')
            ->will($this->returnValue([$this->updatePropertyAction]));

        $this->updatePropertyAction
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context));

        $this->updatePropertyAction
            ->expects($this->any())
            ->method('execute');

        $this->service->process($fact);
    }

    /**
     * @test
     */
    public function testProcessRule()
    {
        $ticket = $this->getTarget();
        $rule = $this->getRule();
        $context = new ExecutionContext(['status' => Status::CLOSED]);

        $this->configurationProvider
            ->expects($this->once())
            ->method('getEntityConfiguration')
            ->will($this->returnValue(new ParameterBag($this->getEntities()['ticket'])));

        $this->targetProvider
            ->expects($this->once())
            ->method('getTargets')
            ->will($this->returnValue([$ticket]));

        $this->actionProvider
            ->expects($this->any())
            ->method('getActions')
            ->will($this->returnValue([$this->notifyByEmailAction]));

        $this->configurationProvider
            ->expects($this->once())
            ->method('getTargetByEntity')
            ->will($this->returnValue('ticket'));

        $this->notifyByEmailAction
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context));

        $result = $this->service->processRule($rule);

        $this->assertEquals(1, $result);
    }

    /**
     * @return WorkflowRule
     */
    private function getRule()
    {
        $rule = new WorkflowRule('workflow_rule', 'ticket');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $equalCondition = new Condition('Eq', ['status' => 'new'], $group);
        $notEqualCondition = new Condition('Neq', ['status' => 'open'], $group);
        $action = new WorkflowAction('UpdateProperty', ['status' => 'closed'], $rule);

        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($equalCondition);
        $group->addCondition($notEqualCondition);

        return $rule;
    }

    /**
     * @return Ticket
     */
    private function getTarget()
    {
        return new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(13),
            self::SUBJECT,
            self::DESCRIPTION,
            $this->createBranch(),
            new User(1, User::TYPE_DIAMANTE),
            $this->createAssignee(),
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::NEW_ONE)
        );
    }

    /**
     * @return Branch
     */
    private function createBranch()
    {
        return new Branch('DUMM', 'DUMMY_NAME', 'DUMYY_DESC');
    }

    /**
     * @return OroUser
     */
    private function createAssignee()
    {
        return $this->createOroUser();
    }

    /**
     * @return OroUser
     */
    private function createOroUser()
    {
        return new OroUser();
    }

    /**
     * @return array
     */
    private function getEntities()
    {
        $entities = [
            'ticket'  => [
                'class' => 'Diamante\DeskBundle\Entity\Ticket'
            ],
            'comment' => [
                'class' => 'Diamante\DeskBundle\Entity\Comment'
            ]
        ];

        return $entities;
    }

    /**
     * @return array
     */
    private function getChangeset()
    {
        $ticketChangeset = [
            'uniqueId'       => [null, new UniqueId('unique_id')],
            'sequenceNumber' => [null, new TicketSequenceNumber(13)],
            'subject'        => [null, self::SUBJECT],
            'description'    => [null, self::DESCRIPTION],
            'branch'         => [null, $this->createBranch()],
            'reporter'       => [null, new User(1, User::TYPE_DIAMANTE)],
            'assignee'       => [null, $this->createAssignee()],
            'source'         => [null, new Source(Source::PHONE)],
            'priority'       => [null, new Priority(Priority::PRIORITY_LOW)],
            'status'         => [null, new Status(Status::NEW_ONE)]
        ];

        return $ticketChangeset;
    }
}