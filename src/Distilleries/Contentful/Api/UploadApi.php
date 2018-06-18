<?php

namespace Distilleries\Contentful\Api;

interface UploadApi
{
    /**
     * Upload given file to Contentful Medias.
     *
     * @param  string  $file
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uploadFile(string $file) : array;
}
