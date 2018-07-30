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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function live(Request $request): JsonResponse
    {
        return $this->handle($request, false);
    }

    /**
     * Handle Contentful preview webhook.
     *
     * @param  \Illuminate\Http\Request  $request
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
     * @param  \Illuminate\Http\Request  $request
     * @param  boolean  $isPreview
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handle(Request $request, bool $isPreview): JsonResponse
    {
        $headers = $request->header();
        $payload = $request->all();

        $response = (new Manager)->handle($headers, $payload, $isPreview);

        return response()->json($response['message'], $response['status']);
    }
}
