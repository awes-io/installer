<?php


namespace AwesIO\Installer\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KeyCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('key')
            ->setDescription('Add PackageKit CDN key to .env')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Adds path to .env dir')
            ->addOption('key', 'k', InputOption::VALUE_OPTIONAL, 'Adds PackageKit CDN key to .env');
    }

    /**
     * Execute the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $directory = $input->getOption('path');
        if (empty($directory)) {
            $directory = getcwd();
        } elseif (substr($directory, 0, 1) !== '/') {
            $directory = getcwd() . '/' . $directory;
        }

        $output->writeln('<info>Writing PackageKit cdn key to .env...</info>');

        if (!$this->key = $input->getOption('key')) {
            $this->getKey();
        }

        $this->setKey($directory);

        $output->writeln('<comment>PackageKit cdn key is set.</comment>');
    }
}
