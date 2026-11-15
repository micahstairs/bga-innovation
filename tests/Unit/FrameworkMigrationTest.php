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

    public function testNoFormatBlockCallsOnTemplates()
    {
        // format_block() evals its first argument, so it breaks under the new framework JS loader
        // whether the template is passed by name (ReferenceError: scoped variables) or by value
        // (SyntaxError: eval of raw HTML). Templates must go through format_string() instead.
        $root = dirname(__DIR__, 2);
        $offenders = [];
        foreach (['innovation.js', 'src/innovation.ts'] as $file) {
            foreach (file("$root/$file") as $index => $line) {
                if (preg_match("/format_block\\(\\s*['\"]?jstpl/", $line)) {
                    $offenders[] = $file . ':' . ($index + 1) . ': ' . trim($line);
                }
            }
        }
        $this->assertSame([], $offenders, "format_block() on templates breaks under the new framework JS loader; use format_string() instead:\n" . implode("\n", $offenders));
    }

    public function testWireActionNamesDeclaredInStates()
    {
        // The new framework's performAction() validates the wire action name against the current
        // state's possibleactions client-side and silently rejects mismatches (this broke alpha, see
        // https://boardgamearena.com/bug?id=256606), so every wire action name the JS sends with
        // client-side validation enabled must be declared in the state(s) where it is used.
        $root = dirname(__DIR__, 2);

        // Collect the wire action names sent with client-side validation enabled.
        $wireActions = [];
        foreach (['src/innovation.ts', 'innovation.js'] as $file) {
            $source = file_get_contents("$root/$file");
            preg_match_all('/performServerAction\(\s*"([a-zA-Z_]+)"([\s\S]*?)\);/', $source, $calls, PREG_SET_ORDER);
            foreach ($calls as $call) {
                // Calls passing validateAction=false (debug and view-preference actions) are exempt.
                if (!preg_match('/,\s*false\s*$/', $call[2])) {
                    $wireActions[$call[1]] = true;
                }
            }
        }
        $this->assertNotEmpty($wireActions);

        // Parse each state's possibleactions from states.inc.php. Slice per-state blocks first so a
        // state without possibleactions cannot swallow the next state's list (arrays may be
        // multi-line).
        $statesSource = file_get_contents("$root/states.inc.php");
        $statesSource = preg_replace('~//[^\n]*~', '', $statesSource);
        preg_match_all('/^\s*\d+\s*=>\s*array\(/m', $statesSource, $offsets, PREG_OFFSET_CAPTURE);
        $this->assertNotEmpty($offsets[0]);
        $declaredByState = [];
        foreach ($offsets[0] as $i => $offset) {
            $start = $offset[1];
            $end = $offsets[0][$i + 1][1] ?? strlen($statesSource);
            $block = substr($statesSource, $start, $end - $start);
            if (!preg_match('/"name"\s*=>\s*"(\w+)"/', $block, $nameMatch)) {
                continue;
            }
            if (preg_match('/"possibleactions"\s*=>\s*(?:array\(|\[)(.*?)(?:\)|\])/s', $block, $actionsMatch)) {
                preg_match_all('/"([a-zA-Z]+)"/', $actionsMatch[1], $names);
                $declaredByState[$nameMatch[1]] = $names[1];
            }
        }

        // State-specific bindings: each wire action name must be declared in the state whose
        // interactions send it.
        $expectedBindings = [
            'initialMeld' => ['turn0'],
            'updateInitialMeld' => ['turn0'],
            'draw' => ['playerTurn'],
            'meld' => ['playerTurn'],
            'dogma' => ['playerTurn'],
            'endorse' => ['playerTurn'],
            'achieve' => ['playerTurn'],
            'achieveCardBack' => ['playerTurn'],
            'choose' => ['selectionMove'],
            'chooseSpecialOption' => ['selectionMove'],
            'chooseRecto' => ['selectionMove'],
            'publicationRearrange' => ['selectionMove'],
            'seizeRelicToHand' => ['relicPlayerTurn'],
            'seizeRelicToAchievements' => ['relicPlayerTurn'],
            'passSeizeRelic' => ['relicPlayerTurn'],
            'dogmaArtifactOnDisplay' => ['artifactPlayerTurn'],
            'returnArtifactOnDisplay' => ['artifactPlayerTurn'],
            'passArtifactOnDisplay' => ['artifactPlayerTurn'],
            'passPromoteCard' => ['promoteCardPlayerTurn'],
            'promoteCard' => ['promoteCardPlayerTurn'],
            'promoteCardBack' => ['promoteCardPlayerTurn'],
            'passDogmaPromotedCard' => ['dogmaPromotedPlayerTurn'],
            'dogmaPromotedCard' => ['dogmaPromotedPlayerTurn'],
        ];
        $offenders = [];
        foreach ($expectedBindings as $wireAction => $states) {
            foreach ($states as $state) {
                if (!in_array($wireAction, $declaredByState[$state] ?? [], true)) {
                    $offenders[] = "$wireAction is not declared in possibleactions of state $state";
                }
            }
        }
        // Generic layer: every validated wire action name must be declared somewhere, so a new
        // action with a misspelled or undeclared wire name fails here even without a binding above.
        $allDeclared = array_merge(...array_values($declaredByState));
        foreach (array_keys($wireActions) as $wireAction) {
            if (!in_array($wireAction, $allDeclared, true)) {
                $offenders[] = "$wireAction is sent with client-side validation but declared in no state's possibleactions";
            }
        }
        $this->assertSame([], $offenders, "Wire action names must match possibleactions under the new framework:\n" . implode("\n", $offenders));
    }
}
