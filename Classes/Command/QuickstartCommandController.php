<?php
namespace Queo\SiteKickstarter\Command;

/*
 * This file is part of the Neos.SiteKickstarter package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Reflection\ReflectionService;
use Neos\SiteKickstarter\Service\GeneratorService;
use Queo\SiteKickstarter\Service\AbstractSitePackageGeneratorService;
use Queo\SiteKickstarter\Service\AfxTemplateGeneratorService;
use Queo\SiteKickstarter\Service\FluidTemplateGeneratorService;

/**
 * Command controller for the Kickstart generator
 */
class QuickstartCommandController extends CommandController
{
    /**
     * @var PackageManager
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * Kickstart a new site package
     *
     * This command generates a new site package with basic Fusion and Sites.xml
     *
     * @param string $packageKey The packageKey for your site
     * @param string $siteName The siteName of your site
     * @return string
     */
    public function siteCommand($packageKey, $siteName)
    {
        if (!$this->packageManager->isPackageKeyValid($packageKey)) {
            $this->outputLine('Package key "%s" is not valid. Only UpperCamelCase in the format "Vendor.PackageKey", please!', [$packageKey]);
            $this->quit(1);
        }

        if ($this->packageManager->isPackageAvailable($packageKey)) {
            $this->outputLine('Package "%s" already exists.', [$packageKey]);
            $this->quit(1);
        }

        $generatorClasses = $this->reflectionService->getAllSubClassNamesForClass(AbstractSitePackageGeneratorService::class);

        $renderEngine = $this->output->select('What generator do you want to use?',
            $generatorClasses
        );

        $generatorService = $this->objectManager->get($renderEngine);

        $generatedFiles = $generatorService->generateSitePackage($packageKey, $siteName);
        $this->outputLine(implode(PHP_EOL, $generatedFiles));
    }
}
