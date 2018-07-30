<?php

namespace Distilleries\Contentful\Api;

interface SyncApi
{
    /**
     * Sync initial API call.
     *
     * @param  string  $type
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncInitial(string $type = 'Entry'): array;

    /**
     * Next sync API call (based on previous returned syncNextUrl).
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncNext(): array;
}
