<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Gateway\UpdateSerializer;

use Ibexa\Contracts\Core\Search\Document;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType\IdentifierField;
use Ibexa\Solr\Gateway\UpdateSerializerInterface;

/**
 * @internal
 */
final class JsonUpdateSerializer extends UpdateSerializer implements UpdateSerializerInterface
{
    /**
     * @throws \JsonException
     */
    public function serialize(array $documents): string
    {
        $data = [];
        foreach ($documents as $document) {
            if (empty($document->documents)) {
                $document->documents[] = $this->getNestedDummyDocument($document->id);
            }

            $data[] = $this->mapDocumentToData($document);
        }

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, array<mixed>>
     */
    private function mapDocumentToData(Document $document): array
    {
        $data['id'] = $this->fieldValueMapper->map(
            new Field('id', $document->id, new IdentifierField())
        );
        foreach ($document->fields as $field) {
            $fieldName = $this->nameGenerator->getTypedName($field->getName(), $field->getType());
            $value = $this->fieldValueMapper->map($field);
            $data[$fieldName] = $this->buildValue($value, $fieldName, $data);
        }

        foreach ($document->documents as $subDocument) {
            $data['_childDocuments_'][] = $this->mapDocumentToData($subDocument);
        }

        return $data;
    }

    public function getSupportedFormat(): string
    {
        return 'json';
    }

    private function buildValue(mixed $value, string $fieldName, array $data): mixed
    {
        return !array_key_exists($fieldName, $data) || !is_array($data[$fieldName])
            ? $value
            // append value(s) to a multivalued type
            : array_merge($data[$fieldName], $value);
    }
}
