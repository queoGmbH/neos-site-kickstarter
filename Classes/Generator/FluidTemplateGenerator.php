<?php

namespace Queo\SiteKickstarter\Generator;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManager;
use Neos\Utility\Files;
use Neos\ContentRepository\Domain\Repository\ContentDimensionRepository;
use Neos\ContentRepository\Utility;
use Queo\SiteKickstarter\Annotation as QSK;

/**
 * Service to generate site packages
 *
 * @QSK\SitePackageGenerator("Fluid Basic")
 */
class FluidTemplateGenerator extends AbstractSitePackageGenerator
{
    /**
     * @Flow\Inject
     * @var PackageManager
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var ContentDimensionRepository
     */
    protected $contentDimensionRepository;

    /**
     * Generate a site package and fill it with boilerplate data.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return array
     */
    public function generateSitePackage($packageKey, $siteName)
    {
        $this->packageManager->createPackage($packageKey, [
            'type' => 'neos-site',
            "require" => [
                "neos/neos" => "*",
                "neos/nodetypes" => "*"
            ],
            "suggest" => [
                "neos/seo" => "*"
            ]
        ]);

        $this->generateSitesXml($packageKey, $siteName);
        $this->generateSitesRootFusion($packageKey, $siteName);
        $this->generateSitesPageFusion($packageKey, $siteName);
        $this->generateDefaultTemplate($packageKey, $siteName);
        $this->generateNodeTypesConfiguration($packageKey);
        $this->generateAdditionalFolders($packageKey);

        return $this->generatedFiles;
    }

    /**
     * Generate a "Sites.xml" for the given package and name.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return void
     */
    protected function generateSitesXml($packageKey, $siteName)
    {
        $templatePathAndFilename = $this->getResourcePathForFile('Content/Sites.xml');

        $contextVariables = [
            'packageKey' => $packageKey,
            'siteName' => htmlspecialchars($siteName),
            'siteNodeName' => $this->generateSiteNodeName($packageKey),
            'dimensions' => $this->contentDimensionRepository->findAll()
        ];

        $fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

        $sitesXmlPathAndFilename = $this->packageManager->getPackage($packageKey)->getResourcesPath() . 'Private/Content/Sites.xml';
        $this->generateFile($sitesXmlPathAndFilename, $fileContent);
    }

    /**
     * Generate basic root Fusion file.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return void
     */
    protected function generateSitesRootFusion($packageKey, $siteName)
    {
        $templatePathAndFilename = $this->getResourcePathForFile('Fusion/Root.fusion');

        $contextVariables = [
            'packageKey' => $packageKey,
            'siteName' => $siteName,
            'siteNodeName' => $this->generateSiteNodeName($packageKey)
        ];

        $fileContent = $this->renderSimpleTemplate($templatePathAndFilename, $contextVariables);

        $sitesRootFusionPathAndFilename = $this->packageManager->getPackage($packageKey)->getResourcesPath() . 'Private/Fusion/Root.fusion';
        $this->generateFile($sitesRootFusionPathAndFilename, $fileContent);
    }

    /**
     * Generate basic Fusion documentNode file.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return void
     */
    protected function generateSitesPageFusion($packageKey, $siteName)
    {

        $templatePathAndFilename = $this->getResourcePathForFile('Fusion/NodeTypes/Pages/Page.fusion');

        $contextVariables = [];
        $contextVariables['packageKey'] = $packageKey;
        $contextVariables['siteName'] = $siteName;
        $packageKeyDomainPart = substr(strrchr($packageKey, '.'), 1) ?: $packageKey;
        $contextVariables['siteNodeName'] = $packageKeyDomainPart;

        $fileContent = $this->renderSimpleTemplate($templatePathAndFilename, $contextVariables);

        $sitesPageFusionPathAndFilename = $this->packageManager->getPackage($packageKey)->getResourcesPath() . 'Private/Fusion/NodeTypes/Page.fusion';
        $this->generateFile($sitesPageFusionPathAndFilename, $fileContent);
    }

    /**
     * Generate basic template file.
     *
     * @param string $packageKey
     * @param string $siteName
     * @return void
     */
    protected function generateDefaultTemplate($packageKey, $siteName)
    {
        $templatePathAndFilename = $this->getResourcePathForFile('Template/SiteTemplate.html');

        $contextVariables = [
            'siteName' => $siteName,
            'neosViewHelper' => '{namespace neos=Neos\Neos\ViewHelpers}',
            'fusionViewHelper' => '{namespace fusion=Neos\Fusion\ViewHelpers}',
            'siteNodeName' => $this->generateSiteNodeName($packageKey)
        ];

        $fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

        $defaultTemplatePathAndFilename = $this->packageManager->getPackage($packageKey)->getResourcesPath() . 'Private/Templates/Page/Default.html';
        $this->generateFile($defaultTemplatePathAndFilename, $fileContent);
    }

    /**
     * Generate site node name based on the given package key
     *
     * @param string $packageKey
     * @return string
     */
    protected function generateSiteNodeName($packageKey)
    {
        return Utility::renderValidNodeName($packageKey);
    }

    /**
     * Generate a example NodeTypes.yaml
     *
     * @param string $packageKey
     * @throws \Neos\FluidAdaptor\Core\Exception
     */
    protected function generateNodeTypesConfiguration($packageKey)
    {
        $templatePathAndFilename = $this->getResourcePathForFile('Configuration/NodeTypes.Document.Page.yaml');

        $contextVariables = [
            'packageKey' => $packageKey
        ];

        $fileContent = $this->renderSimpleTemplate($templatePathAndFilename, $contextVariables);

        $sitesNodeTypesPathAndFilename = $this->packageManager->getPackage($packageKey)->getConfigurationPath() . 'NodeTypes.Document.Page.yaml';
        $this->generateFile($sitesNodeTypesPathAndFilename, $fileContent);
    }

    /**
     * Generate additional folders for site packages.
     *
     * @param string $packageKey
     */
    protected function generateAdditionalFolders($packageKey)
    {
        $resourcesPath = $this->packageManager->getPackage($packageKey)->getResourcesPath();
        $publicResourcesPath = Files::concatenatePaths([$resourcesPath, 'Public']);

        foreach (['Images', 'JavaScript', 'Styles'] as $publicResourceFolder) {
            Files::createDirectoryRecursively(Files::concatenatePaths([$publicResourcesPath, $publicResourceFolder]));
        }
    }

    /**
     * Simplified template rendering
     *
     * @param string $templatePathAndFilename
     * @param array $contextVariables
     * @return string
     */
    protected function renderSimpleTemplate($templatePathAndFilename, array $contextVariables)
    {
        $content = file_get_contents($templatePathAndFilename);
        foreach ($contextVariables as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }
        return $content;
    }

    /**
     * returns resource path for the generator
     *
     * @param $pathToFile
     * @return string
     */
    protected function getResourcePathForFile($pathToFile)
    {
        return 'resource://Queo.SiteKickstarter/Private/FluidGenerator/' . $pathToFile;
    }
}
