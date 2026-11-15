<?php

namespace Unit;

use PHPUnit\Framework\TestCase;

/**
 * The game class now extends \Bga\GameFramework\Table, so the retired global \Table class does not
 * exist in production. The test bootstrap aliases the two names to the same stub, which would hide
 * type-hints against the retired class (this broke alpha, see
 * https://boardgamearena.com/bug?id=256551), so guard with source inspection instead.
 *
 * Likewise, the new framework's loader scopes top-level JS variables, so format_block() can no
 * longer resolve templates by name; templates must be passed directly to format_string().
 */
class FrameworkMigrationTest extends TestCase
{
    public function testNoReferencesToRetiredGlobalTableClass()
    {
        $root = dirname(__DIR__, 2);
        $files = array_merge(
            glob("$root/*.php"),
            glob("$root/modules/Innovation/*.php"),
            glob("$root/modules/Innovation/Cards/*.php"),
            glob("$root/modules/Innovation/Cards/Base/*.php"),
            glob("$root/modules/Innovation/Utils/*.php")
        );
        $files = array_filter($files, function ($file) {
            return basename($file) !== '_ide_helper.php';
        });
        $this->assertNotEmpty($files);

        $offenders = [];
        foreach ($files as $file) {
            foreach (file($file) as $index => $line) {
                $trimmed = trim($line);
                if (str_starts_with($trimmed, '*') || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '/*')) {
                    continue;
                }
                if (preg_match('/(?<![A-Za-z\\\\])\\\\Table(?![A-Za-z])/', $line)) {
                    $offenders[] = basename($file) . ':' . ($index + 1) . ': ' . $trimmed;
                }
            }
        }
        $this->assertSame([], $offenders, "These references break under the new framework class hierarchy:\n" . implode("\n", $offenders));
    }

    public function testNoByNameTemplateCallsInGameJs()
    {
        $root = dirname(__DIR__, 2);
        $offenders = [];
        foreach (file("$root/innovation.js") as $index => $line) {
            if (preg_match("/format_block\\('/", $line)) {
                $offenders[] = 'innovation.js:' . ($index + 1) . ': ' . trim($line);
            }
        }
        $this->assertSame([], $offenders, "By-name template lookups break under the new framework JS loader; use format_string() with the template variable instead:\n" . implode("\n", $offenders));
    }
}
