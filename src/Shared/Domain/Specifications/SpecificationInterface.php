<?php

declare(strict_types=1);

namespace Api\Shared\Domain\Specifications;

/**
 * Interfaz para implementar el patrón Specification
 * 
 * Las Specifications encapsulan reglas de negocio que determinan
 * si un objeto o valor satisface ciertos criterios.
 */
interface SpecificationInterface
{
    /**
     * Verifica si el candidato satisface la especificación
     *
     * @param mixed $candidate El objeto o valor a validar
     * @return bool True si satisface la especificación, false en caso contrario
     */
    public function isSatisfiedBy(mixed $candidate): bool;

    /**
     * Obtiene el mensaje de error descriptivo cuando la especificación no se cumple
     *
     * @param mixed $candidate El objeto o valor que no satisface la especificación
     * @return string Mensaje descriptivo del error
     */
    public function getErrorMessage(mixed $candidate): string;
}
