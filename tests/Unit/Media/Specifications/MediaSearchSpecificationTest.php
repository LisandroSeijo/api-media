<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Specifications;

use Api\Media\Domain\Specifications\MediaSearchSpecification;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitarios para MediaSearchSpecification (Composite Specification)
 */
class MediaSearchSpecificationTest extends TestCase
{
    private MediaSearchSpecification $spec;

    protected function setUp(): void
    {
        parent::setUp();
        $this->spec = new MediaSearchSpecification();
    }

    /** @test */
    public function it_accepts_valid_search_parameters(): void
    {
        $this->assertTrue(
            $this->spec->isSatisfiedBy('cats', 10, 0)
        );
    }

    /** @test */
    public function it_accepts_search_with_null_limit_and_offset(): void
    {
        $this->assertTrue(
            $this->spec->isSatisfiedBy('dogs', null, null)
        );
    }

    /** @test */
    public function it_rejects_empty_query(): void
    {
        $this->assertFalse(
            $this->spec->isSatisfiedBy('', 10, 0)
        );
    }

    /** @test */
    public function it_rejects_query_exceeding_max_length(): void
    {
        $longQuery = str_repeat('a', 51); // 51 chars, max is 50
        
        $this->assertFalse(
            $this->spec->isSatisfiedBy($longQuery, 10, 0)
        );
    }

    /** @test */
    public function it_rejects_invalid_limit(): void
    {
        $this->assertFalse(
            $this->spec->isSatisfiedBy('cats', 0, 0) // limit < 1
        );
        
        $this->assertFalse(
            $this->spec->isSatisfiedBy('cats', 51, 0) // limit > 50
        );
    }

    /** @test */
    public function it_rejects_invalid_offset(): void
    {
        $this->assertFalse(
            $this->spec->isSatisfiedBy('cats', 10, -1) // offset < 0
        );
        
        $this->assertFalse(
            $this->spec->isSatisfiedBy('cats', 10, 5000) // offset > 4999
        );
    }

    /** @test */
    public function it_returns_all_validation_errors(): void
    {
        $errors = $this->spec->getValidationErrors('', 0, -1);
        
        $this->assertCount(3, $errors);
        $this->assertArrayHasKey('query', $errors);
        $this->assertArrayHasKey('limit', $errors);
        $this->assertArrayHasKey('offset', $errors);
    }

    /** @test */
    public function it_returns_only_relevant_errors(): void
    {
        // Solo query es inválido
        $errors = $this->spec->getValidationErrors('', 10, 0);
        
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('query', $errors);
        $this->assertArrayNotHasKey('limit', $errors);
        $this->assertArrayNotHasKey('offset', $errors);
    }

    /** @test */
    public function it_returns_empty_errors_for_valid_input(): void
    {
        $errors = $this->spec->getValidationErrors('cats', 10, 0);
        
        $this->assertEmpty($errors);
    }

    /** @test */
    public function it_correctly_detects_when_has_errors(): void
    {
        $this->assertTrue(
            $this->spec->hasErrors('', 10, 0)
        );
        
        $this->assertFalse(
            $this->spec->hasErrors('cats', 10, 0)
        );
    }

    /** @test */
    public function it_validates_edge_cases(): void
    {
        // Query con 1 char (mínimo)
        $this->assertTrue(
            $this->spec->isSatisfiedBy('a', null, null)
        );
        
        // Query con 50 chars (máximo)
        $maxQuery = str_repeat('a', 50);
        $this->assertTrue(
            $this->spec->isSatisfiedBy($maxQuery, null, null)
        );
        
        // Limit = 1 (mínimo)
        $this->assertTrue(
            $this->spec->isSatisfiedBy('test', 1, null)
        );
        
        // Limit = 50 (máximo)
        $this->assertTrue(
            $this->spec->isSatisfiedBy('test', 50, null)
        );
        
        // Offset = 0 (mínimo)
        $this->assertTrue(
            $this->spec->isSatisfiedBy('test', null, 0)
        );
        
        // Offset = 4999 (máximo)
        $this->assertTrue(
            $this->spec->isSatisfiedBy('test', null, 4999)
        );
    }
}
