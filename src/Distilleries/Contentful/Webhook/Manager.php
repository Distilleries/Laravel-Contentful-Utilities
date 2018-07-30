<?php

namespace Distilleries\Contentful\Webhook;

use Exception;

class Manager
{
    /**
     * Handle Contentful webhook for given headers and payload.
     *
     * @param  array  $headers
     * @param  array  $payload
     * @param  boolean  $isPreview
     * @return array
     */
    public function handle(array $headers, array $payload, bool $isPreview = false): array
    {
        if (! isset($headers['x-contentful-topic'])) {
            return $this->response('Page not found', 404);
        }

        $topics = explode('.', $headers['x-contentful-topic'][0]);
        if ($topics[0] !== 'ContentManagement') {
            return $this->response('Page not found', 404);
        }

        switch ($topics[1]) {
            case 'Asset':
                $handler = new AssetHandler;
                break;
            case 'Entry':
                $handler = new EntryHandler;
                break;
            case 'ContentType':
            default:
                $handler = null;
        }

        if (! empty($handler)) {
            try {
                $handler->handle($topics[2], $payload, $isPreview);
            } catch (Exception $e) {
                return $this->response($e->getMessage(), 500);
            }
        }

        return $this->response();
    }

    /**
     * Return normalized service response signature.
     *
     * @param  string  $message
     * @param  integer  $status
     * @return array
     */
    private function response(string $message = '', int $status = 200): array
    {
        return [
            'status' => $status,
            'message' => ! empty($message) ? $message : null,
        ];
    }
}
