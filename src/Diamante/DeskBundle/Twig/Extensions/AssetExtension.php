<?php
/*
 * Copyright (c) 2017 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\DeskBundle\Twig\Extensions;

use Symfony\Bridge\Twig\Extension\AssetExtension as SymfonyAssetExtension;

/**
 * Class AssetExtension
 * @package Diamante\DistributionBundle\Twig\Extensions
 */
class AssetExtension extends SymfonyAssetExtension
{
    protected $pathMapper = ['js/routes.js' => 'js/routes'];

    /**
     * Returns the public url/path of an asset.
     *
     * If the package used to generate the path is an instance of
     * UrlPackage, you will always get a URL and not a path.
     *
     * @param string $path       A public path
     * @param null $packageName  The name of the asset package to use
     * @param bool $absolute
     * @param null $version
     *
     * @return string The public path of the asset
     */
    public function getAssetUrl($path, $packageName = null, $absolute = false, $version = null)
    {
        if (array_key_exists($path, $this->pathMapper)) {
            $path = $this->pathMapper[$path];
        }

        return parent::getAssetUrl($path, $packageName, $absolute, $version);
    }

    /**
     * @return string
     */
    public static function getClass()
    {
        return __CLASS__;
    }
}
