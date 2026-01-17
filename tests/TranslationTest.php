<?php
declare(strict_types = 1);
namespace Slothsoft\Lang;

use PHPUnit\Framework\TestCase;

/**
 * TranslationTest
 *
 * @see Translation
 *
 * @todo auto-generated
 */
final class TranslationTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Translation::class), "Failed to load class 'Slothsoft\Lang\Translation'!");
    }
}