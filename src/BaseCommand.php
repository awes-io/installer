<?php


namespace AwesIO\Installer\Console;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Symfony\Component\Process\Process;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class BaseCommand extends Command
{
    /**
     * PackageKit token
     *
     * @var string
     */
    protected $token;

    /**
     * PackageKit key
     *
     * @var string
     */
    protected $key;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://repo.pkgkit.com',
        ]);

        parent::__construct();
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

        if (!extension_loaded('zip')) {
            throw new RuntimeException('The Zip PHP extension is not installed. Please install it and try again.');
        }

        $directory = ($input->getArgument('name')) ? getcwd() . '/' . $input->getArgument('name') : getcwd();

        if (!$input->getOption('force')) {
            $this->verifyApplicationDoesntExist($directory);
        }

        $output->writeln('<info>Crafting application...</info>');

        $this->download($zipFile = $this->makeFilename())
            ->extract($zipFile, $directory)
            ->prepareWritableDirectories($directory, $output)
            ->cleanUp($zipFile);

        if (!$this->key = $input->getOption('key')) {
            $this->getKey();
        }

        if (!$this->token = $input->getOption('token')) {
            $this->getToken();
        }

        $this->setToken($directory);

        $composer = $this->findComposer();

        $commands = [
            $composer . ' install --no-scripts',
            $composer . ' run-script post-root-package-install',
            $composer . ' run-script post-create-project-cmd',
            $composer . ' run-script post-autoload-dump',
        ];

        if ($input->getOption('no-ansi')) {
            $commands = array_map(function ($value) {
                return $value . ' --no-ansi';
            }, $commands);
        }

        if ($input->getOption('quiet')) {
            $commands = array_map(function ($value) {
                return $value . ' --quiet';
            }, $commands);
        }

        $process = new Process(implode(' && ', $commands), $directory, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        $this->setKey($directory);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Do you want to support us by giving Github star? [Y/n]', true);
        if ($helper->ask($input, $output, $question)) {
            (new Process('composer thanks', $directory))->run();
        }

        $output->writeln('<comment>Application ready! Build something amazing.</comment>');
    }

    /**
     * Get PackageKit token
     */
    protected function getToken()
    {
        $helper = $this->getHelper('question');
        $question = new Question("Please enter your API-TOKEN from Package Kit \n");
        $this->token = $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Get PackageKit key
     */
    protected function getKey()
    {
        $helper = $this->getHelper('question');
        $question = new Question("Please enter your PKGKIT_CDN_KEY. You can get it for free on https://www.pkgkit.com/awes-io/create \n");
        $this->key = $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Check PackageKit composer token
     *
     * @return bool
     */
    protected function checkToken(): bool
    {
        if (empty($this->token)) {
            return false;
        }

        try {
            $response = $this->client->get('validate?token=' . $this->token);
            return $response->getStatusCode() == 200;
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() == 403) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Check PackageKit cdn key
     *
     * @return bool
     */
    protected function checkKey(): bool
    {
        if (empty($this->key)) {
            return false;
        }

        try {
            $response = $this->client->get('check?key=' . $this->key);
            return $response->getStatusCode() == 200;
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() == 403) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Set PackageKit token to composer.json
     *
     * @param string $directory
     */
    protected function setToken(string $directory)
    {
        while (!$this->checkToken()) {
            $this->output->writeln('<comment>Token is invalid.</comment>');
            $this->getToken();
        }

        $file = $directory . '/composer.json';
        $composer = file_get_contents($file);
        $composer = json_decode($composer, true);

        foreach ($composer['repositories'] as &$repository) {
            if ($repository['url'] === 'https://repo.pkgkit.com') {
                $repository['options']['http']['header'] = ['API-TOKEN: ' . $this->token];
            }
        }

        $composer = json_encode($composer, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);
        file_put_contents($file, $composer);
    }

    /**
     * Set PackageKit key to .env
     *
     * @param string $directory
     */
    protected function setKey(string $directory)
    {
        $var = 'PKGKIT_CDN_KEY';
        while (!$this->checkKey()) {
            $this->output->writeln('<comment>Key is invalid.</comment>');
            $this->getKey();
        }

        $file = $directory . '/.env';
        $env = file_get_contents($file);
        if (strpos($env, $var) == false) {
            $env = $env . "{$var}={$this->key}\n";
        } else {
            $val = explode($var, $env, 2)[1];
            $val = explode("\n", $val, 2)[0];
            $val = $var . $val;
            $env = str_replace($val, "{$var}={$this->key}", $env);
        }
        file_put_contents($file, $env);
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param string $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Generate a random temporary filename.
     *
     * @return string
     */
    protected function makeFilename()
    {
        return getcwd() . '/awes-io_' . md5(time() . uniqid()) . '.zip';
    }

    /**
     * Extract the Zip file into the given directory.
     *
     * @param string $zipFile
     * @param string $directory
     * @return $this
     */
    protected function extract(string $zipFile, string $directory): self
    {
        $tmpdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(time() . uniqid());

        $archive = new ZipArchive;

        $archive->open($zipFile);

        $archive->extractTo($tmpdir);

        $this->moveExtracted($tmpdir, $directory);

        $archive->close();

        return $this;
    }

    /**
     * Move extracted from temp dir to project dir
     *
     * @param string $directory
     */
    protected function moveExtracted(string $tmpdir, string $directory): void
    {
        $filesystem = new Filesystem;

        $subdir = array_diff(scandir($tmpdir), ['..', '.']);
        if (count($subdir) == 1) {
            $subdir = $tmpdir . DIRECTORY_SEPARATOR . array_shift($subdir);
        } else {
            $subdir = $tmpdir;
        }

        $filesystem->mirror($subdir, $directory, null, ['override' => true, 'delete' => false]);
        $filesystem->remove($tmpdir);
    }

    /**
     * Clean-up the Zip file.
     *
     * @param string $zipFile
     * @return $this
     */
    protected function cleanUp($zipFile): self
    {
        @chmod($zipFile, 0777);

        @unlink($zipFile);

        return $this;
    }

    /**
     * Make sure the storage and bootstrap cache directories are writable.
     *
     * @param string $appDirectory
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return $this
     */
    protected function prepareWritableDirectories($appDirectory, OutputInterface $output)
    {
        $filesystem = new Filesystem;

        try {
            $filesystem->chmod($appDirectory . DIRECTORY_SEPARATOR . "bootstrap/cache", 0755, 0000, true);
            $filesystem->chmod($appDirectory . DIRECTORY_SEPARATOR . "storage", 0755, 0000, true);
        } catch (IOExceptionInterface $e) {
            $output->writeln('<comment>You should verify that the "storage" and "bootstrap/cache" directories are writable.</comment>');
        }

        return $this;
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        $composerPath = getcwd() . '/composer.phar';

        if (file_exists($composerPath)) {
            return '"' . PHP_BINARY . '" ' . $composerPath;
        }

        return 'composer';
    }
}
