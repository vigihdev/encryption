<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Path;
use Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract;

#[AsCommand(
    name: 'env:decrypt',
    description: 'Mendekripsi satu atau lebih variabel di dalam file .env'
)]
class EnvironmentDecryptorCommand extends Command
{
    private const ENCRYPTED_PREFIX = 'def50200';

    public function __construct(
        private readonly EnvironmentEncryptorServiceContract $service
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'env-path',
                InputArgument::REQUIRED,
                'Path menuju file .env (contoh: .env)',
                null,
                function () {
                    return ['.env'];
                }
            )
            ->addArgument(
                'env-keys',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Satu atau lebih kunci environment yang akan didekripsi (kosongkan untuk semua variabel)',
                null,
                function () {
                    $dotEnvVars = getenv('SYMFONY_DOTENV_VARS') ? explode(',', getenv('SYMFONY_DOTENV_VARS')) : [];
                    return $dotEnvVars;
                }

            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Simpan hasil dekripsi ke file lain (contoh: .env.decrypted)'
            )
            ->addOption(
                'show',
                's',
                InputOption::VALUE_NONE,
                'Tampilkan nilai yang didekripsi di terminal'
            )
            ->setHelp(
                <<<'HELP'
                Command ini memungkinkan Anda untuk mendekripsi nilai dari variabel environment yang terenkripsi di dalam file .env.
                
                Contoh penggunaan:
                
                1. Mendekripsi satu variabel:
                   php bin/console env:decrypt .env APP_KEY
                   
                2. Mendekripsi beberapa variabel sekaligus:
                   php bin/console env:decrypt .env APP_KEY DB_PASSWORD API_SECRET
                   
                3. Mendekripsi semua variabel dan tampilkan:
                   php bin/console env:decrypt .env --show
                   
                4. Mendekripsi semua variabel dan simpan ke file baru:
                   php bin/console env:decrypt .env --output=.env.backup
                   
                5. Tampilkan informasi detail (gunakan -v):
                   php bin/console env:decrypt .env -v
                HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $envPath = $input->getArgument('env-path');
        $envKeys = $input->getArgument('env-keys');
        $outputFile = $input->getOption('output');
        $showOutput = $input->getOption('show');

        // Pakai OutputInterface untuk cek verbose mode
        $isVerbose = $output->isVerbose();

        $envFullPath = Path::join(getcwd(), $envPath);

        if (! is_file($envFullPath)) {
            $io->error("File environment '{$envPath}' tidak ditemukan di path: {$envFullPath}");
            return Command::FAILURE;
        }

        $io->info("Mulai proses dekripsi untuk file: {$envPath}...");

        try {
            // Baca file .env
            $envContent = file_get_contents($envFullPath);
            if ($envContent === false) {
                $io->error("Gagal membaca file: {$envFullPath}");
                return Command::FAILURE;
            }

            $envItems = (new Dotenv())->parse($envContent);

            // Filter variabel yang akan didekripsi
            if (!empty($envKeys)) {
                $envItems = array_filter(
                    $envItems,
                    fn($key) => in_array($key, $envKeys),
                    ARRAY_FILTER_USE_KEY
                );

                if (empty($envItems)) {
                    $io->warning("Tidak ada variabel yang ditemukan dengan kunci: " . implode(', ', $envKeys));
                    return Command::SUCCESS;
                }
            }

            // Filter hanya variabel yang terenkripsi
            $encryptedItems = array_filter(
                $envItems,
                fn($value) => $this->isEncrypted($value),
                ARRAY_FILTER_USE_BOTH
            );

            if (empty($encryptedItems)) {
                $io->warning("Tidak ada variabel terenkripsi yang ditemukan.");
                return Command::SUCCESS;
            }

            if ($isVerbose) {
                $io->section('Informasi Dekripsi:');
                $io->writeln("Total variabel ditemukan: " . count($envItems));
                $io->writeln("Variabel terenkripsi: " . count($encryptedItems));
                $io->newLine();
            }

            // Proses dekripsi
            $decryptedResults = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($encryptedItems as $key => $ciphertext) {
                try {
                    $decryptedValue = $this->service->decrypt($ciphertext);
                    $decryptedResults[$key] = $decryptedValue;
                    $successCount++;

                    if ($showOutput || $isVerbose) {
                        if ($isVerbose) {
                            $io->writeln("✅ <info>{$key}</info>");
                            $io->writeln("   Encrypted: <comment>" . substr($ciphertext, 0, 50) . "...</comment>");
                            $io->writeln("   Decrypted: <info>{$decryptedValue}</info>");
                            $io->newLine();
                        } else {
                            $io->writeln("<info>{$decryptedValue}</info>");
                        }
                    }
                } catch (\Throwable $e) {
                    $errorCount++;
                    $decryptedResults[$key] = null;

                    if ($isVerbose) {
                        $io->writeln("❌ <error>{$key}</error>");
                        $io->writeln("   Error: <comment>{$e->getMessage()}</comment>");
                        $io->newLine();
                    }
                }
            }

            // Simpan ke file output jika diminta
            if ($outputFile) {
                $this->saveDecryptedFile($envContent, $decryptedResults, $outputFile, $io);
            }

            // Summary
            if ($isVerbose) {
                $io->section('Summary:');
                $io->writeln("✅ Berhasil didekripsi: <info>{$successCount}</info> variabel");
                if ($errorCount > 0) {
                    $io->writeln("❌ Gagal didekripsi: <error>{$errorCount}</error> variabel");
                }
            }

            if ($successCount > 0) {
                $io->success("Proses dekripsi selesai. {$successCount} variabel berhasil didekripsi.");
            } else {
                $io->warning("Tidak ada variabel yang berhasil didekripsi.");
            }
        } catch (\Throwable $e) {
            $io->error("Terjadi kesalahan: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Simpan hasil dekripsi ke file
     */
    private function saveDecryptedFile(string $originalContent, array $decryptedResults, string $outputFile, SymfonyStyle $io): void
    {
        $outputFullPath = Path::join(getcwd(), $outputFile);

        // Replace encrypted values dengan decrypted values
        $decryptedContent = $originalContent;
        foreach ($decryptedResults as $key => $decryptedValue) {
            if ($decryptedValue !== null) {
                // Cari line yang mengandung key dan replace value-nya
                $pattern = '/^(' . preg_quote($key, '/') . '=)(.*)$/m';
                $replacement = '${1}' . $decryptedValue;
                $decryptedContent = preg_replace($pattern, $replacement, $decryptedContent);
            }
        }

        if ((bool) file_put_contents($outputFullPath, $decryptedContent) === false) {
            $io->error("Gagal menyimpan file: {$outputFile}");
            return;
        }

        $io->success("File terdekripsi disimpan di: {$outputFile}");
    }

    /**
     * Memeriksa apakah sebuah value sudah dienkripsi.
     */
    private function isEncrypted(string $value): bool
    {
        return str_starts_with($value, self::ENCRYPTED_PREFIX);
    }
}
