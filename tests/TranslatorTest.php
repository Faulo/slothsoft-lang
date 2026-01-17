<?php
declare(strict_types = 1);
namespace Slothsoft\Lang;

use PHPUnit\Framework\TestCase;

/**
 * TranslatorTest
 *
 * @see Translator
 *
 * @todo auto-generated
 */
final class TranslatorTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Translator::class), "Failed to load class 'Slothsoft\Lang\Translator'!");
    }
}