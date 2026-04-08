<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * CsvParserService
 *
 * Handles all Ahrefs CSV parsing operations:
 * - Auto-detect columns
 * - Preview rows
 * - Full parse with type casting
 * - Handle UTF-8 BOM, delimiter detection
 */
class CsvParserService
{
    /** Expected Ahrefs column names (internal key => CSV header) */
    private array $expectedColumns;

    public function __construct()
    {
        $this->expectedColumns = config('rankreport.csv_ahrefs_columns', []);
    }

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Store uploaded CSV to disk (always as UTF-8), return stored path.
     */
    public function storeUpload(UploadedFile $file, int $projectId): string
    {
        $dir      = config('rankreport.storage.csv_path') . '/' . $projectId;
        $filename = now()->format('Y-m-d_His') . '_' . Str::uuid() . '.csv';

        // Read raw content, convert to UTF-8 if needed, then store
        $rawContent = file_get_contents($file->getRealPath());
        $utf8Content = $this->convertToUtf8($rawContent);

        $storagePath = $dir . '/' . $filename;
        \Illuminate\Support\Facades\Storage::put($storagePath, $utf8Content);

        return $storagePath;
    }

    /**
     * Read header row and return detected + auto-mapped columns.
     *
     * @return array{headers: string[], auto_map: array<string,string>}
     */
    public function detectColumns(string $storagePath): array
    {
        $headers = $this->readHeaders($storagePath);

        // Build auto-map: internal_key => matched_csv_header
        $autoMap = [];
        foreach ($this->expectedColumns as $internalKey => $expectedHeader) {
            $match = $this->fuzzyMatch($expectedHeader, $headers);
            if ($match !== null) {
                $autoMap[$internalKey] = $match;
            }
        }

        return [
            'headers'  => $headers,
            'auto_map' => $autoMap,
        ];
    }

    /**
     * Parse first N rows for preview.
     *
     * @return array{headers: string[], rows: array[]}
     */
    public function previewRows(string $storagePath, array $columnMap, int $limit = 20): array
    {
        return $this->doParse($storagePath, $columnMap, $limit);
    }

    /**
     * Full parse — all rows.
     *
     * @return array{headers: string[], rows: array[], skipped: int, total: int}
     */
    public function parse(string $storagePath, array $columnMap): array
    {
        return $this->doParse($storagePath, $columnMap, PHP_INT_MAX);
    }

    /**
     * Count valid rows in a file without loading them all into memory.
     */
    public function countRows(string $storagePath, array $columnMap): int
    {
        $result = $this->doParse($storagePath, $columnMap, PHP_INT_MAX);
        return count($result['rows']);
    }

    // ─── Internal Helpers ─────────────────────────────────────────────────────

    private function doParse(string $storagePath, array $columnMap, int $limit): array
    {
        $absolutePath = storage_path('app/' . $storagePath);

        if (!file_exists($absolutePath)) {
            throw new \RuntimeException("CSV file not found: {$storagePath}");
        }

        // Convert UTF-16 → UTF-8 temp file if needed
        $utf8Path  = $this->ensureUtf8File($absolutePath);
        $delimiter = $this->detectDelimiter($utf8Path);

        $file = new \SplFileObject($utf8Path, 'r');
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($delimiter);

        $rows    = [];
        $skipped = 0;
        $total   = 0;
        $headers = null;

        foreach ($file as $lineIndex => $row) {
            // SplFileObject có thể trả về false hoặc [false] ở EOF / dòng trống
            if ($row === false || !is_array($row) || $row === [false]) {
                continue;
            }

            if ($lineIndex === 0) {
                // Header row — strip BOM, normalize
                $headers = array_map(fn($h) => $this->stripBom(trim($h)), $row);
                continue;
            }

            if ($headers === null || count($row) < 2) {
                continue;
            }

            $total++;

            if (count($rows) >= $limit) {
                continue; // Keep counting total but stop collecting
            }

            // Map row to internal keys
            $mapped = $this->mapRow($row, $headers, $columnMap);

            if ($mapped === null) {
                $skipped++;
                continue;
            }

            $rows[] = $mapped;
        }

        // Cleanup tmp file if created
        $this->cleanupTmpFile($utf8Path, $absolutePath);

        return [
            'headers' => $headers ?? [],
            'rows'    => $rows,
            'skipped' => $skipped,
            'total'   => $total,
        ];
    }

    /**
     * Map a single CSV row to internal key structure.
     * Returns null if the row is invalid (no keyword).
     */
    private function mapRow(array $row, array $headers, array $columnMap): ?array
    {
        // Build header→index lookup
        $headerIndex = array_flip($headers);

        $get = function (string $internalKey) use ($row, $headers, $headerIndex, $columnMap): mixed {
            $csvHeader = $columnMap[$internalKey] ?? null;
            if (!$csvHeader) return null;
            $idx = $headerIndex[$csvHeader] ?? null;
            if ($idx === null) return null;
            $val = $row[$idx] ?? null;
            return (string) $val === '' ? null : trim((string) $val);
        };

        // Keyword is required
        $keyword = $get('keyword');
        if (empty($keyword)) return null;

        // current_position: '-' or empty = null (not ranked)
        $posRaw = $get('current_position');
        $currentPosition = null;
        if ($posRaw !== null && $posRaw !== '-' && is_numeric($posRaw)) {
            $pos = (int) $posRaw;
            $currentPosition = ($pos >= 1 && $pos <= 1000) ? $pos : null;
        }

        // volume: strip commas, cast to int
        $volumeRaw = $get('volume');
        $searchVolume = 0;
        if ($volumeRaw !== null) {
            $searchVolume = (int) str_replace([',', ' '], '', $volumeRaw);
        }

        // Branded: '1', 'true', 'TRUE' → true
        $brandedRaw = $get('branded');
        $brandFlag = in_array(strtolower((string) $brandedRaw), ['1', 'true', 'yes'], true);

        return [
            'keyword'          => $keyword,
            'normalized_keyword' => \App\Models\Keyword::normalize($keyword),
            'current_position' => $currentPosition,
            'search_volume'    => $searchVolume,
            'target_url'       => $get('current_url'),
            'location'         => $get('location'),
            'country_code'     => $get('country_code'),
            'brand_flag'       => $brandFlag,
            'entities'         => $get('entities'),
            'kd'               => $get('kd'),
            'cpc'              => $get('cpc'),
            'organic_traffic'  => $get('organic_traffic'),
            'serp_features'    => $get('serp_features'),
            'informational'    => $get('informational'),
            'commercial'       => $get('commercial'),
            'navigational'     => $get('navigational'),
            'transactional'    => $get('transactional'),
            '_raw'             => $this->rowToAssoc($row, $headers), // full row for raw_data_json
        ];
    }

    private function readHeaders(string $storagePath): array
    {
        $absolutePath = storage_path('app/' . $storagePath);
        $utf8Path     = $this->ensureUtf8File($absolutePath);
        $delimiter    = $this->detectDelimiter($utf8Path);

        $file = new \SplFileObject($utf8Path, 'r');
        $file->setFlags(\SplFileObject::READ_CSV);
        $file->setCsvControl($delimiter);

        $firstLine = $file->current();

        $this->cleanupTmpFile($utf8Path, $absolutePath);

        if (!$firstLine) return [];

        return array_map(fn($h) => $this->stripBom(trim($h)), $firstLine);
    }

    private function detectDelimiter(string $absolutePath): string
    {
        $firstLine = '';
        $fp = fopen($absolutePath, 'r');
        if ($fp) {
            $firstLine = fgets($fp, 4096) ?: '';
            fclose($fp);
        }

        $commaCount = substr_count($firstLine, ',');
        $tabCount   = substr_count($firstLine, "\t");

        return $tabCount > $commaCount ? "\t" : ',';
    }

    private function stripBom(string $str): string
    {
        // Strip UTF-8 BOM
        return ltrim($str, "\xEF\xBB\xBF");
    }

    /**
     * If the file is UTF-16 (LE or BE), convert it to a UTF-8 temp file and
     * return that temp path. Otherwise return the original path unchanged.
     */
    private function ensureUtf8File(string $absolutePath): string
    {
        $bom = file_get_contents($absolutePath, false, null, 0, 3);

        // UTF-16 LE BOM: FF FE
        if (str_starts_with($bom, "\xFF\xFE")) {
            $content = file_get_contents($absolutePath);
            $utf8    = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16LE');
            return $this->writeTmpFile($utf8);
        }

        // UTF-16 BE BOM: FE FF
        if (str_starts_with($bom, "\xFE\xFF")) {
            $content = file_get_contents($absolutePath);
            $utf8    = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16BE');
            return $this->writeTmpFile($utf8);
        }

        return $absolutePath;
    }

    /**
     * Convert raw file content to UTF-8 string (for storeUpload).
     */
    private function convertToUtf8(string $content): string
    {
        // UTF-16 LE BOM: FF FE
        if (str_starts_with($content, "\xFF\xFE")) {
            return mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16LE');
        }

        // UTF-16 BE BOM: FE FF
        if (str_starts_with($content, "\xFE\xFF")) {
            return mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16BE');
        }

        return $content;
    }

    private function writeTmpFile(string $content): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'csv_utf8_');
        file_put_contents($tmp, $content);
        return $tmp;
    }

    private function cleanupTmpFile(string $pathUsed, string $originalPath): void
    {
        if ($pathUsed !== $originalPath && file_exists($pathUsed)) {
            @unlink($pathUsed);
        }
    }

    /**
     * Fuzzy-match expected header against actual CSV headers.
     * Returns the matching header string or null.
     */
    private function fuzzyMatch(string $expected, array $actualHeaders): ?string
    {
        // Exact match first
        if (in_array($expected, $actualHeaders, true)) {
            return $expected;
        }

        // Case-insensitive match
        foreach ($actualHeaders as $h) {
            if (strcasecmp($h, $expected) === 0) {
                return $h;
            }
        }

        // Similarity match (threshold 75%)
        $best  = null;
        $bestScore = 0;
        foreach ($actualHeaders as $h) {
            similar_text($expected, $h, $pct);
            if ($pct > 75 && $pct > $bestScore) {
                $bestScore = $pct;
                $best = $h;
            }
        }

        return $best;
    }

    private function rowToAssoc(array $row, array $headers): array
    {
        $assoc = [];
        foreach ($headers as $i => $header) {
            $assoc[$header] = $row[$i] ?? null;
        }
        return $assoc;
    }
}
