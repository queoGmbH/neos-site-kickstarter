<?php

namespace Queo\SiteKickstarter\Generator;

abstract class AbstractSitePackageGenerator extends \Neos\Kickstarter\Service\GeneratorService
{
    public abstract function generateSitePackage($packageKey, $siteName);
}
