<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Command;

use Defuse\Crypto\Key;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;

#[AsCommand(
    name: 'defuse:generate-key',
    description: 'Generate Defuse Crypto encryption key dan simpan ke file'
)]
class DefuseKeyGeneratorCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'out-file',
                InputArgument::REQUIRED,
                'Path file output untuk menyimpan kunci (contoh: ~/.config/encryption.key)'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force overwrite file jika sudah ada'
            )
            ->addOption(
                'show',
                's',
                InputOption::VALUE_NONE,
                'Tampilkan kunci di terminal juga'
            )
            ->addOption(
                'permissions',
                'm',
                InputOption::VALUE_REQUIRED,
                'Permissions untuk file kunci (contoh: 0600)',
                '0600'
            )
            ->setHelp(
                <<<'HELP'
                Command ini menghasilkan kunci enkripsi Defuse Crypto yang aman dan menyimpannya ke file.
                
                Contoh penggunaan:
                
                1. Generate kunci dan simpan ke file:
                   php bin/console defuse:generate-key ~/.config/myapp/encryption.key
                   
                2. Generate kunci, overwrite file yang ada:
                   php bin/console defuse:generate-key ~/.config/myapp/encryption.key --force
                   
                3. Generate kunci dan tampilkan di terminal juga:
                   php bin/console defuse:generate-key ~/.config/myapp/encryption.key --show
                   
                4. Generate kunci dengan permissions khusus:
                   php bin/console defuse:generate-key ~/.config/myapp/encryption.key --permissions=0400
                   
                KEAMANAN:
                - Simpan file kunci di lokasi yang aman
                - Set permissions yang ketat (600 atau 400)
                - Jangan commit kunci ke version control!
                - Backup kunci dengan aman
                HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $outFile = $input->getArgument('out-file');
        $force = $input->getOption('force');
        $showKey = $input->getOption('show');
        $permissions = $input->getOption('permissions');

        // Expand tilde (~) to home directory
        $outFile = $this->expandTilde($outFile);
        $directory = Path::getDirectory($outFile);

        $io->info("Generating Defuse Crypto key...");

        // Validasi directory
        if (!is_dir($directory)) {
            $io->error("Directory tidak ditemukan: {$directory}");
            $io->note("Buat directory terlebih dahulu: mkdir -p " . escapeshellarg($directory));
            return Command::FAILURE;
        }

        if (!is_writable($directory)) {
            $io->error("Directory tidak writable: {$directory}");
            return Command::FAILURE;
        }

        // Validasi file existence
        if (file_exists($outFile) && !$force) {
            $io->error("File sudah ada: {$outFile}");
            $io->note("Gunakan option --force untuk overwrite file yang ada.");
            return Command::FAILURE;
        }

        try {
            // Generate key
            $key = Key::createNewRandomKey();
            $keyString = $key->saveToAsciiSafeString();

            // Save to file
            if (file_put_contents($outFile, $keyString) === false) {
                $io->error("Gagal menyimpan kunci ke file: {$outFile}");
                return Command::FAILURE;
            }

            // Set permissions
            if ($permissions && !chmod($outFile, octdec($permissions))) {
                $io->warning("Gagal set permissions {$permissions} untuk file: {$outFile}");
            }

            $io->success("Kunci berhasil disimpan di: {$outFile}");

            // Show key if requested
            if ($showKey) {
                $io->section('Kunci Encryption:');
                $io->writeln("<info>{$keyString}</info>");
                $io->newLine();
            }

            // Security notes
            $io->note([
                "File permissions: " . substr(sprintf('%o', fileperms($outFile)), -4),
                "Jangan share kunci ini dengan siapapun!",
                "Backup kunci di lokasi yang aman.",
                "Gunakan kunci ini dengan env:encrypt command."
            ]);
        } catch (\Throwable $e) {
            $io->error("Gagal generate kunci: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Expand tilde (~) to home directory path
     */
    private function expandTilde(string $path): string
    {
        if (str_starts_with($path, '~/')) {
            $home = getenv('HOME') ?: getenv('USERPROFILE') ?: ($_SERVER['HOME'] ?? null);
            if ($home) {
                return $home . substr($path, 1);
            }
        }

        return $path;
    }
}
