<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LangCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:check {locales?* : Locales to compare with the fallback locale (default: zh)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List translation keys present in the fallback locale (en) but missing in other locales';

    public function handle(): int
    {
        $locales = $this->argument('locales') ?: ['zh'];
        $failed = false;

        foreach ($locales as $locale) {
            $missing = self::missingKeys($locale);
            $count = array_sum(array_map('count', $missing));

            if ($count === 0) {
                $this->info("{$locale}: no missing keys.");
                continue;
            }

            $failed = true;
            $this->error("{$locale}: {$count} missing key(s).");
            foreach ($missing as $file => $keys) {
                foreach ($keys as $key) {
                    $this->line("  {$file}.php  {$key}");
                }
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Keys in each fallback-locale lang file that the given locale lacks, as
     * [file => [dot.key, ...]]. A missing file lists all of its keys.
     */
    public static function missingKeys(string $locale): array
    {
        $fallback = config('app.fallback_locale', 'en');
        $missing = [];

        foreach (glob(resource_path("lang/{$fallback}/*.php")) as $fallbackFile) {
            $file = basename($fallbackFile, '.php');
            $localeFile = resource_path("lang/{$locale}/{$file}.php");

            $expected = self::flatten(require $fallbackFile);
            $actual = is_file($localeFile) ? self::flatten(require $localeFile) : [];

            $keys = array_keys(array_diff_key($expected, $actual));
            if ($keys !== []) {
                $missing[$file] = $keys;
            }
        }

        return $missing;
    }

    private static function flatten(array $lines, string $prefix = ''): array
    {
        $result = [];
        foreach ($lines as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            if (is_array($value) && $value !== []) {
                $result += self::flatten($value, $fullKey);
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }
}
