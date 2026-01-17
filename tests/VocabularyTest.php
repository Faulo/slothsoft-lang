<?php
declare(strict_types = 1);
namespace Slothsoft\Lang;

use PHPUnit\Framework\TestCase;

/**
 * VocabularyTest
 *
 * @see Vocabulary
 *
 * @todo auto-generated
 */
class VocabularyTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Vocabulary::class), "Failed to load class 'Slothsoft\Lang\Vocabulary'!");
    }
}