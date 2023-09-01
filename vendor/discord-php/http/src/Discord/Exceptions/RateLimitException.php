<?php

/*
 * This file is a part of the DiscordPHP-Http project.
 *
 * Copyright (c) 2021-present David Cole <david.cole1340@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE file.
 */

namespace Discord\Http\Exceptions;

/**
 * Thrown when a request to Discord's REST API got rate limited and the library
 * does not know how to handle.
 *
 * @author SQKo
 */
class RateLimitException extends RequestFailedException
{
    protected $code = 429;
}
