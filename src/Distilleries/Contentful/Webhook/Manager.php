<?php

namespace Distilleries\Contentful\Contentful\Webhook;

class Manager
{
    /**
     * Handle Contentful webhook for given headers and payload.
     *
     * @param  array  $headers
     * @param  array  $payload
     * @return array
     */
    public function handle($headers, $payload)
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
            $handler->handle($topics[2], $payload);
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
    private function response($message = '', $status = 200)
    {
        return [
            'status' => $status,
            'message' => ! empty($message) ? $message : null,
        ];
    }
}
