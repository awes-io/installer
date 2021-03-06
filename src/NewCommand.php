<?php

namespace AwesIO\Installer\Console;

use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class NewCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new AwesIO application')
            ->addArgument('name', InputArgument::OPTIONAL)
            ->addOption('key', 'k', InputOption::VALUE_OPTIONAL, 'Adds PackageKit CDN key to .env')
            ->addOption('token', 't', InputOption::VALUE_OPTIONAL, 'Adds PackageKit token to composer.json')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
    }

    /**
     * Download the temporary Zip to the given file.
     *
     * @param string $zipFile
     * @return $this
     */
    protected function download($zipFile)
    {
        $response = (new Client)->get('https://github.com/awes-io/awes-io/archive/master.zip');

        file_put_contents($zipFile, $response->getBody());

        return $this;
    }
}
