<?php
declare(strict_types = 1);
namespace Slothsoft\Lang;

use PHPUnit\Framework\TestCase;

/**
 * GrammarJaTest
 *
 * @see GrammarJa
 *
 * @todo auto-generated
 */
class GrammarJaTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(GrammarJa::class), "Failed to load class 'Slothsoft\Lang\GrammarJa'!");
    }
}