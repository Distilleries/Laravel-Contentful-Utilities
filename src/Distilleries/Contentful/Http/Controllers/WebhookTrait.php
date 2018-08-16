<?php

namespace Distilleries\Contentful\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Distilleries\Contentful\Webhook\Manager;

trait WebhookTrait
{
    /**
     * Handle Contentful live webhook.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function live(Request $request): JsonResponse
    {
        return $this->handle($request, false);
    }

    /**
     * Handle Contentful preview webhook.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function preview(Request $request): JsonResponse
    {
        use_contentful_preview();

        return $this->handle($request, true);
    }

    /**
     * Handle webhook request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  boolean $isPreview
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handle(Request $request, bool $isPreview): JsonResponse
    {
        $headers = $request->header();
        $headers = is_string($headers) ? [$headers] : $headers;
        $payload = $request->all();

        $response = (new Manager)->handle($headers, $payload, $isPreview);
        $responseClass = response();

        if (method_exists($responseClass, 'json')) {
            return $responseClass->json($response['message'], $response['status']);
        } else {
            return response(json_encode($response['message']), $response['status']);
        }
    }
}
