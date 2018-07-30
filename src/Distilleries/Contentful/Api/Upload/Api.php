<?php

namespace Distilleries\Contentful\Api\Upload;

use GuzzleHttp\RequestOptions;
use Distilleries\Contentful\Api\BaseApi;
use Distilleries\Contentful\Api\UploadApi;

class Api extends BaseApi implements UploadApi
{
    /**
     * {@inheritdoc}
     */
    protected $baseUrl = 'https://upload.contentful.com';

    /**
     * {@inheritdoc}
     */
    public function uploadFile(string $file): array
    {
        $response = $this->client->request('POST', $this->url('uploads'), [
            RequestOptions::BODY => file_get_contents($file),
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/octet-stream',
                'Authorization' => 'Bearer ' . $this->config['tokens']['management'],
            ],
        ]);

        return $this->decodeResponse($response);
    }
}
