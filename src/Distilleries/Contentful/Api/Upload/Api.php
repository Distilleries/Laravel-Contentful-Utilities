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
    public function uploadFile(string $file) : array
    {
        $body = starts_with($file, '/') ? file_get_contents($file) : $file;

        $response = $this->client->request('POST', $this->url('uploads'), [
            RequestOptions::BODY => $body,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/octet-stream',
                'Authorization' => 'Bearer ' . $this->config['tokens']['management'],
            ],
        ]);

        return $this->decodeResponse($response);
    }
}
