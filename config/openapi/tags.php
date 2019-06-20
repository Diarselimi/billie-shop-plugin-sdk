<?php

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     tags={
 *          @OA\Tag(name="Orders API", x={"groups":{"public", "plugins"}}),
 *          @OA\Tag(name="Checkout API", x={"groups":{"public", "checkout"}}),
 *          @OA\Tag(name="Dashboard API", x={"groups":{"public", "dashboard"}}),
 *          @OA\Tag(name="OAuth API", x={"groups":{"public", "private", "checkout"}}),
 *          @OA\Tag(name="Internal API", x={"groups":{"private"}}),
 *          @OA\Tag(name="Internal API for Services", x={"groups":{"private"}}),
 *     }
 * )
 */
