<?php

/*
 * This file is a part of the DiscordPHP-Http project.
 *
 * Copyright (c) 2021-present David Cole <david.cole1340@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE file.
 */

namespace Discord\Http\Multipart;

class MultipartField
{
    private string $name;
    private string $content;
    private array $headers;
    private ?string $fileName;

    /**
     * @var String[]
     */
    public function __construct(
        string $name,
        string $content,
        array $headers = [],
        ?string $fileName = null
    ) {
        $this->name = $name;
        $this->content = $content;
        $this->headers = $headers;
        $this->fileName = $fileName;
    }

    public function __toString(): string
    {
        $out = 'Content-Disposition: form-data; name="'.$this->name.'"';

        if (! is_null($this->fileName)) {
            $out .= '; filename="'.urlencode($this->fileName).'"';
        }

        $out .= PHP_EOL;

        foreach ($this->headers as $header => $value) {
            $out .= $header.': '.$value.PHP_EOL;
        }

        $out .= PHP_EOL.$this->content.PHP_EOL;

        return $out;
    }
}
