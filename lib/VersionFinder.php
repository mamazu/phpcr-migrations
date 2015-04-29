<?php

namespace DTL\PhpcrMigrations;

use Symfony\Component\Finder\Finder;

class VersionFinder
{
    private $paths;
    private $collection;

    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    public function getCollection()
    {
        if ($this->collection) {
            return $this->collection;
        }

        $versions = array();
        $finder = new Finder();
        $finder->name('/Version[0-9]{12}.php/');
        $finder->files();

        foreach ($this->paths as $path) {
            $finder->in($path);
        }

        foreach ($finder as $versionFile) {
            $className = $versionFile->getBasename('.php');
            require_once $versionFile->getRealPath();
            $classFqn = MigratorUtil::getClassNameFromFile($versionFile->getRealPath());

            $version = new $classFqn();

            if (!$version instanceof VersionInterface) {
                throw new MigratorException(sprintf(
                    'Version class "%s" must implement VersionInterface',
                    $className
                ));
            }

            $versionTimestamp = substr($versionFile->getBaseName(), 7, 12);
            $versions[$versionTimestamp] = $version;
        }

        $this->collection = new VersionCollection($versions);

        return $this->collection;
    }
}
