<?php


namespace AwesIO\Installer\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TokenCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('token')
            ->setDescription('Add PackageKit token to composer.json')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Adds path to composer.json dir')
            ->addOption('token', 't', InputOption::VALUE_OPTIONAL, 'Adds PackageKit token to composer.json');
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

        $output->writeln('<info>Writing PackageKit token to composer.json...</info>');

        if (!$this->token = $input->getOption('token')) {
            $this->getToken();
        }

        $this->setToken($directory);

        $output->writeln('<comment>PackageKit token is set.</comment>');
    }
}
