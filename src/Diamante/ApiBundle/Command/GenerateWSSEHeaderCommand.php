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
namespace Diamante\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateWSSEHeaderCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this->setName('diamante:wsse:generate-header');
        $this->setDescription('Generate X-WSSE HTTP header for a given Api User');
        $this->setDefinition(
            array(
                    new InputArgument('email', InputArgument::REQUIRED, 'User email'),
            )
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $user = $this
            ->getContainer()
            ->get('diamante.api.user.repository')
            ->findUserByEmail($email);

        if (null === $user) {
            throw new \InvalidArgumentException(sprintf('User "%s" does not exist', $email));
        }


        $created = date('c');
        // http://stackoverflow.com/questions/18117695/how-to-calculate-wsse-nonce
        $prefix = gethostname();
        $nonce  = base64_encode(substr(md5(uniqid($prefix . '_', true)), 0, 16));
        $secret = $user->getPassword();

        $passwordDigest = base64_encode(sha1(
            sprintf(
                '%s%s%s',
                base64_decode($nonce),
                $created,
                $secret
            ),
            true
        ));

        $output->writeln('<info>To use WSSE authentication add following headers to the request:</info>');
        $output->writeln('Authorization: WSSE profile="UsernameToken"');
        $output->writeln(
            sprintf(
                'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
                $email,
                $passwordDigest,
                $nonce,
                $created
            )
        );
        $output->writeln('');

        return 0;
    }
}
