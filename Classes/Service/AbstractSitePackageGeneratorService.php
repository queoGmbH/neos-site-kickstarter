<?php

namespace Queo\SiteKickstarter\Service;

abstract class AbstractSitePackageGeneratorService extends \Neos\Kickstarter\Service\GeneratorService
{
    public abstract function generateSitePackage($packageKey, $siteName);
}
