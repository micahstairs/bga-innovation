<?php

namespace Unit;

use PHPUnit\Framework\TestCase;

/**
 * The alpha deploy (misc/bgaprojectrename.php) rewrites every backslash followed by "Innovation" to
 * a backslash followed by the alpha game name in the files it processes, but it leaves declarations
 * such as "namespace Innovation\Cards;" and "use Innovation\...;" alone. A leading-backslash
 * reference like \Innovation\Cards\Foo would therefore point at a namespace that does not exist on
 * alpha (this broke City cards, see https://boardgamearena.com/bug?id=231971). Only the game class
 * itself (backslash, Innovation, then no further backslash) may be referenced that way.
 */
class AlphaRenameTest extends TestCase
{
    public function testNoLeadingBackslashNamespaceReferencesInRenamedFiles()
    {
        $root = dirname(__DIR__, 2);
        $files = array_merge(
            glob("$root/*.php"),
            glob("$root/modules/Innovation/*.php"),
            glob("$root/modules/Innovation/Cards/*.php"),
            glob("$root/modules/Innovation/Utils/*.php")
        );
        $files = array_filter($files, function ($file) {
            return basename($file) !== '_ide_helper.php';
        });
        $this->assertNotEmpty($files);

        $offenders = [];
        foreach ($files as $file) {
            foreach (file($file) as $index => $line) {
                if (preg_match('/\\\\Innovation\\\\/i', $line)) {
                    $offenders[] = basename($file) . ':' . ($index + 1) . ': ' . trim($line);
                }
            }
        }
        $this->assertSame([], $offenders, "These references break after the alpha rename:\n" . implode("\n", $offenders));
    }
}
