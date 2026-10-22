<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Duplicate-proof detection (PRD v1.2 viral tasks): a SHA-256 of the uploaded screenshot and of the normalised proof URL,
 * plus a pointer to the earlier submission it duplicates. Additive; existing rows are back-filled where the file still exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_submissions', function (Blueprint $t) {
            if (!Schema::hasColumn('task_submissions', 'screenshot_hash')) {
                $t->char('screenshot_hash', 64)->nullable()->index();
            }
            if (!Schema::hasColumn('task_submissions', 'proof_url_hash')) {
                $t->char('proof_url_hash', 64)->nullable()->index();
            }
            if (!Schema::hasColumn('task_submissions', 'duplicate_of_id')) {
                $t->unsignedBigInteger('duplicate_of_id')->nullable();
            }
        });

        // back-fill what can be computed (the legacy rows are not flagged as duplicates retroactively)
        DB::table('task_submissions')->orderBy('id')->each(function ($row) {
            $update = [];
            try {
                if ($row->screenshot_path && Storage::disk('public')->exists($row->screenshot_path)) {
                    $update['screenshot_hash'] = hash_file('sha256', Storage::disk('public')->path($row->screenshot_path));
                }
            } catch (\Throwable $e) {
                // file unreadable: leave NULL
            }
            if ($row->proof_url) {
                $update['proof_url_hash'] = hash('sha256', self::normaliseUrl($row->proof_url));
            }
            if ($update) {
                DB::table('task_submissions')->where('id', $row->id)->update($update);
            }
        });
    }

    public static function normaliseUrl(string $url): string
    {
        $p = parse_url(trim($url));
        if (!$p || empty($p['host'])) {
            return strtolower(trim($url));
        }
        parse_str($p['query'] ?? '', $q);
        foreach (array_keys($q) as $k) {
            if (str_starts_with(strtolower((string) $k), 'utm_') || in_array(strtolower((string) $k), ['fbclid', 'gclid'], true)) {
                unset($q[$k]);
            }
        }
        ksort($q);

        return strtolower($p['scheme'] ?? 'https') . '://' . strtolower($p['host']) . (isset($p['port']) ? ':' . $p['port'] : '')
            . rtrim($p['path'] ?? '', '/') . ($q ? '?' . http_build_query($q) : '');
    }

    public function down(): void
    {
        Schema::table('task_submissions', function (Blueprint $t) {
            foreach (['duplicate_of_id', 'proof_url_hash', 'screenshot_hash'] as $c) {
                if (Schema::hasColumn('task_submissions', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
