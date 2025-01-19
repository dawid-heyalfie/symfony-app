<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class SlugTransformer implements DataTransformerInterface
{
    public function transform($value): string
    {
        // No transformation for view data; return as-is
        return $value ?? '';
    }

    public function reverseTransform($value): string
    {
        if (null === $value || '' === $value) {
            return ''; // Allow empty slugs to be handled later
        }

        // Convert string to slug:
        // - Trim whitespace
        // - Replace spaces and special characters with dashes
        // - Remove leading/trailing dashes
        // - Convert to lowercase
        return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $value), '-'));
    }
}
