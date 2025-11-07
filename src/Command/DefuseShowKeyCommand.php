<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Command;

use Defuse\Crypto\Key;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'defuse:show-key',
    description: 'Tampilkan kunci Defuse Crypto dari file'
)]
class DefuseShowKeyCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'key-file',
                InputArgument::REQUIRED,
                'Path file kunci (contoh: ~/.config/encryption.key)'
            )
            ->setHelp(
                <<<'HELP'
                Command ini menampilkan kunci Defuse Crypto dari file.
                
                Contoh penggunaan:
                   php bin/console defuse:show-key ~/.config/encryption.key
                   
                PERINGATAN:
                - Hanya gunakan di environment yang aman
                - Jangan tampilkan kunci di public space
                HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $keyFile = $input->getArgument('key-file');

        // Expand tilde
        $keyFile = $this->expandTilde($keyFile);

        if (!file_exists($keyFile)) {
            $io->error("File kunci tidak ditemukan: {$keyFile}");
            return Command::FAILURE;
        }

        try {
            $keyString = file_get_contents($keyFile);
            if ($keyString === false) {
                $io->error("Gagal membaca file kunci: {$keyFile}");
                return Command::FAILURE;
            }

            // Validate key format
            Key::loadFromAsciiSafeString($keyString);

            $io->success("Kunci valid dari file: {$keyFile}");
            $io->writeln("<info>{$keyString}</info>");

            $io->note("Hati-hati! Jangan expose kunci ini di public space.");
        } catch (\Throwable $e) {
            $io->error("Kunci tidak valid: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function expandTilde(string $path): string
    {
        if (str_starts_with($path, '~/')) {
            $home = getenv('HOME') ?: ($_SERVER['HOME'] ?? null);
            if ($home) {
                return $home . substr($path, 1);
            }
        }
        return $path;
    }
}
