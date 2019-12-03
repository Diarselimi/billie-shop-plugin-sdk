<?php

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     tags={
 *          @OA\Tag(name="Authentication", x={"groups":{"public", "private"}}),
 *          @OA\Tag(name="Checkout Client", x={"groups":{"public", "private"}}),
 *          @OA\Tag(name="Checkout Server", x={"groups":{"public", "private"}}),
 *          @OA\Tag(name="Back-end Order Creation", x={"groups":{"public", "private"}}),
 *          @OA\Tag(name="Order Management", x={"groups":{"public", "private"}}),
 *          @OA\Tag(name="Webhooks", x={"groups":{"public", "private"}}),
 *
 *          @OA\Tag(name="Dashboard Orders", x={"groups":{"private"}}),
 *          @OA\Tag(name="Dashboard Debtors", x={"groups":{"private"}}),
 *          @OA\Tag(name="Dashboard Payments", x={"groups":{"private"}}),
 *          @OA\Tag(name="Dashboard Users", x={"groups":{"private"}}),
 *          @OA\Tag(name="Dashboard Merchants", x={"groups":{"private"}}),
 *
 *          @OA\Tag(name="Support", x={"groups":{"private"}}),
 *          @OA\Tag(name="API Docs", x={"groups":{"private"}}),
 *     }
 * )
 */
