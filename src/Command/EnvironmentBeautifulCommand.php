<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Vigihdev\Encryption\Handler\EnvironmentBeautifulHandler;

#[AsCommand(
    name: 'env:beautiful',
    description: 'Format and organize environment variables with grouping and security masking'
)]
class EnvironmentBeautifulCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'env-path',
                InputArgument::REQUIRED,
                'Path to environment file (e.g.: .env, .env.ssh)',
                null,
                function () {
                    return ['.env', '.env.local', '.env.ssh'];
                }
            )
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command beautifies and organizes environment files by:

                    • <comment>Grouping variables by prefix</comment> (e.g., SIRENT_, OKKARENT_)
                    • <comment>Detecting encrypted values</comment> (Defuse Crypto pattern)
                    • <comment>Adding section headers</comment> for better readability
                    • <comment>Preserving comments and structure</comment>

                    <info>Example Usage:</info>

                    <comment># Beautify default .env file</comment>
                    <info>php %command.full_name% .env</info>

                    <comment># Beautify SSH-specific environment file</comment>  
                    <info>php %command.full_name% .env.ssh</info>

                    <info>Expected Result:</info>

                    Before:
                    <comment>SIRENT_SSH_HOST=host.com
                    OKKARENT_SSH_USER=user
                    SIRENT_SSH_PORT=22</comment>

                    After:
                    <comment># SIRENT
                    SIRENT_SSH_HOST=host.com
                    SIRENT_SSH_PORT=22

                    # OKKARENT  
                    OKKARENT_SSH_USER=user</comment>

                    <info>Security Features:</info>

                    • Encrypted values (starting with def50200...) are preserved
                    • File permissions and ownership are maintained
                    • Original file is backed up before modification

                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $envPath = $input->getArgument('env-path');
        $envFullPath = Path::join(getcwd(), $envPath);

        if (!is_file($envFullPath)) {
            $io->error([
                "Environment file '{$envPath}' not found.",
                "Expected path: {$envFullPath}",
                "Available files: " . implode(', ', glob('.*') ?: ['none'])
            ]);
            return Command::FAILURE;
        }

        $io->info("Starting beautification process for: {$envPath}");

        // Create backup
        $backupPath = $envFullPath . '.backup';
        if (!file_exists($backupPath)) {
            copy($envFullPath, $backupPath);
            $io->note("Backup created: " . basename($backupPath));
        }

        $beautiful = new EnvironmentBeautifulHandler(envPath: $envFullPath);
        $beautifiedContent = $beautiful->toBeautifulEnvironment();

        if ((bool) file_put_contents($envFullPath, $beautifiedContent)) {
            $io->success([
                "Environment file successfully beautified!",
                "Variables grouped by prefix with section headers",
                "Encrypted values preserved and detected",
                "Backup maintained at: " . basename($backupPath)
            ]);

            // Show preview
            $io->section("Preview of beautified content:");
            $io->text(array_slice(explode("\n", $beautifiedContent), 0, 10));
            if (substr_count($beautifiedContent, "\n") > 10) {
                $io->note("... and " . (substr_count($beautifiedContent, "\n") - 10) . " more lines");
            }

            return Command::SUCCESS;
        }

        $io->error("Failed to write beautified content to environment file.");
        return Command::FAILURE;
    }
}
