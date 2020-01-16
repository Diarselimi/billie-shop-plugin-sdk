<?php

namespace App\Application\UseCase\GetMerchantSepaMandate;

use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GetMerchantSepaMandateResponse extends StreamedResponse
{
    private const SIZE_CHUNK_TO_STREAM = 1024;

    public function stream(StreamInterface $stream): GetMerchantSepaMandateResponse
    {
        $this->setCallback(function () use ($stream) {
            do {
                $content = $stream->read(self::SIZE_CHUNK_TO_STREAM);

                echo $content;
                flush();
            } while (!$stream->eof());
        });

        return $this;
    }
}
