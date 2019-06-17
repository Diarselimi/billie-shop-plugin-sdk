<?php

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     tags={
 *          @OA\Tag(name="Orders API"),
 *          @OA\Tag(name="Checkout API"),
 *          @OA\Tag(name="Dashboard API"),
 *          @OA\Tag(name="OAuth API"),
 *          @OA\Tag(name="Miscellaneous"),
 *          @OA\Tag(name="Internal API", x={"groups":{"private"}}),
 *          @OA\Tag(name="Internal API (for Services)", x={"groups":{"private"}}),
 *     },
 *     x={"tagGroups"={
 *          {"name":"PaD API", "tags":{"OAuth API", "Orders API", "Checkout API", "Dashboard API"}, "groups":{"public"}},
 *          {"name":"PaD Internal API", "tags":{"Internal API", "Internal API for Services"}, "groups":{"private"}},
 *     }},
 * )
 */
