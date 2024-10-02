<?php

declare(strict_types=1);

namespace Fabpl\Preset\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

final class InstallCommand extends Command
{
    protected $signature = 'preset:install
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    protected $description = 'Install the preset';

    public function handle(): int
    {
        $this->info('Installing preset...');

        if ($this->hasComposerPackage('phpunit/phpunit')) {
            $this->removeComposerDevPackages(['phpunit/phpunit']);
        }

        if (! $this->requireComposerDevPackages([
            'larastan/larastan',
            'laravel/telescope',
            'pestphp/pest',
            'pestphp/pest-plugin-laravel',
            'rector/rector',
        ])) {
            return self::FAILURE;
        }

        $this->runCommands(['php artisan telescope:install']);

        $this->replaceInFile(
            'App\Providers\TelescopeServiceProvider::class,',
            '',
            base_path('bootstrap/providers.php')
        );

        $filesystem = new Filesystem;

        $filesystem->copyDirectory(__DIR__.'/../../../stubs', base_path());

        $filesystem->delete([
            base_path('tests/Unit/ExampleTest.php'),
            base_path('tests/Feature/ExampleTest.php'),
        ]);

        $configuration = json_decode(file_get_contents(base_path('composer.json')), associative: true);

        $configuration['scripts']['analyse'] = ['phpstan analyse --memory-limit=2G'];

        $configuration['scripts']['check'] = [
            'rector --dry-run',
            'pint --test',
            'phpstan analyse --memory-limit=2G',
            'pest --coverage --min=100',
        ];

        $configuration['scripts']['coverage'] = ['pest --coverage'];

        $configuration['scripts']['lint'] = ['pint'];

        $configuration['scripts']['refactor'] = ['rector'];

        $configuration['scripts']['test'] = ['pest'];

        file_put_contents(
            base_path('composer.json'),
            str(json_encode($configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                ->append(PHP_EOL)
                ->toString(),
        );

        $this->info('Preset has been installed!');

        return self::SUCCESS;
    }

    /**
     * Determine if the given Composer package is installed.
     */
    private function hasComposerPackage(string $package): bool
    {
        $packages = json_decode(file_get_contents(base_path('composer.json')), true);

        return array_key_exists($package, $packages['require'] ?? [])
            || array_key_exists($package, $packages['require-dev'] ?? []);
    }

    /**
     * Get the path to the appropriate PHP binary.
     */
    private function phpBinary(): string
    {
        return (new PhpExecutableFinder)->find(false) ?: 'php';
    }

    /**
     * Removes the given Composer Packages as "dev" dependencies.
     */
    private function removeComposerDevPackages(array $packages): bool
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = [$this->phpBinary(), $composer, 'remove', '--dev'];
        }

        $command = array_merge(
            $command ?? ['composer', 'remove', '--dev'],
            $packages
        );

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            }) === 0;
    }

    /**
     * Replace a given string within a given file.
     */
    private function replaceInFile(string $search, string $replace, string $path): void
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Install the given Composer Packages as "dev" dependencies.
     */
    private function requireComposerDevPackages(array $packages): bool
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = [$this->phpBinary(), $composer, 'require', '--dev'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require', '--dev'],
            $packages
        );

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            }) === 0;
    }

    /**
     * Run the given commands.
     */
    private function runCommands(array $commands): void
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });
    }
}
