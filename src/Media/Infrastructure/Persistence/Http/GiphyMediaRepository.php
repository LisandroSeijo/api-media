<?php

declare(strict_types=1);

namespace Api\Media\Infrastructure\Persistence\Http;

use Api\Media\Domain\Entities\MediaItem;
use Api\Media\Domain\Repositories\MediaRepositoryInterface;
use Api\Media\Domain\ValueObjects\SearchQuery;
use Api\Media\Domain\ValueObjects\Limit;
use Api\Media\Domain\ValueObjects\Offset;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Implementación del repositorio usando GIPHY API
 */
class GiphyMediaRepository implements MediaRepositoryInterface
{
    private const BASE_URL = 'https://api.giphy.com/v1';
    private const ENDPOINT_SEARCH = '/gifs/search';
    private const ENDPOINT_BY_ID = '/gifs';

    private Client $client;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.giphy.api_key');
        
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'timeout' => 10.0,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function search(SearchQuery $query, Limit $limit, Offset $offset): array
    {
        try {
            $response = $this->client->get(self::ENDPOINT_SEARCH, [
                'query' => [
                    'api_key' => $this->apiKey,
                    'q' => $query->getUrlEncoded(),
                    'limit' => $limit->getValue(),
                    'offset' => $offset->getValue(),
                    'rating' => 'g', // Filtro de contenido seguro
                    'lang' => 'es', // Idioma español
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->transformResponse($data);

        } catch (GuzzleException $e) {
            Log::error('GIPHY API Error: ' . $e->getMessage(), [
                'query' => $query->getValue(),
                'exception' => $e,
            ]);

            throw new \RuntimeException(
                'Error connecting to GIPHY API: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findById(string $id): ?MediaItem
    {
        try {
            $response = $this->client->get(self::ENDPOINT_BY_ID . '/' . $id, [
                'query' => [
                    'api_key' => $this->apiKey,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['data'])) {
                return null;
            }

            return MediaItem::fromApiResponse($data['data']);

        } catch (GuzzleException $e) {
            Log::error('GIPHY API Error (Find by ID): ' . $e->getMessage(), [
                'id' => $id,
                'exception' => $e,
            ]);

            return null;
        }
    }

    /**
     * Transforma la respuesta de la API a nuestro formato
     *
     * @param array $data
     * @return array{data: MediaItem[], pagination: array, meta: array}
     */
    private function transformResponse(array $data): array
    {
        // Verificar respuesta sintética (error de GIPHY)
        if (empty($data['meta']['response_id'])) {
            throw new \RuntimeException('GIPHY API returned a synthetic response');
        }

        $mediaItems = array_map(
            fn($itemData) => MediaItem::fromApiResponse($itemData),
            $data['data'] ?? []
        );

        return [
            'data' => $mediaItems,
            'pagination' => $data['pagination'] ?? [],
            'meta' => [
                'status' => $data['meta']['status'] ?? 200,
                'msg' => $data['meta']['msg'] ?? 'OK',
                'response_id' => $data['meta']['response_id'] ?? '',
            ],
        ];
    }
}
